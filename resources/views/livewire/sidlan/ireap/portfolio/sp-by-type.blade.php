<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Livewire\Attributes\On;

new class extends Component {
    public $irZeroOneData = [];
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

        // Normalize sheet data
        $zeroOne = collect($this->irZeroOneData)->map(function ($row) {
            $normalized = [];
            foreach ($row as $key => $value) {
                $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
                $normalized[$normalizedKey] = trim((string)$value);
            }
            $normalized['sp_id'] = isset($normalized['sp_id']) ? strtolower(trim($normalized['sp_id'])) : null;
            return $normalized;
        });

        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($row) => is_array($row) && count($row) > 0)
            ->values()
            ->map(function ($row) {
                $normalized = [];
                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
                    $normalized[$normalizedKey] = trim((string)$value);
                }
                $normalized['sp_id'] = isset($normalized['sp_id']) ? strtolower(trim($normalized['sp_id'])) : null;
                return $normalized;
            });

        $nol1Lookup = $zeroTwo
            ->filter(fn($item) => !empty($item['sp_id'] ?? null))
            ->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued'] ?? null]);

        // Apply filters
        $filtered = $zeroOne->filter(function ($item) {
            $clusterMatch = $this->filterCluster === 'All' || ($item['cluster'] ?? '') === $this->filterCluster;
            return $clusterMatch;
        });

        // Identify pipeline and approved
       $pipelineItems = $filtered->filter(function ($item) use ($nol1Lookup) {
            $stage = $item['stage'] ?? '';
            $status = $item['status'] ?? '';
            $spId = strtolower($item['sp_id'] ?? '');
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);

            return $stage === 'Pre-procurement'
                && in_array($status, [
                    'Subproject Confirmed',
                    'Business Plan Package for RPCO technical review submitted',
                    'RPCO Technical Review of Business Plan conducted',
                    'Joint Technical Review (JTR) conducted',
                    'SP approved by RPAB',
                    'Signing of the IMA',
                    'Subproject Issued with No Objection Letter 1',
                ])
                && !$hasNol1;
        });

        // --- APPROVED ---
        $approvedItems = $filtered->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower($item['sp_id'] ?? '');
            $stage = $item['stage'] ?? '';
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return in_array($stage, ['Implementation', 'For procurement', 'Completed']) && $hasNol1;
        });

        $spTypes = $filtered
            ->pluck('sp_type')
            ->filter(fn($t) => !empty($t))
            ->unique()
            ->values()
            ->toArray();

        $this->chartData = [
            'labels' => $spTypes,
            'datasets' => [
                [
                    'label' => 'Approved',
                    'data' => array_map(fn($type) => $approvedItems->where('sp_type', $type)->count(), $spTypes),
                    'backgroundColor' => '#004ef5'
                ],
                [
                    'label' => 'Pipeline',
                    'data' => array_map(fn($type) => $pipelineItems->where('sp_type', $type)->count(), $spTypes),
                    'backgroundColor' => '#1abc9c'
                ],
            ]
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
        <div class="tile-title">Subprojects by Type (No.)</div>
        <div class="tile-content position-relative" style="height: 300px;"
             x-data="SPTypeStackedChart(@entangle('chartData'))"
             x-init="$watch('chartData', () => init())">
            <canvas id="chrt-sp-by-type"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
function SPTypeStackedChart(chartData) {
    return {
        chart: null,
        chartData: chartData,
        init() {
            if (this.chart) this.chart.destroy();

            // Clone datasets and add borderRadius / borderSkipped
            const datasetsWithRadius = this.chartData.datasets.map((dataset, datasetIndex) => ({
                ...dataset,
                borderSkipped: false,
                borderRadius: (ctx) => {
                    const dataIndex = ctx.dataIndex;
                    const datasets = ctx.chart.data.datasets;

                    // Only round if top segment
                    let isTop = true;
                    for (let i = datasetIndex + 1; i < datasets.length; i++) {
                        if (datasets[i].data[dataIndex] > 0) {
                            isTop = false;
                            break;
                        }
                    }

                    return isTop ? { topLeft: 6, topRight: 6, bottomLeft: 0, bottomRight: 0 } : 0;
                }
            }));

            const ctx = document.getElementById('chrt-sp-by-type').getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.chartData.labels,
                    datasets: datasetsWithRadius
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: { mode: 'index', intersect: false },
                        datalabels: {
                            color: 'white',
                            font: { weight: 'bold' },
                            anchor: 'center',
                            align: 'center',
                            formatter: value => value > 0 ? value : ''
                        }
                    },
                    scales: {
                        x: { stacked: true },
                        y: { stacked: true, beginAtZero: true }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
    }
}
</script>

