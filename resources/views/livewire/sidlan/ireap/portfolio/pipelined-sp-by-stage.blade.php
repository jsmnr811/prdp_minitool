<?php

use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Livewire\Attributes\On;

new class extends Component {
    public $irZeroOneData = [];
    public $filterCluster = 'All';
    public $filterType = 'All';

    public $pipelineCount = 0;
    public $pipelineAmount = 0.0;

    protected $listeners = ['filterUpdated'];

    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computePipelineTotals();
    }

    #[On('filter-updated')]
    public function filterUpdated($cluster, $type)
    {
        $this->filterCluster = $cluster;
        $this->filterType = $type;
        $this->computePipelineTotals();
    }

    private function computePipelineTotals()
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');

        // Normalize ZERO ONE
        $zeroOne = collect($this->irZeroOneData)->map(function ($row) {
            $normalized = [];
            foreach ($row as $key => $value) {
                $normalized[$key = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)))] = trim((string)$value);
            }
            $normalized['sp_id'] = $normalized['sp_id'] ?? null;
            return $normalized;
        });

        // Normalize ZERO TWO
        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($row) => is_array($row) && count($row) > 0)
            ->map(function ($row) {
                $normalized = [];
                foreach ($row as $key => $value) {
                    $normalized[strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)))] = trim((string)$value);
                }
                $normalized['sp_id'] = $normalized['sp_id'] ?? null;
                return $normalized;
            });

        // NOL1 lookup
        $nol1Lookup = $zeroTwo
            ->filter(fn($item) => !empty($item['sp_id']))
            ->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued'] ?? null]);

        // Apply filters
        $filtered = $zeroOne->filter(function ($item) {
            $clusterMatch = $this->filterCluster === 'All' || ($item['cluster'] ?? '') === $this->filterCluster;
            $typeMatch = $this->filterType === 'All' || ($item['project_type'] ?? '') === $this->filterType;
            return $clusterMatch && $typeMatch;
        });

        // Pipeline items: only Pre-procurement stage and no NOL1
        $pipelineItems = $filtered->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower($item['sp_id'] ?? '');
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return ($item['stage'] ?? '') === 'Pre-procurement' && !$hasNol1;
        });

        $this->pipelineCount = $pipelineItems->count();

        $this->pipelineAmount = $pipelineItems->sum(function ($item) {
            $fields = [
                'cost_nol_1',
                'rpab_approved_cost',
                'estimated_project_cost',
                'cost_during_validation',
                'indicative_project_cost',
            ];
            foreach ($fields as $field) {
                if (!empty($item[$field])) {
                    return floatval(str_replace([',', '₱', ' '], '', $item[$field]));
                }
            }
            return 0;
        });
    }
};
?>
<div class="col">
    <div class="tile-container">
        <div class="tile-title">Pipelined Subproject Cost by Stage</div>
        <div class="card border-0 shadow-sm" style="background-color: #e0f0ff; border-left: 5px solid #0047e0; cursor: default;">
            <div class="card-body">
                <h6 class="fw-bold mb-2" style="color: #0047e0; text-align: left;">Pre-Procurement Stage</h6>
                <h4 class="fw-bold mb-0" style="color: #0047e0;">
                    PhP {{ number_format($pipelineAmount, 2) }}
                </h4>
            </div>
        </div>
    </div>
</div>
