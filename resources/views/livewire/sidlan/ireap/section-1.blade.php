<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Services\SidlanLocalAPIServices;

new class extends Component {
    public $chartData = [];

    public $pipelinePieChartData = [];
    public $approvedPieChartData = [];

    public function mount($irZeroOneData = []): void
    {
        $apiService = new SidlanLocalAPIServices();
        $irZeroTwoData = $apiService->executeRequest(['dataset_id' => 'ir-01-002']);

        $totals = $this->computePipelineAndApproved($irZeroOneData, $irZeroTwoData);


        $pipeline = $totals['pipeline'];

        $approved = $totals['approved'];

        $totalAllocation = $pipeline + $approved + 5;
        $totalAllocation = ceil($totalAllocation / 10) * 10;

        $this->pipelinePieChartData = $totals['pipeline_chart_data'];

        $this->approvedPieChartData = $totals['approved_chart_data'];
        $this->chartData = [
            'labels' => ['Subproject Portfolio'],
            'datasets' => [
                [
                    'label' => 'Total Allocation',
                    'data' => [$totalAllocation],
                    'backgroundColor' => '#0047e0',
                    'stack' => 'Stack 0',
                ],
                [
                    'label' => 'Approved',
                    'data' => [$approved],
                    'backgroundColor' => '#2AB7A9',
                    'stack' => 'Stack 1',
                ],
                [
                    'label' => 'Pipelined',
                    'data' => [$pipeline],
                    'backgroundColor' => '#3498db',
                    'stack' => 'Stack 1',
                ],
            ],
        ];
    }

    private function computePipelineAndApproved(array $zeroOneData, array $zeroTwoData): array
    {
        $zeroOne = collect($zeroOneData);
        $zeroTwo = collect($zeroTwoData);

        $nol1Lookup = $zeroTwo->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued']]);

        $pipelineItems = $zeroOne->filter(fn($item) => $item['stage'] === 'Pre-procurement' && $item['status'] === 'Subproject Confirmed');
        $approvedItems = $zeroOne->filter(fn($item) => in_array($item['stage'], ['Implementation', 'For procurement', 'Completed']) && !empty($nol1Lookup[$item['sp_id']] ?? null));

        $pipeline = $pipelineItems->count();
        $approved = $approvedItems->count();

        $clusterOrder = ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'];
        $clusterColors = [
            'Luzon A' => '#0047e0',
            'Luzon B' => '#1abc9c',
            'Visayas' => '#3498db',
            'Mindanao' => '#9b59b6',
        ];

        // Group costs by cluster for pipeline and approved
        $pipelineCostsRaw = $pipelineItems
            ->groupBy('cluster')
            ->map(
                fn($items) => $items->reduce(function ($carry, $item) {
                    $cost = $item['cost_during_validation'] ?: $item['sp_indicative_cost'];
                    return $carry + floatval($cost);
                }, 0.0),
            )
            ->toArray();

        $approvedCostsRaw = $approvedItems
            ->groupBy('cluster')
            ->map(
                fn($items) => $items->reduce(function ($carry, $item) {
                    $cost = $item['cost_during_validation'] ?: $item['sp_indicative_cost'];
                    return $carry + floatval($cost);
                }, 0.0),
            )
            ->toArray();

        $pipelineCostsPerCluster = [];
        $approvedCostsPerCluster = [];

        foreach ($clusterOrder as $cluster) {
            $pipelineCostsPerCluster[$cluster] = $pipelineCostsRaw[$cluster] ?? 0.0;
            $approvedCostsPerCluster[$cluster] = $approvedCostsRaw[$cluster] ?? 0.0;
        }


        $pipelineChartData = [];
        $approvedChartData = [];

        foreach ($clusterOrder as $cluster) {
            $pipelineChartData[] = [
                'label' => $cluster,
                'data' => $pipelineCostsPerCluster[$cluster],
                'backgroundColor' => $clusterColors[$cluster],
            ];
            $approvedChartData[] = [
                'label' => $cluster,
                'data' => $approvedCostsPerCluster[$cluster],
                'backgroundColor' => $clusterColors[$cluster],
            ];
        }

        return [
            'pipeline' => $pipeline,
            'approved' => $approved,
            'pipeline_costs_per_cluster' => $pipelineCostsPerCluster, // simple array cluster => cost
            'approved_costs_per_cluster' => $approvedCostsPerCluster, // simple array cluster => cost
            'pipeline_chart_data' => $pipelineChartData, // data ready for charts
            'approved_chart_data' => $approvedChartData, // data ready for charts
        ];
    }

    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.placeholder.section-1');
    }
};

?>
<div class="row row-cols-1 row-cols-lg-2 row-gap-4 mt-5">
    <div class="col">
        <div class="tile-container">
            <div class="tile-title ">Subproject Financing (in Billion Pesos)</div>
            <div class="tile-content position-relative overflow-hidden chart-container" style="height: 400px;"
                x-data="ChartOne()" x-init="init()">
                <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100" id="chart-one"></canvas>
            </div>
            <div>
                <ul class="small mt-2">
                    <li>
                        Cost of approved SPs now constitutes <span id="approved-percentage"
                            class="text-primary">53%</span> of
                        the total allocation for financing
                        rural infrastructures (Php <span id="approved-amount" class="text-primary">18.15 B</span> out of
                        Php
                        <span id="total-budget" class="text-primary">34.28 B</span>)
                    </li>
                    <li>
                        Cost of pipelined SPs is <span id="pipeline-budget-stat" class="text-danger">above</span> the
                        remaining
                        fund (unallocated) for financing rural
                        infrastructures, by <span id="pipeline-excess">39.8</span>% (Php <span id="excess-amount">6.42
                            B</span>)
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="row row-cols-1 row-gap-4 h-100">
            <div class="row">
                <div class="tile-container h-100 d-flex flex-column">
                    <div class="tile-title" style="font-size: 1.2rem;">Cost of Pipelined Subprojects by Cluster (in Billion Pesos)</div>
                    <div class="tile-content position-relative overflow-hidden flex-grow-1 chart-container"
                        x-data="PipelinePieChart()" x-init="init()">
                        <canvas id="chart-cluster-pipeline"
                            class="position-absolute top-0 start-0 bottom-0 end-0"></canvas>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="tile-container h-100 d-flex flex-column">
                    <div class="tile-title" style="font-size: 1.2rem;">Cost of Approved Subprojects by Cluster (in Billion Pesos)</div>
                    <div class="tile-content position-relative overflow-hidden flex-grow-1 chart-container"
                        x-data="ApprovedPieChart()" x-init="init()">
                        <canvas id="chart-cluster-approved"
                            class="position-absolute top-0 start-0 bottom-0 end-0"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@script
<script>
    let clusterChart = null;

    // Draw stacked bar chart with rounded top corners (only for top stack)
    const init_cluster_chart = async (chartData) => {
        const canvas = document.getElementById('chrt-sp-by-cluster');
        if (!canvas) return;

        if (clusterChart) {
            clusterChart.destroy();
            clusterChart = null;
        }

        const ctx = canvas.getContext('2d');

        // Determine Y-axis scale dynamically
        const allValues = chartData.datasets.flatMap(ds => ds.data);
        const maxValue = Math.max(...allValues);
        const step = maxValue <= 50 ? 10 : maxValue <= 100 ? 20 : maxValue <= 200 ? 25 : 50;
        const yMax = Math.ceil(maxValue / step) * step;

        // Custom plugin for rounded top bars
        const roundedBarPlugin = {
            id: 'roundedBarPlugin',
            afterDatasetsDraw(chart) {
                const { ctx, data } = chart;
                const totalDatasets = data.datasets.length;

                data.datasets.forEach((dataset, datasetIndex) => {
                    const isTopStack = datasetIndex === totalDatasets - 1;
                    const isSingleStack = totalDatasets === 1;
                    if (!isTopStack && !isSingleStack) return;

                    const meta = chart.getDatasetMeta(datasetIndex);
                    ctx.save();
                    meta.data.forEach(bar => {
                        const { x, y, base, width } = bar;
                        const radius = 8;
                        const left = x - width / 2;
                        const right = x + width / 2;
                        const top = y;
                        const bottom = base;

                        ctx.beginPath();
                        ctx.fillStyle = dataset.backgroundColor;
                        ctx.moveTo(left, bottom);
                        ctx.lineTo(left, top + radius);
                        ctx.quadraticCurveTo(left, top, left + radius, top);
                        ctx.lineTo(right - radius, top);
                        ctx.quadraticCurveTo(right, top, right, top + radius);
                        ctx.lineTo(right, bottom);
                        ctx.closePath();
                        ctx.fill();
                    });
                    ctx.restore();
                });
            }
        };

        // Initialize the chart
        clusterChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets.map(dataset => ({
                    ...dataset,
                    borderSkipped: false,
                    borderRadius: 0, // handled manually by plugin
                    barPercentage: 0.7,
                    categoryPercentage: 0.8
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: 10 },
                scales: {
                    x: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { color: '#333', font: { size: 12 } }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        max: yMax,
                        ticks: {
                            stepSize: step,
                            color: '#333',
                            font: { size: 12 }
                        },
                        grid: {
                            drawBorder: false,
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: '#000',
                            font: { size: 13 },
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    },
                    datalabels: {
                        display: true,
                        color: '#fff',
                        font: { size: 14, weight: 'bold' },
                        formatter: v => (v === 0 ? '' : v)
                    }
                }
            },
            plugins: [ChartDataLabels, roundedBarPlugin]
        });
    };

    // Example usage (you can trigger this inside update_dashboard_data)
    const fetch_sidlan_portfolio_data = async (params = {}) => {
        try {
            const response = await fetch(`/api/sidlan/portfolio?${new URLSearchParams(params)}`);
            const result = await response.json();
            await init_cluster_chart(result.chartData);
        } catch (error) {
            console.error('Error fetching portfolio chart data:', error);
        }
    };
</script>
@endscript


