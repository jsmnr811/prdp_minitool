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

        // Normalize data
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

        // Apply cluster filter
        $filtered = $zeroOne->filter(function ($item) {
            return $this->filterCluster === 'All' || ($item['cluster'] ?? '') === $this->filterCluster;
        });

        // Approved SPs only
        $approvedItems = $filtered->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower($item['sp_id'] ?? '');
            $stage = $item['stage'] ?? '';
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return in_array($stage, ['Implementation', 'For procurement', 'Completed']) && $hasNol1;
        });

        $stages = ['Implementation', 'For procurement', 'Completed'];

        // Sum cost
        $costByStage = array_map(
            fn($stage) =>
            $approvedItems
                ->where('stage', $stage)
                ->sum(fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0)),
            $stages
        );

        $this->chartData = [
            'labels' => $stages,
            'datasets' => [[
                'label' => 'Approved SPs Cost (₱)',
                'data' => $costByStage,
                'backgroundColor' => '#004ef5',
                'borderRadius' => 6,
            ]]
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
        <div class="tile-title">Approved Subproject Cost by Stage</div>
        <div class="tile-content position-relative" style="height: 300px;"
            x-data="ApprovedStageCostChart(@entangle('chartData'))"
            x-init="$watch('chartData', () => init())">
            <canvas id="chrt-approved-cost-by-stage"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0"></script>

<script>
    function formatCurrency(value) {
        const abs = Math.abs(value);
        let formatted;
        if (abs >= 1_000_000_000_000) formatted = (value / 1_000_000_000_000).toFixed(2) + ' T';
        else if (abs >= 1_000_000_000) formatted = (value / 1_000_000_000).toFixed(2) + ' B';
        else if (abs >= 1_000_000) formatted = (value / 1_000_000).toFixed(2) + ' M';
        else if (abs >= 1_000) formatted = (value / 1_000).toFixed(2) + ' K';
        else formatted = value.toFixed(2);
        return '₱ ' + formatted;
    }

    function ApprovedStageCostChart(chartData) {
        return {
            chart: null,
            chartData: chartData,
            init() {
                if (this.chart) this.chart.destroy();

                const ctx = document.getElementById('chrt-approved-cost-by-stage').getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: this.chartData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    boxWidth: 20
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: (context) => formatCurrency(context.raw || 0)
                                }
                            },
                            datalabels: {
                                color: 'white',
                                font: {
                                    weight: 'bold'
                                },
                                anchor: 'center',
                                align: 'center',
                                formatter: (value) => value > 0 ? formatCurrency(value) : ''
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                },
                                ticks: {
                                    callback: (value) => formatCurrency(value)
                                }
                            }
                        },
                        elements: {
                            bar: {
                                borderSkipped: 'bottom', 
                                borderRadius: {
                                    topLeft: 6,
                                    topRight: 6,
                                    bottomLeft: 0,
                                    bottomRight: 0
                                }
                            }
                        }

                    },
                    plugins: [ChartDataLabels]
                });
            }
        }
    }
</script>