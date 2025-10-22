<?php

use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;

/**
 * I-REAP Portfolio Component
 *
 * Displays two donut charts showing the total cost of:
 *  - Pipelined subprojects by cluster
 *  - Approved subprojects by cluster
 *
 * Data Source:
 *  - IR-01-001 (input from mount)
 *  - IR-01-002 (fetched via SidlanGoogleSheetService)
 *
 * Computations:
 *  - Counts and total amounts for both pipeline and approved subprojects
 *  - Cluster-level groupings (Luzon A, Luzon B, Visayas, Mindanao)
 *  - Conversion of amounts to billions (₱)
 *
 * UI:
 *  - Two donut charts rendered using Chart.js and ChartDataLabels
 *  - Full width responsive layout, 300px chart height
 */
new class extends Component {
    /** @var array Raw data from IR-01-001 (passed via mount) */
    public $irZeroOneData = [];

    /** @var int Counts of pipeline, approved, and total projects */
    public $pipelineCount = 0;
    public $approvedCount = 0;
    public $totalCount = 0;

    /** @var float Total project costs in pesos */
    public $pipelineAmount = 0.0;
    public $approvedAmount = 0.0;
    public $totalAmount = 0.0;

    /** @var array Donut chart labels and values */
    public $pipelinedLabels = [];
    public $pipelinedValues = [];
    public $approvedLabels = [];
    public $approvedValues = [];

    /** @var array Expected cluster names for consistent ordering */
    private array $expectedClusters = ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'];

    /**
     * Lifecycle: Initializes component data
     *
     * @param array $irZeroOneData Raw IR-01-001 dataset
     */
    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeTotalsAndCharts();
    }

    /**
     * Normalizes sheet data keys:
     *  - Lowercases all keys
     *  - Replaces spaces and special chars with underscores
     *  - Trims string values
     *
     * @param array $row
     * @return array
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $key = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
            $normalized[$key] = trim((string) $value);
        }

        if (isset($normalized['sp_id'])) {
            $normalized['sp_id'] = strtolower(trim($normalized['sp_id']));
        }

        return $normalized;
    }

    /**
     * Extracts the first valid cost value from several possible fields.
     * Returns 0 if all are empty or zero.
     *
     * @param array $item
     * @return float
     */
    private function extractCost(array $item): float
    {
        foreach ([
            'cost_nol_1',
            'rpab_approved_cost',
            'estimated_project_cost',
            'cost_during_validation',
            'indicative_project_cost'
        ] as $field) {
            if (!empty($item[$field]) && floatval($item[$field]) != 0) {
                return floatval($item[$field]);
            }
        }
        return 0.0;
    }

    /**
     * Core logic:
     *  - Fetches IR-01-002 data
     *  - Filters pipeline and approved items
     *  - Computes totals and prepares chart-ready arrays
     *
     * @return void
     */
    private function computeTotalsAndCharts(): void
    {
        $api = new SidlanGoogleSheetService();
        $irZeroTwoData = $api->getSheetData('ir-01-002');

        // Normalize both sheets
        $zeroOne = collect($this->irZeroOneData)->map(fn($r) => $this->normalizeRow($r));
        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($r) => is_array($r) && count($r) > 0)
            ->map(fn($r) => $this->normalizeRow($r));

        // Create lookup of NOL1 issuance by SP ID
        $nol1Lookup = $zeroTwo
            ->filter(fn($i) => !empty($i['sp_id'] ?? null))
            ->mapWithKeys(fn($i) => [$i['sp_id'] => $i['nol1_issued'] ?? null]);

        // Pipeline: Pre-procurement stage and matching statuses
        $allowedStatuses = [
            'Subproject Confirmed',
            'Business Plan Package for RPCO technical review submitted',
            'RPCO Technical Review of Business Plan conducted',
            'Joint Technical Review (JTR) conducted',
            'SP approved by RPAB',
            'Signing of the IMA',
            'Subproject Issued with No Objection Letter 1',
        ];

        $pipelineItems = $zeroOne->filter(fn($item) =>
            ($item['stage'] ?? '') === 'Pre-procurement'
            && in_array(($item['status'] ?? ''), $allowedStatuses)
            && empty($item['nol1_issued'])
        );

        // Approved: Must have valid NOL1 and proper stage
        $validStages = ['implementation', 'for procurement', 'completed'];
        $approvedItems = $zeroOne->filter(function ($item) use ($nol1Lookup, $validStages) {
            $spId = strtolower(trim($item['sp_id'] ?? ''));
            $stage = strtolower(trim($item['stage'] ?? ''));
            $nol1 = $nol1Lookup[$spId] ?? null;

            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return in_array($stage, $validStages) && $hasNol1;
        });

        // Counts and totals
        $this->pipelineCount = $pipelineItems->count();
        $this->approvedCount = $approvedItems->count();
        $this->totalCount = $this->pipelineCount + $this->approvedCount;

        $this->pipelineAmount = $pipelineItems->sum(fn($i) => $this->extractCost($i));
        $this->approvedAmount = $approvedItems->sum(fn($i) => $this->extractCost($i));
        $this->totalAmount = $this->pipelineAmount + $this->approvedAmount;

        /**
         * Helper: group items by cluster and sum costs
         *
         * @param \Illuminate\Support\Collection $items
         * @return \Illuminate\Support\Collection
         */
        $groupByCluster = fn($items) => $items
            ->groupBy(fn($i) => $i['cluster'] ?? 'Unspecified')
            ->map(fn($g) => $g->sum(fn($r) => $this->extractCost($r)));

        $pipelinedGroups = $groupByCluster($pipelineItems);
        $approvedGroups = $groupByCluster($approvedItems);

        // Enforce consistent cluster order
        $this->pipelinedLabels = $this->expectedClusters;
        $this->approvedLabels = $this->expectedClusters;

        // Convert cost to billions (for chart readability)
        $this->pipelinedValues = collect($this->expectedClusters)
            ->map(fn($c) => round(($pipelinedGroups[$c] ?? 0) / 1_000_000_000, 2));
        $this->approvedValues = collect($this->expectedClusters)
            ->map(fn($c) => round(($approvedGroups[$c] ?? 0) / 1_000_000_000, 2));
    }

    /**
     * Fallback placeholder view when loading
     */
    public function placeholder()
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};
?>

<!-- ==========================
     VIEW (BLADE)
     ========================== -->
<div class="col">
    <div class="row row-cols-1 row-gap-4">

        <!-- Donut Chart: Pipelined Subprojects -->
        <div class="col">
            <div class="tile-container d-flex flex-column align-items-center" style="width:100%; height:300px;">
                <div class="tile-title" style="font-size:1.2rem; margin-bottom:0.5rem;">
                    Cost of Pipelined Subprojects by Cluster (in Billion Pesos)
                </div>
                <div class="tile-content position-relative chart-container" style="flex-grow:1; width:100%;">
                    <canvas id="chrt-cluster-pipelined" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </div>

        <!-- Donut Chart: Approved Subprojects -->
        <div class="col">
            <div class="tile-container d-flex flex-column align-items-center" style="width:100%; height:300px;">
                <div class="tile-title" style="font-size:1.2rem; margin-bottom:0.5rem;">
                    Cost of Approved Subprojects by Cluster (in Billion Pesos)
                </div>
                <div class="tile-content position-relative chart-container" style="flex-grow:1; width:100%;">
                    <canvas id="chrt-cluster-approved" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ==========================
     SCRIPT (Chart.js)
     ========================== -->
<script>
/**
 * Initializes the donut charts for Pipelined and Approved subprojects.
 * Utilizes Chart.js and ChartDataLabels for clean visualization.
 */
document.addEventListener('DOMContentLoaded', () => {
    const colors = ['#004ef5', '#1abc9c', '#3498db', '#9b59b6'];

    /**
     * Create a reusable donut chart instance.
     * @param {string} canvasId - Canvas element ID
     * @param {Array} labels - Chart labels (clusters)
     * @param {Array} values - Chart data values (in billions)
     */
    const createDonutChart = (canvasId, labels, values) => {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data: values,
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
                                const fullValue = ctx.raw * 1_000_000_000; // convert back to pesos
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
                        font: { weight: 'normal', size: 16 },
                        formatter: value => value > 0 ? `₱${value} B` : ''
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    };

    // Render both charts (pipelined + approved)
    createDonutChart('chrt-cluster-pipelined', @json($pipelinedLabels), @json($pipelinedValues));
    createDonutChart('chrt-cluster-approved', @json($approvedLabels), @json($approvedValues));
});
</script>
