<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $irZeroOneData = [];

    public $approvedCount = 0;
    public $pipelineCount = 0;
    public $totalCount = 0;

    public $filterCluster = 'All';
    public $filterType = 'All';

    public $chartData = [];

    protected $listeners = ['filterUpdated'];

    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeFilteredTotals();
    }

    #[On('filter-updated')]
    public function filterUpdated($cluster, $type)
    {
        $this->filterCluster = $cluster;
        $this->filterType = $type;
        $this->computeFilteredTotals();
    }

    private function computeFilteredTotals()
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');

        // --- Normalize ZERO ONE ---
        $zeroOne = collect($this->irZeroOneData)->map(function ($row) {
            $normalized = [];
            foreach ($row as $key => $value) {
                $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
                $normalized[$normalizedKey] = trim((string)$value);
            }
            // Ensure sp_id always normalized
            $normalized['sp_id'] = isset($normalized['sp_id'])
                ? strtolower(trim($normalized['sp_id']))
                : null;
            return $normalized;
        });

        // --- Normalize ZERO TWO ---
        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($row) => is_array($row) && count($row) > 0)
            ->values()
            ->map(function ($row) {
                $normalized = [];
                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
                    $normalized[$normalizedKey] = trim((string)$value);
                }
                // Normalize ID
                $normalized['sp_id'] = isset($normalized['sp_id'])
                    ? strtolower(trim($normalized['sp_id']))
                    : null;
                return $normalized;
            });

        // --- Build NOL1 Lookup ---
        $nol1Lookup = $zeroTwo
            ->filter(fn($item) => !empty($item['sp_id'] ?? null))
            ->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued'] ?? null]);

        // --- Apply Filters ---
        $filtered = $zeroOne->filter(function ($item) {
            $clusterMatch = $this->filterCluster === 'All' || ($item['cluster'] ?? '') === $this->filterCluster;
            $typeMatch = $this->filterType === 'All' || ($item['project_type'] ?? '') === $this->filterType;
            return $clusterMatch && $typeMatch;
        });

        // --- PIPELINE ---
        $pipelineItems = $filtered->filter(
            fn($item) => ($item['stage'] ?? '') === 'Pre-procurement'
                && ($item['status'] ?? '') === 'Subproject Confirmed'
        );

        // --- APPROVED ---
        $approvedItems = $filtered->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower($item['sp_id'] ?? '');
            $stage = $item['stage'] ?? '';
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return in_array($stage, ['Implementation', 'Procurement', 'Completed']) && $hasNol1;
        });

        // --- Totals for chart ---
        // Determine which clusters to show
        if ($this->filterCluster === 'All') {
            $clusters = ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'];
        } else {
            $clusters = [$this->filterCluster];
        }

        $pipelineByCluster = $pipelineItems->groupBy('cluster')->map->count();
        $approvedByCluster = $approvedItems->groupBy('cluster')->map->count();


        $this->chartData = [
            'labels' => $clusters,
            'datasets' => [
                [
                    'label' => 'Approved SPs',
                    'data' => array_map(fn($c) => $approvedByCluster[$c] ?? 0, $clusters),
                    'backgroundColor' => '#004ef5',
                    'stack' => 'Stack 1',
                ],
                [
                    'label' => 'Pipeline SPs',
                    'data' => array_map(fn($c) => $pipelineByCluster[$c] ?? 0, $clusters),
                    'backgroundColor' => '#1abc9c',
                    'stack' => 'Stack 1',
                ],
            ],
        ];
    }


    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};

?>

<div class="col">
    <div class="tile-container">
        <div class="tile-title">Subprojects by Cluster (No.)</div>
        <div class="tile-content position-relative" style="height: 300px;"
             x-data="ClusterStackedChart(@entangle('chartData'))"
             x-init="$watch('chartData', () => init())">
            <canvas id="chrt-sp-by-cluster"></canvas>
        </div>
    </div>
</div>

@script
<script>
    window.ClusterStackedChart = function(entangledChartData) {
        let chart = null;

        return {
            chartData: entangledChartData, // reactive Livewire data

            init() {
                const ctx = document.getElementById('chrt-sp-by-cluster');
                if (!ctx) return;

                // Destroy previous chart if exists
                if (chart) chart.destroy();

                const data = this.chartData; // reactive Livewire property

                // Handle empty data
                if (!data || !data.labels || data.labels.length === 0) return;

                // Compute max stacked value per label
                const maxPerLabel = data.labels.map((_, idx) =>
                    data.datasets.reduce((sum, ds) => sum + (ds.data[idx] || 0), 0)
                );
                const maxValue = Math.max(...maxPerLabel, 10); // fallback to 10

                // Determine step size
                let step = maxValue <= 50 ? 5 :
                           maxValue <= 100 ? 10 :
                           maxValue <= 200 ? 20 :
                           maxValue <= 500 ? 50 : 100;

                const max = Math.ceil(maxValue / step) * step;

                chart = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        animation: { duration: 900, easing: 'easeOutQuart' },
                        datasets: { bar: { borderWidth: 0, borderSkipped: false, barPercentage: 0.85, categoryPercentage: 0.9 } },
                        scales: {
                            x: { stacked: true, grid: { display: true }, ticks: { font: { size: 13 }, color: '#333' } },
                            y: { stacked: true, beginAtZero: true, max: max, ticks: { stepSize: step, font: { size: 12 }, color: '#555' }, grid: { drawBorder: false, color: 'rgba(0,0,0,0.05)', lineWidth: 1 } }
                        },
                        plugins: {
                            legend: { position: 'top', labels: { font: { size: 13, weight: '500' }, boxWidth: 40, color: '#333' } },
                            tooltip: { backgroundColor: '#1f2937', titleFont: { size: 13 }, bodyFont: { size: 12 }, bodyColor: '#fff', cornerRadius: 6, callbacks: { label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}` } },
                            datalabels: { anchor: 'center', align: 'center', color: '#fff', font: { size: 12, weight: '600' }, formatter: v => v > 0 ? v : '' }
                        }
                    },
                    plugins: [ChartDataLabels]
                });

                // Rounded top corners only for top dataset
                chart.data.datasets.forEach((dataset, i) => {
                    dataset.borderRadius = i === chart.data.datasets.length - 1 ? { topLeft: 8, topRight: 8 } : 0;
                });

                chart.update();
            }
        };
    }
</script>
@endscript
