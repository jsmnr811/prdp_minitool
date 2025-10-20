<?php

use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;

new class extends Component {
    public $irZeroOneData = [];

    // Counts & amounts
    public $pipelineCount = 0;
    public $approvedCount = 0;
    public $totalCount = 0;

    public $pipelineAmount = 0.0;
    public $approvedAmount = 0.0;
    public $totalAmount = 0.0;

    // Donut chart data
    public $pipelinedLabels = [];
    public $pipelinedValues = [];
    public $approvedLabels = [];
    public $approvedValues = [];

    // Expected clusters
    private $expectedClusters = ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'];

    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeTotalsAndCharts();
    }

    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
            $normalized[$normalizedKey] = trim((string)$value);
        }
        $normalized['sp_id'] = isset($normalized['sp_id']) ? strtolower(trim($normalized['sp_id'])) : null;
        return $normalized;
    }

    private function computeTotalsAndCharts()
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');

        // Normalize IR-01-001 and IR-01-002
        $zeroOne = collect($this->irZeroOneData)->map(fn($row) => $this->normalizeRow($row));
        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($row) => is_array($row) && count($row) > 0)
            ->map(fn($row) => $this->normalizeRow($row));

        // NOL1 lookup
        $nol1Lookup = $zeroTwo
            ->filter(fn($item) => !empty($item['sp_id'] ?? null))
            ->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued'] ?? null]);

        // Pipelined items
        // $pipelineItems = $zeroOne->filter(fn($item) =>
        //     strtolower(trim($item['stage'] ?? '')) === 'pre-procurement' &&
        //     strtolower(trim($item['status'] ?? '')) === 'subproject confirmed'
        // );
        $pipelineItems = $zeroOne->filter(
            fn($item) => ($item['stage'] ?? '') === 'Pre-procurement'
                && in_array(($item['status'] ?? ''), [
                    'Subproject Confirmed',
                    'Business Plan Package for RPCO technical review submitted',
                    'RPCO Technical Review of Business Plan conducted',
                    'Joint Technical Review (JTR) conducted',
                    'SP approved by RPAB',
                    'Signing of the IMA',
                    'Subproject Issued with No Objection Letter 1',
                ]) && empty($item['nol1_issued'])
        );


        // Approved items
        $approvedItems = $zeroOne->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower(trim($item['sp_id'] ?? ''));
            $stage = strtolower(trim($item['stage'] ?? ''));
            $validStages = ['implementation', 'for procurement', 'completed'];

            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);

            return in_array($stage, $validStages) && $hasNol1;
        });

        // Counts
        $this->pipelineCount = $pipelineItems->count();
        $this->approvedCount = $approvedItems->count();
        $this->totalCount = $this->pipelineCount + $this->approvedCount;

        // Amounts
        // $this->pipelineAmount = $pipelineItems->sum(fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0));
        $this->pipelineAmount = $pipelineItems->sum(function ($item) {
            $fields = [
                'cost_nol_1',
                'rpab_approved_cost',
                'estimated_project_cost',
                'cost_during_validation',
                'indicative_project_cost',
            ];

            foreach ($fields as $field) {
                if (!empty($item[$field]) && floatval($item[$field]) != 0) {
                    return floatval($item[$field]);
                }
            }

            return 0;
        });
        // $this->approvedAmount = $approvedItems->sum(fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0));
        $this->approvedAmount = $approvedItems->sum(function ($item) {
            $fields = [
                'cost_nol_1',
                'rpab_approved_cost',
                'estimated_project_cost',
                'cost_during_validation',
                'indicative_project_cost',
            ];

            foreach ($fields as $field) {
                if (!empty($item[$field]) && floatval($item[$field]) != 0) {
                    return floatval($item[$field]);
                }
            }

            return 0;
        });
        $this->totalAmount = $this->pipelineAmount + $this->approvedAmount;

        // Prepare donut chart data
        $this->pipelinedLabels = $this->expectedClusters;
        $this->approvedLabels = $this->expectedClusters;

        // $pipelinedGroups = $pipelineItems
        //     ->groupBy(fn($item) => $item['cluster'] ?? 'Unspecified')
        //     ->map(fn($group) => $group->sum(fn($row) => floatval($row['cost_during_validation'] ?? $row['sp_indicative_cost'] ?? 0)));

        // $approvedGroups = $approvedItems
        //     ->groupBy(fn($item) => $item['cluster'] ?? 'Unspecified')
        //     ->map(fn($group) => $group->sum(fn($row) => floatval($row['cost_during_validation'] ?? $row['sp_indicative_cost'] ?? 0)));

        $pipelinedGroups = $pipelineItems
            ->groupBy(fn($item) => $item['cluster'] ?? 'Unspecified')
            ->map(function ($group) {
                return $group->sum(function ($row) {
                    $fields = [
                        'cost_nol_1',
                        'rpab_approved_cost',
                        'estimated_project_cost',
                        'cost_during_validation',
                        'indicative_project_cost',
                    ];

                    foreach ($fields as $field) {
                        if (!empty($row[$field]) && floatval($row[$field]) != 0) {
                            return floatval($row[$field]);
                        }
                    }

                    return 0;
                });
            });

        $approvedGroups = $approvedItems
            ->groupBy(fn($item) => $item['cluster'] ?? 'Unspecified')
            ->map(function ($group) {
                return $group->sum(function ($row) {
                    $fields = [
                        'cost_nol_1',
                        'rpab_approved_cost',
                        'estimated_project_cost',
                        'cost_during_validation',
                        'indicative_project_cost',
                    ];

                    foreach ($fields as $field) {
                        if (!empty($row[$field]) && floatval($row[$field]) != 0) {
                            return floatval($row[$field]);
                        }
                    }

                    return 0;
                });
            });


        // Ensure all clusters exist
        $pipelinedGroups = collect($this->expectedClusters)
            ->mapWithKeys(fn($cluster) => [$cluster => $pipelinedGroups[$cluster] ?? 0]);

        $approvedGroups = collect($this->expectedClusters)
            ->mapWithKeys(fn($cluster) => [$cluster => $approvedGroups[$cluster] ?? 0]);

        $this->pipelinedValues = $pipelinedGroups->values()->map(fn($v) => round($v / 1_000_000_000, 2));
        $this->approvedValues = $approvedGroups->values()->map(fn($v) => round($v / 1_000_000_000, 2));
    }

    public function placeholder()
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};
?>

<div class="row row-cols-1 row-gap-4">
    <!-- Chart 1: Pipelined -->
    <div class="col">
        <div class="tile-container d-flex flex-column align-items-center" style="width:100%; height:300px;">
            <div class="tile-title" style="font-size:1.2rem; margin-bottom:0.5rem;">Cost of Pipelined Subprojects by Cluster (in Billion Pesos)</div>
            <div class="tile-content position-relative chart-container" style="flex-grow:1; width:100%;">
                <canvas id="chrt-cluster-pipelined" style="width:100%; height:100%;"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart 2: Approved -->
    <div class="col">
        <div class="tile-container d-flex flex-column align-items-center" style="width:100%; height:300px;">
            <div class="tile-title" style="font-size:1.2rem; margin-bottom:0.5rem;">Cost of Approved Subprojects by Cluster (in Billion Pesos)</div>
            <div class="tile-content position-relative chart-container" style="flex-grow:1; width:100%;">
                <canvas id="chrt-cluster-approved" style="width:100%; height:100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const colors = ['#004ef5', '#1abc9c', '#3498db', '#9b59b6'];

        // Chart 1: Pipelined
        const ctxPipelined = document.getElementById('chrt-cluster-pipelined');
        if (ctxPipelined) {
            new Chart(ctxPipelined, {
                type: 'doughnut',
                data: {
                    labels: @json($pipelinedLabels),
                    datasets: [{
                        data: @json($pipelinedValues),
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '37%',
                    radius: '64%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 15,
                                padding: 10,
                                color: '#000'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const fullValue = ctx.raw * 1_000_000_000; // convert back to full pesos
                                    return '₱' + fullValue.toLocaleString('en-PH', {
                                        maximumFractionDigits: 0
                                    });
                                }
                            }
                        },

                        datalabels: {
                            color: ctx => colors[ctx.dataIndex],
                            align: 'end',
                            anchor: 'end',
                            offset: 8,
                            font: {
                                weight: 'normal',
                                size: 16
                            },
                            formatter: value => value > 0 ? `₱${value} B` : ''
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        // Chart 2: Approved
        const ctxApproved = document.getElementById('chrt-cluster-approved');
        if (ctxApproved) {
            new Chart(ctxApproved, {
                type: 'doughnut',
                data: {
                    labels: @json($approvedLabels),
                    datasets: [{
                        data: @json($approvedValues),
                        backgroundColor: colors,
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '37%',
                    radius: '64%',
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                boxWidth: 15,
                                padding: 10,
                                color: '#000'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => {
                                    const fullValue = ctx.raw * 1_000_000_000; // convert back to full pesos
                                    return '₱' + fullValue.toLocaleString('en-PH', {
                                        maximumFractionDigits: 0
                                    });
                                }
                            }
                        },
                        datalabels: {
                            color: ctx => colors[ctx.dataIndex],
                            align: 'end',
                            anchor: 'end',
                            offset: 8,
                            font: {
                                weight: 'normal',
                                size: 16
                            },
                            formatter: value => value > 0 ? `₱${value} B` : ''
                        }
                    }
                },
                plugins: [ChartDataLabels]
            });
        }
    });
</script>