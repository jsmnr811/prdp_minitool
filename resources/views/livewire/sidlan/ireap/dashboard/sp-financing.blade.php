<?php

use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;

new class extends Component {
    public $irZeroOneData = [];

    public $approvedCount = 0;
    public $pipelineCount = 0;
    public $totalCount = 0;

    public $approvedAmount = 0.0;
    public $pipelineAmount = 0.0;
    public $totalAmount = 0.0;

    // ✅ Static total allocation: ₱34.28 billion
    public $totalAllocation = 1280000000;

    public function mount($irZeroOneData = []): void
    {
        $this->irZeroOneData = $irZeroOneData;
        $this->computeTotals();
    }

    private function computeTotals()
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');

        $zeroOne = collect($this->irZeroOneData)->map(function ($row) {
            $normalized = [];
            foreach ($row as $key => $value) {
                $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
                $normalized[$normalizedKey] = trim((string)$value);
            }
            $normalized['sp_id'] = isset($normalized['sp_id'])
                ? strtolower(trim($normalized['sp_id']))
                : null;
            return $normalized;
        });

        $zeroTwo = collect($irZeroTwoData)
            ->filter(fn($row) => is_array($row) && count($row) > 0)
            ->map(function ($row) {
                $normalized = [];
                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(str_replace([' ', '-', '/', ':'], '_', trim($key)));
                    $normalized[$normalizedKey] = trim((string)$value);
                }
                $normalized['sp_id'] = isset($normalized['sp_id'])
                    ? strtolower(trim($normalized['sp_id']))
                    : null;
                return $normalized;
            });

        $nol1Lookup = $zeroTwo
            ->filter(fn($item) => !empty($item['sp_id'] ?? null))
            ->mapWithKeys(fn($item) => [$item['sp_id'] => $item['nol1_issued'] ?? null]);

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

        $approvedItems = $zeroOne->filter(function ($item) use ($nol1Lookup) {
            $spId = strtolower($item['sp_id'] ?? '');
            $stage = $item['stage'] ?? '';
            $nol1 = $nol1Lookup[$spId] ?? null;
            $hasNol1 = !empty($nol1) && !in_array(strtolower(trim($nol1)), ['no', 'n/a', 'none', '0']);
            return in_array($stage, ['Implementation', 'For procurement', 'Completed']) && $hasNol1;
        });

        $this->pipelineCount = $pipelineItems->count();
        $this->approvedCount = $approvedItems->count();
        $this->totalCount = $this->pipelineCount + $this->approvedCount;

        // $this->pipelineAmount = $pipelineItems->sum(
        //     fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0)
        // );

        $this->pipelineAmount = $pipelineItems->sum(function ($item) {
            return collect([
                'cost_nol_1',
                'rpab_approved_cost',
                'estimated_project_cost',
                'cost_during_validation',
                'indicative_project_cost',
            ])
                ->map(fn($field) => floatval($item[$field] ?? 0))
                ->first(fn($value) => $value != 0, 0);
        });


        // $this->approvedAmount = $approvedItems->sum(
        //     fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0)
        // );

         $this->approvedAmount = $approvedItems->sum(function ($item) {
            return collect([
                'cost_nol_1',
                'rpab_approved_cost',
                'estimated_project_cost',
                'cost_during_validation',
                'indicative_project_cost',
            ])
                ->map(fn($field) => floatval($item[$field] ?? 0))
                ->first(fn($value) => $value != 0, 0);
        });
        $this->totalAmount = $this->pipelineAmount + $this->approvedAmount;
    }

    public function placeholder()
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};
?>

<div class="col">
    <div class="tile-container">
        <div class="tile-title" style="font-size: 1.2rem;">Subproject Financing (in Billion Pesos)</div>

        <div class="tile-content chart-container" style="height: 400px;">
            <canvas id="chrt-portfolio" style="width:100%; height:100%;"></canvas>
        </div>

        <div>
            <ul class="small mt-3">
                <li>
                    Cost of approved SPs now constitutes
                    <span class="text-primary">
                        {{ $totalAllocation > 0 ? round($approvedAmount / $totalAllocation * 100, 1) : 0 }}%
                    </span>
                    of the total allocation for financing rural infrastructures
                    (<span class="text-primary">
                        ₱{{ formatAmount($approvedAmount) }}
                    </span>
                    out of
                    <span class="text-dark">
                        ₱{{ formatAmount($totalAllocation) }}
                    </span>).
                </li>

                <li class="mt-1">
                    @php
                    $remainingFund = $totalAllocation - $approvedAmount;
                    $difference = $pipelineAmount - $remainingFund;
                    @endphp
                    Cost of pipelined SPs is
                    <span class="
            {{
                $difference > 0
                    ? 'text-danger'
                    : ($difference < 0
                        ? 'text-success'
                        : 'text-primary')
            }}
        ">
                        {{
                $difference > 0
                    ? 'above'
                    : ($difference < 0
                        ? 'below'
                        : 'equal to')
            }}
                    </span>
                    the remaining fund (unallocated) for financing rural infrastructures,
                    by
                    <span class="text-dark">
                        {{ $totalAllocation > 0 ? round(abs(($pipelineAmount - ($totalAllocation - $approvedAmount)) / ($totalAllocation - $approvedAmount)) * 100, 1) : 0 }}%
                    </span>
                    (<span class="text-dark">
                        ₱{{ formatAmount($pipelineAmount) }}
                    </span>).
                </li>
            </ul>
        </div>
    </div>
</div>

@php
function formatAmount($value) {
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

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const canvas = document.getElementById('chrt-portfolio');
        const ctx = canvas.getContext('2d');

        canvas.width = canvas.offsetWidth * window.devicePixelRatio;
        canvas.height = canvas.offsetHeight * window.devicePixelRatio;
        ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

        const approvedAmount = @json($approvedAmount);
        const pipelineAmount = @json($pipelineAmount);
        const totalAllocation = @json($totalAllocation);


        function formatShort(value) {
            if (value >= 1_000_000_000) return (value / 1_000_000_000).toFixed(2) + ' B';
            if (value >= 1_000_000) return (value / 1_000_000).toFixed(2) + ' M';
            if (value >= 1_000) return (value / 1_000).toFixed(2) + ' K';
            return value.toFixed(2);
        }

        function formatFull(value) {
            return '₱ ' + value.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Subproject Portfolio'],
                datasets: [{
                        label: 'Total Allocation',
                        data: [totalAllocation],
                        backgroundColor: '#004ef5',
                        borderColor: '#004ef5',
                        borderWidth: 1,
                        borderSkipped: 'bottom',
                        borderRadius: {
                            topLeft: 10,
                            topRight: 10
                        },
                        stack: 'Base',
                        order: 1
                    },
                    {
                        label: 'Approved',
                        data: [approvedAmount],
                        backgroundColor: '#007bff',
                        stack: 'Funding',
                        order: 2,
                        borderSkipped: false,
                        borderRadius: 0
                    },
                    {
                        label: 'Pipeline',
                        data: [pipelineAmount],
                        backgroundColor: '#1abc9c',
                        stack: 'Funding',
                        order: 2,
                        borderSkipped: 'bottom',
                        borderRadius: {
                            topLeft: 10,
                            topRight: 10
                        }
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: ${formatFull(ctx.raw)}`
                        }
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 12
                        },
                        anchor: 'center',
                        align: 'center',
                        formatter: (value) => formatShort(value)
                    }
                },
                scales: {
                    x: {
                        stacked: true
                    },
                    y: {
                        beginAtZero: true,
                        suggestedMax: totalAllocation,
                        ticks: {
                            callback: (value) => formatShort(value)
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    });
</script>