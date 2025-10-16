<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Livewire\Attributes\On;

new class extends Component {
    public $irZeroOneData = [];
    public $filterCluster = 'All';
    public $filterType = 'All';
    public $tableData = [];

    protected $listeners = ['filterUpdated'];

    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeTableData();
    }

    #[On('filter-updated')]
    public function filterUpdated($cluster, $type)
    {
        $this->filterCluster = $cluster;
        $this->filterType = $type;
        $this->computeTableData();
    }

    private function computeTableData()
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
        $pipelineItems = $filtered->filter(
            fn($item) => ($item['stage'] ?? '') === 'Pre-procurement' &&
                ($item['status'] ?? '') === 'Subproject Confirmed'
        );

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

        // Compute table data
        $this->tableData = collect($spTypes)->map(function ($type) use ($pipelineItems, $approvedItems) {
            $pipelineType = $pipelineItems->where('sp_type', $type);
            $approvedType = $approvedItems->where('sp_type', $type);

            $pipelineCount = $pipelineType->count();
            $pipelineCost = $pipelineType->sum(fn($i) => floatval($i['cost_during_validation'] ?? $i['sp_indicative_cost'] ?? 0));

            $approvedCount = $approvedType->count();
            $approvedCost = $approvedType->sum(fn($i) => floatval($i['cost_during_validation'] ?? $i['sp_indicative_cost'] ?? 0));

            return [
                'type' => $type,
                'pipeline_count' => $pipelineCount,
                'pipeline_cost' => $pipelineCost,
                'approved_count' => $approvedCount,
                'approved_cost' => $approvedCost,
                'total_count' => $pipelineCount + $approvedCount,
                'total_cost' => $pipelineCost + $approvedCost,
            ];
        })->sortBy('type')->values()->toArray();
    }

    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};
?>

<div class="col-12">
    <div class="tile-container">
        <div class="tile-title">Summary by Subproject Type</div>
        <div class="tile-content">
            <div class="table-responsive">
                <table class="table content-table text-center" id="tbl-summary-type"
                       style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <colgroup>
                        <col style="width: 30%;">   <!-- Type -->
                        <col style="width: 5%;">    <!-- Pipelined NO. -->
                        <col style="width: 18.33%;"> <!-- Pipelined COST -->
                        <col style="width: 5%;">    <!-- Approved NO. -->
                        <col style="width: 18.33%;"> <!-- Approved COST -->
                        <col style="width: 5%;">    <!-- Total NO. -->
                        <col style="width: 18.33%;"> <!-- Total COST -->
                    </colgroup>

                    <thead>
                        <tr>
                            <th class="border" rowspan="2">Type</th>
                            <th class="border" colspan="2">Pipelined</th>
                            <th class="border" colspan="2">Approved</th>
                            <th class="border" colspan="2">Total</th>
                        </tr>
                        <tr>
                            <th class="border">NO.</th>
                            <th class="border">COST</th>
                            <th class="border">NO.</th>
                            <th class="border">COST</th>
                            <th class="border">NO.</th>
                            <th class="border">COST</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tableData as $row)
                        <tr>
                            <td class="border text-start" style="word-wrap: break-word;">
                                {{ $row['type'] }}
                            </td>

                            {{-- Pipelined --}}
                            <td class="border text-center">{{ number_format($row['pipeline_count']) }}</td>
                            <td class="border text-end">₱ {{ number_format($row['pipeline_cost'], 2) }}</td>

                            {{-- Approved --}}
                            <td class="border text-center">{{ number_format($row['approved_count']) }}</td>
                            <td class="border text-end">₱ {{ number_format($row['approved_cost'], 2) }}</td>

                            {{-- Total --}}
                            <td class="border text-center">{{ number_format($row['total_count']) }}</td>
                            <td class="border text-end">₱ {{ number_format($row['total_cost'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td class="border text-center" colspan="7">No data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

