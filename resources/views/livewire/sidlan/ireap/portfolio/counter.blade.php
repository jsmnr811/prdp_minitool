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

    public $approvedAmount = 0.0;
    public $pipelineAmount = 0.0;
    public $totalAmount = 0.0;

    public $filterCluster = 'All';
    public $filterType = 'All';

    protected $listeners = ['filterUpdated'];

    // Mount initial data
    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeFilteredTotals();
    }

    // Handle filter event from Filter component

    #[On('filter-updated')]
    public function filterUpdated($cluster, $type)
    {
        $this->filterCluster = $cluster;
        $this->filterType = $type;
        $this->computeFilteredTotals();
    }
    // Compute filtered totals
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

    // --- Totals ---
    $this->pipelineCount = $pipelineItems->count();
    $this->approvedCount = $approvedItems->count();
    $this->totalCount = $this->pipelineCount + $this->approvedCount;

    $this->pipelineAmount = $pipelineItems->sum(
        fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0)
    );

    $this->approvedAmount = $approvedItems->sum(
        fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0)
    );

    $this->totalAmount = $this->pipelineAmount + $this->approvedAmount;
}




    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};

?>

<div class="row row-cols-1 row-cols-lg-3 mt-5 row-gap-3">
    <!-- APPROVED -->
    <div class="col">
        <div class="score-card border border-primary text-primary border-2 bg-white">
            <div class="title">Total Approved SPs</div>
            <div class="value" id="approved-count">
                {{ number_format($approvedCount) }} Projects
            </div>
            <div class="sub-value" id="approved-amount">
                PhP {{ number_format($approvedAmount, 2) }}
            </div>
        </div>
    </div>

    <!-- PIPELINED -->
    <div class="col">
        <div class="score-card border border-2 bg-white" style="color: #1abc9c; border-color: #1abc9c !important;">
            <div class="title">Total Pipelined SPs</div>
            <div class="value" id="pipeline-count">
                {{ number_format($pipelineCount) }} Projects
            </div>
            <div class="sub-value" id="pipeline-amount">
                PhP {{ number_format($pipelineAmount, 2) }}
            </div>
        </div>
    </div>

    <!-- TOTAL -->
    <div class="col">
        <div class="score-card border border-2 bg-white" style="color: #3498db; border-color: #3498db !important;">
            <div class="title">Total Subprojects</div>
            <div class="value" id="total-count">
                {{ number_format($totalCount) }} Projects
            </div>
            <div class="sub-value" id="total-amount">
                PhP {{ number_format($totalAmount, 2) }}
            </div>
        </div>
    </div>
</div>