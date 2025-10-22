<?php

use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;

/**
 * I-REAP Subproject Financing Overview Component
 *
 * Displays a stacked bar chart showing:
 *  - Total allocation
 *  - Cost of approved subprojects
 *  - Cost of pipelined subprojects
 *
 * Data Sources:
 *  - IR-01-001: Initial subproject data
 *  - IR-01-002: No Objection Letter (NOL1) issuance and approval data
 *
 * Key Metrics:
 *  - Counts and costs of approved and pipelined subprojects
 *  - Comparison of total allocation vs. commitments
 *
 * Visualization:
 *  - Chart.js stacked bar chart with formatted labels and tooltips
 *  - Legend showing allocation and spending progress
 */
new class extends Component {
    /** @var array Raw data from IR-01-001 sheet */
    public $irZeroOneData = [];

    /** @var int Project counts by type */
    public $approvedCount = 0;
    public $pipelineCount = 0;
    public $totalCount = 0;

    /** @var float Project costs by type (in pesos) */
    public $approvedAmount = 0.0;
    public $pipelineAmount = 0.0;
    public $totalAmount = 0.0;

    /** @var float Total allocated fund for rural infrastructure */
    public $totalAllocation = 1_280_000_000; // ₱1.28 billion

    /**
     * Lifecycle method to initialize data.
     *
     * @param array $irZeroOneData Input dataset for IR-01-001
     */
    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeTotals();
    }

    /**
     * Normalizes sheet data:
     *  - Converts keys to lowercase with underscores
     *  - Trims all string values
     *
     * @param array $row
     * @return array
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
            $normalized[$normalizedKey] = trim((string) $value);
        }
        if (isset($normalized['sp_id'])) {
            $normalized['sp_id'] = strtolower(trim($normalized['sp_id']));
        }
        return $normalized;
    }

    /**
     * Extracts the first valid cost value from known cost fields.
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
            'indicative_project_cost',
        ] as $field) {
            $value = floatval($item[$field] ?? 0);
            if ($value > 0) {
                return $value;
            }
        }
        return 0.0;
    }

    /**
     * Computes project totals for both pipeline and approved stages.
     * Uses IR-01-001 for base data and IR-01-002 for NOL1 verification.
     */
    private function computeTotals(): void
    {
        $api = new SidlanGoogleSheetService();
        $irZeroTwoData = $api->getSheetData('ir-01-002');

        // Normalize both datasets
        $zeroOne = collect($this->irZeroOneData)->map(fn($r) => $this->normalizeRow($r));
        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($r) => is_array($r) && count($r) > 0)
            ->map(fn($r) => $this->normalizeRow($r));

        // Create lookup table for NOL1 issuance
        $nol1Lookup = $zeroTwo
            ->filter(fn($item) => !empty($item['sp_id'] ?? null))
            ->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued'] ?? null]);

        // Define project stage/status filters
        $pipelineStatuses = [
            'Subproject Confirmed',
            'Business Plan Package for RPCO technical review submitted',
            'RPCO Technical Review of Business Plan conducted',
            'Joint Technical Review (JTR) conducted',
            'SP approved by RPAB',
            'Signing of the IMA',
            'Subproject Issued with No Objection Letter 1',
        ];

        // Pipeline: pre-procurement stage, no NOL1 yet
        $pipelineItems = $zeroOne->filter(fn($item) =>
            ($item['stage'] ?? '') === 'Pre-procurement'
            && in_array(($item['status'] ?? ''), $pipelineStatuses)
            && empty($item['nol1_issued'])
        );

        // Approved: valid NOL1 and proper stage
        $approvedItems = $zeroOne->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower($item['sp_id'] ?? '');
            $stage = strtolower($item['stage'] ?? '');
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return in_array($stage, ['implementation', 'for procurement', 'completed']) && $hasNol1;
        });

        // Count totals
        $this->pipelineCount = $pipelineItems->count();
        $this->approvedCount = $approvedItems->count();
        $this->totalCount = $this->pipelineCount + $this->approvedCount;

        // Cost totals
        $this->pipelineAmount = $pipelineItems->sum(fn($i) => $this->extractCost($i));
        $this->approvedAmount = $approvedItems->sum(fn($i) => $this->extractCost($i));
        $this->totalAmount = $this->pipelineAmount + $this->approvedAmount;
    }

    /**
     * Placeholder view displayed while loading data.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function placeholder()
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};
?>

<!-- ===============================
     VIEW (BLADE)
     =============================== -->
<div class="col">
    <div class="tile-container w-100">
        <div class="tile-title" style="font-size: 1.2rem;">
            Subproject Financing (in Billion Pesos)
        </div>

        <!-- Chart Container -->
        <div class="tile-content chart-container" style="height: 400px;">
            <canvas id="chrt-portfolio" class="w-100 h-100"></canvas>
        </div>

        <!-- Descriptive Summary -->
        <div class="mt-3 small">
            <ul>
                <li>
                    Cost of approved SPs now constitutes
                    <span class="text-primary">
                        {{ $totalAllocation > 0 ? round($approvedAmount / $totalAllocation * 100, 1) : 0 }}%
                    </span>
                    of the total allocation for financing rural infrastructures
                    (<span class="text-primary">₱{{ formatAmount($approvedAmount) }}</span>
                    out of
                    <span class="text-dark">₱{{ formatAmount($totalAllocation) }}</span>).
                </li>

                @php
                    $remainingFund = $totalAllocation - $approvedAmount;
                    $difference = $pipelineAmount - $remainingFund;
                @endphp

                <li class="mt-1">
                    Cost of pipelined SPs is
                    <span class="{{ $difference > 0 ? 'text-danger' : ($difference < 0 ? 'text-success' : 'text-primary') }}">
                        {{ $difference > 0 ? 'above' : ($difference < 0 ? 'below' : 'equal to') }}
                    </span>
                    the remaining unallocated fund for financing rural infrastructures, by
                    <span class="text-dark">
                        {{ $totalAllocation > 0 ? round(abs(($pipelineAmount - $remainingFund) / $remainingFund) * 100, 1) : 0 }}%
                    </span>
                    (<span class="text-dark">₱{{ formatAmount($pipelineAmount) }}</span>).
                </li>
            </ul>
        </div>
    </div>
</div>

@php
/**
 * Formats numeric amounts to human-readable short units.
 * e.g. ₱1.25B, ₱10.5M, ₱500K
 */
function formatAmount($value)
{
    if ($value >= 1_000_000_000) {
        return number_format($value / 1_000_000_000, 2) . ' B';
    } elseif ($value >= 1_000_000) {
        return number_format($value / 1_000_000, 2) . ' M';
    } elseif ($value >= 1_000) {
        return number_format($value / 1_000, 2) . ' K';
    } else {
        return number_format($value, 2);
    }
}
@endphp

<!-- ===============================
     SCRIPT (Chart.js)
     =============================== -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('chrt-portfolio');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const { devicePixelRatio } = window;

    // Adjust resolution for crisp charts
    canvas.width = canvas.offsetWidth * devicePixelRatio;
    canvas.height = canvas.offsetHeight * devicePixelRatio;
    ctx.scale(devicePixelRatio, devicePixelRatio);

    const approvedAmount = @json($approvedAmount);
    const pipelineAmount = @json($pipelineAmount);
    const totalAllocation = @json($totalAllocation);

    const formatShort = (value) => {
        if (value >= 1_000_000_000) return (value / 1_000_000_000).toFixed(2) + ' B';
        if (value >= 1_000_000) return (value / 1_000_000).toFixed(2) + ' M';
        if (value >= 1_000) return (value / 1_000).toFixed(2) + ' K';
        return value.toFixed(2);
    };

    const formatFull = (value) =>
        '₱ ' + value.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // Determine which dataset is on top
    const topDataset = pipelineAmount > 0 ? 'Pipeline' : (approvedAmount > 0 ? 'Approved' : null);

    // Helper to determine border radius dynamically
    const getBorderRadius = (label) => {
        return label === topDataset
            ? { topLeft: 10, topRight: 10 }
            : 0;
    };

    // Initialize chart
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Subproject Portfolio'],
            datasets: [
                {
                    label: 'Total Allocation',
                    data: [totalAllocation],
                    backgroundColor: '#004ef5',
                    borderRadius: 10,
                    stack: 'Base',
                    order: 1
                },
                {
                    label: 'Approved',
                    data: [approvedAmount],
                    backgroundColor: '#007bff',
                    borderRadius: getBorderRadius('Approved'),
                    stack: 'Funding',
                    order: 2
                },
                {
                    label: 'Pipeline',
                    data: [pipelineAmount],
                    backgroundColor: '#1abc9c',
                    borderRadius: getBorderRadius('Pipeline'),
                    stack: 'Funding',
                    order: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${formatFull(ctx.raw)}`
                    }
                },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 12 },
                    anchor: 'center',
                    align: 'center',
                    formatter: (value) => formatShort(value)
                }
            },
            scales: {
                x: { stacked: true },
                y: {
                    beginAtZero: true,
                    suggestedMax: totalAllocation,
                    ticks: { callback: (value) => formatShort(value) }
                }
            }
        },
        plugins: [ChartDataLabels]
    });
});
</script>

