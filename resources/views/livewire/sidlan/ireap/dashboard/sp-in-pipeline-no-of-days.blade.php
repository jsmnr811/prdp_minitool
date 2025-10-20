<?php

use Carbon\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;

new class extends Component {
    public $chartData = [];
    public $underBusinessPlanPreparationItems = [];
    public $spNoOfDaysModal = false;
    public $consolidatedTableData = [];
    public $tableData = [];
    public $filterKey = 'All';
    public $loader = false;
    public string $modalTitle = '';
    public string $modalSubtitle = '';
    public array $dataSets = [];

    public function mount(): void
    {
        $this->loader = true;
        $this->initChartData();
    }

    private function initData()
    {
        $underBusinessPlanPreparation = $this->underBusinessPlanPreparation();
        $forRPABApproval = $this->forRPABApproval();
        $RPABApproved = $this->RPABApproved();

        $dataSets['underBusinessPlanPreparation'] = [
            'title' => 'Under Business Plan Preparation',
            'prescribed_timeline' => 204,
            'beyond_timeline_count' => $underBusinessPlanPreparation['beyondTimelineCount'],
            'key' => 'underBusinessPlanPreparation',
            'average_difference_days' => $underBusinessPlanPreparation['average_difference_days'],
            'bar_Label' => '(' . $underBusinessPlanPreparation['beyondTimelineCount'] . ' SPs)',


        ];
        $dataSets['forRPABApproval'] = [
            'title' => 'Under Review /  For RPAB Approval',
            'prescribed_timeline' => 114,
            'beyond_timeline_count' => $forRPABApproval['beyondTimelineCount'],
            'key' => 'forRPABApproval',
            'average_difference_days' => $forRPABApproval['average_difference_days'],
            'bar_Label' => '(' . $forRPABApproval['beyondTimelineCount'] . ' SPs)',

        ];
        $dataSets['rpabApproved'] = [
            'title' => 'RPAB Approved (For NOL 1)',
            'prescribed_timeline' => 120,
            'beyond_timeline_count' => $RPABApproved['beyondTimelineCount'],
            'key' => 'rpabApproved',
            'average_difference_days' => $RPABApproved['average_difference_days'],
            'bar_Label' => '(' . $RPABApproved['beyondTimelineCount'] . ' SPs)',

        ];
        $this->consolidatedTableData['underBusinessPlanPreparation']['subprojectItems'] = $underBusinessPlanPreparation['items'] ?? [];
        $this->consolidatedTableData['underBusinessPlanPreparation']['beyondTimelineItems'] = $underBusinessPlanPreparation['beyondTimeline'] ?? [];

        $this->consolidatedTableData['forRPABApproval']['subprojectItems'] = $forRPABApproval['items'] ?? [];
        $this->consolidatedTableData['forRPABApproval']['beyondTimelineItems'] = $forRPABApproval['beyondTimeline'] ?? [];

        $this->consolidatedTableData['rpabApproved']['subprojectItems'] = $RPABApproved['items'] ?? [];
        $this->consolidatedTableData['rpabApproved']['beyondTimelineItems'] = $RPABApproved['beyondTimeline'] ?? [];
        $this->dataSets = $dataSets;

        return $dataSets;
    }

    private function underBusinessPlanPreparation(): array
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');
        $zeroTwo = collect($irZeroTwoData);

        // Apply filtering based on filterKey
        $items = $zeroTwo->filter(function ($item) {
            $matchesStage = $item['stage'] === 'Pre-procurement';
            $isConfirmed = $item['specific_status'] === 'Subproject Confirmed';
            $hasConfirmedDate = !empty($item['subproject_confirmed']);
            $noBusinessPlan = empty($item['sp_rpab_approved']);

            $baseCondition = $matchesStage && $isConfirmed && $hasConfirmedDate && $noBusinessPlan;

            if ($this->filterKey === 'All') {
                return $baseCondition;
            } elseif (in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) {
                return $baseCondition && $item['cluster'] === $this->filterKey;
            } else {
                return $baseCondition && $item['region'] === $this->filterKey;
            }
        })->map(function ($item) {
            // Determine cost based on priority
            $cost = '-';
            foreach (
                [
                    'cost_nol_1',
                    'rpab_approved_cost',
                    'estimated_project_cost',
                    'cost_during_validation',
                    'indicative_project_cost'
                ] as $field
            ) {
                if (isset($item[$field]) && is_numeric($item[$field]) && (float)$item[$field] > 0) {
                    $cost = '₱' . number_format((float) $item[$field], 2);
                    break;
                }
            }

            return collect($item)->only([
                'cluster',
                'region',
                'province',
                'city_municipality',
                'proponent',
                'project_name',
                'subproject_confirmed',
                'project_type',
                'stage',
                'specific_status'
            ])->merge([
                'cost' => $cost
            ]);
        });

        $now = now();

        // Add date_difference and reformat date
        $items = $items->map(function ($item) use ($now) {
            $confirmedDate = Carbon::parse($item['subproject_confirmed']);
            $dateDiff = $confirmedDate->diffInRealDays($now);
            $formattedDate = $confirmedDate->format('M j, Y');

            return $item->merge([
                'date_difference' => $dateDiff,
                'subproject_confirmed' => $formattedDate,
                'dataset_key' => 'underBusinessPlanPreparation'
            ]);
        });

        $beyondTimeline = $items->filter(function ($item) use ($now) {
            $confirmedDate = Carbon::parse($item['subproject_confirmed']);
            return $confirmedDate->lt($now) && $confirmedDate->diffInRealDays($now) > 204;
        });

        $averageDifferenceDays = $beyondTimeline->avg('date_difference') ?? 0;
        $averageDifferenceDays = round($averageDifferenceDays);

        return [
            'items' => $items,
            'count' => $items->count(),
            'beyondTimeline' => $beyondTimeline,
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => $averageDifferenceDays
        ];
    }

    private function forRPABApproval(): array
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');
        $zeroTwo = collect($irZeroTwoData);

        // Apply filtering based on filterKey
        $items = $zeroTwo->filter(function ($item) {
            $matchesStage = $item['stage'] === 'Pre-procurement';
            $isForRPABApproval = in_array($item['specific_status'], [
                'RPCO Technical Review of Business Plan conducted',
                'Business Plan Package for RPCO technical review submitted'
            ], true);

            $priorityDates = [
                'ima_signed_notarized',
                'sp_rpab_approved',
                'jtr_conducted',
                'rpco_technical_review_conducted',
                'business_plan_packaged',
            ];

            $rpabApprovalDate = null;

            foreach ($priorityDates as $field) {
                if (!empty($item[$field])) {
                    $rpabApprovalDate = $item[$field];
                    break;
                }
            }

            $noNol1Issued = empty($item['nol1_issued']);

            $baseCondition = $matchesStage && $isForRPABApproval && $rpabApprovalDate && $noNol1Issued;

            if ($this->filterKey === 'All') {
                return $baseCondition;
            } elseif (in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) {
                return $baseCondition && $item['cluster'] === $this->filterKey;
            } else {
                return $baseCondition && $item['region'] === $this->filterKey;
            }
        })->map(function ($item) {
            // Determine cost based on priority
            $cost = '-';
            foreach (
                [
                    'cost_nol_1',
                    'rpab_approved_cost',
                    'estimated_project_cost',
                    'cost_during_validation',
                    'indicative_project_cost'
                ] as $field
            ) {
                if (isset($item[$field]) && is_numeric($item[$field]) && (float)$item[$field] > 0) {
                    $cost = '₱' . number_format((float) $item[$field], 2);
                    break;
                }
            }

            // Pick RPAB Approval Date from priority
            $priorityDates = [
                'ima_signed_notarized',
                'sp_rpab_approved',
                'jtr_conducted',
                'rpco_technical_review_conducted',
                'business_plan_packaged',
            ];

            $rpabApprovalDate = null;

            foreach ($priorityDates as $field) {
                if (!empty($item[$field])) {
                    $rpabApprovalDate = $item[$field];
                    break;
                }
            }

            return collect($item)->only([
                'cluster',
                'region',
                'province',
                'city_municipality',
                'proponent',
                'project_name',
                'ima_signed_notarized',
                'sp_rpab_approved',
                'jtr_conducted',
                'rpco_technical_review_conducted',
                'business_plan_packaged',
                'project_type',
                'stage',
                'specific_status'
            ])->merge([
                'cost' => $cost,
                'subproject_confirmed' => $rpabApprovalDate,
                'rpabApprovalDate' => $rpabApprovalDate,
                'dataset_key' => 'forRPABApproval'
            ]);
        });


        $now = now();

        // Add date_difference and reformat date
        $items = $items->map(function ($item) use ($now) {
            $businessPlanPackagedDate = Carbon::parse($item['rpabApprovalDate']);
            $dateDiff = $businessPlanPackagedDate->diffInDays($now);
            $formattedDate = $businessPlanPackagedDate->format('M j, Y');

            return $item->merge([
                'date_difference' => $dateDiff,
                'rpabApprovalDate' => $formattedDate,
                'dataset_key' => 'forRPABApproval'
            ]);
        });

        $beyondTimeline = $items->filter(function ($item) use ($now) {
            $businessPlanPackagedDate = Carbon::parse($item['rpabApprovalDate']);
            return $businessPlanPackagedDate->lt($now) && $businessPlanPackagedDate->diffInDays($now) > 114;
        });

        $averageDifferenceDays = $beyondTimeline->avg('date_difference') ?? 0;
        $averageDifferenceDays = round($averageDifferenceDays);

        return [
            'items' => $items,
            'count' => $items->count(),
            'beyondTimeline' => $beyondTimeline,
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => $averageDifferenceDays
        ];
    }

    private function RPABApproved(): array
    {
        $apiService = new SidlanGoogleSheetService();
        $irZeroTwoData = $apiService->getSheetData('ir-01-002');
        $zeroTwo = collect($irZeroTwoData);

        $items = $zeroTwo->filter(function ($item) {
            $matchesStage = $item['stage'] === 'Pre-procurement';
            $isRPABApproved = in_array($item['specific_status'], [
                'Joint Technical Review (JTR) conducted',
                'SP approved by RPAB',
                'Signing of the IMA',
                'Subproject Issued with No Objection Letter 1'
            ], true);
            $hasNOL1 = !empty($item['nol1_issued']);

            $baseCondition = $matchesStage && $isRPABApproved && $hasNOL1;

            if ($this->filterKey === 'All') {
                return $baseCondition;
            } elseif (in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) {
                return $baseCondition && $item['cluster'] === $this->filterKey;
            } else {
                return $baseCondition && $item['region'] === $this->filterKey;
            }
        })->map(function ($item) {
            // Determine cost based on priority
            $cost = '-';
            foreach (
                [
                    'cost_nol_1',
                    'rpab_approved_cost',
                    'estimated_project_cost',
                    'cost_during_validation',
                    'indicative_project_cost'
                ] as $field
            ) {
                if (isset($item[$field]) && is_numeric($item[$field]) && (float)$item[$field] > 0) {
                    $cost = '₱' . number_format((float) $item[$field], 2);
                    break;
                }
            }

            return collect($item)->only([
                'cluster',
                'region',
                'province',
                'city_municipality',
                'proponent',
                'project_name',
                'nol1_issued',
                'project_type',
                'stage',
                'specific_status'
            ])->merge([
                'cost' => $cost,
                'dataset_key' => 'rpabApproved'
            ]);
        });

        $now = now();

        // Add date_difference and reformat date
        $items = $items->map(function ($item) use ($now) {
            $nol1Date = Carbon::parse($item['nol1_issued']);
            $dateDiff = $nol1Date->diffInRealDays($now);
            $formattedDate = $nol1Date->format('M j, Y');

            return $item->merge([
                'date_difference' => $dateDiff,
                'subproject_confirmed' => $formattedDate,
                'dataset_key' => 'rpabApproved'
            ]);
        });

        $beyondTimeline = $items->filter(function ($item) use ($now) {
            $nol1Date = Carbon::parse($item['nol1_issued']);
            return $nol1Date->lt($now) && $nol1Date->diffInRealDays($now) > 120;
        });

        $averageDifferenceDays = $beyondTimeline->avg('date_difference') ?? 0;
        $averageDifferenceDays = round($averageDifferenceDays);

        return [
            'items' => $items,
            'count' => $items->count(),
            'beyondTimeline' => $beyondTimeline,
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => $averageDifferenceDays
        ];
    }

    public function updatedFilterKey(): void
    {
        $this->loader = true;
        $this->initChartData();
    }

    private function initChartData(): void
    {
        $this->chartData = $this->initData();
        $this->dispatch('generateChartDays', ['chartData' => $this->chartData]);
    }

    #[On('barClicked')]
    public function barClicked($key, $type): void
    {
        $innerKey = $type ? 'beyondTimelineItems' : 'subprojectItems';
        if (isset($this->consolidatedTableData[$key])) {
            $this->tableData = $this->consolidatedTableData[$key][$innerKey];
        } else {
            $this->tableData = [];
        }
        $this->modalSubtitle = $this->dataSets[$key]['title'] ?? '';
        if ($innerKey === 'beyondTimelineItems') {
            $this->modalSubtitle .= ' (No. of SPs Beyond Timeline)';
        }
        $this->spNoOfDaysModal = true;
    }

    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.placeholder.section-2');
    }
};

?>

<div>

    <div class="tile-container h-100 d-flex flex-column">
        <div class="tile-title d-flex flex-column flex-lg-row row-gap-2 justify-content-between align-items-start"
            style="font-size: 1.2rem;">
            <span>I-REAP Subprojects in the Pipeline (No. of Days in the Current Status)</span>
            <div class="d-flex flex-row gap-2 align-items-center small">
                <div class="fw-normal">Show:</div>
                <select wire:model.live="filterKey" class="form-select filter-dropdown pe-lg-5">
                    <option value="All">All</option>
                    <optgroup label="Clusterwide">
                        <option value="Luzon A">Luzon A</option>
                        <option value="Luzon B">Luzon B</option>
                        <option value="Visayas">Visayas</option>
                        <option value="Mindanao">Mindanao</option>
                    </optgroup>
                    <optgroup label="Regionwide">
                        <option value="Cordillera Administrative Region (CAR)" data-group="region">CAR</option>
                        <option value="Ilocos Region (Region I)" data-group="region">Region 01</option>
                        <option value="Cagayan Valley (Region II)" data-group="region">Region 02</option>
                        <option value="Central Luzon (Region III)" data-group="region">Region 03</option>
                        <option value="CALABARZON (Region IV-A)" data-group="region">Region 04A</option>
                        <option value="MIMAROPA (Region IV-B)" data-group="region">Region 04B</option>
                        <option value="Bicol Region (Region V)" data-group="region">Region 05</option>
                        <option value="Western Visayas (Region VI)" data-group="region">Region 06</option>
                        <option value="Central Visayas (Region VII)" data-group="region">Region 07</option>
                        <option value="Eastern Visayas (Region VIII)" data-group="region">Region 08</option>
                        <option value="Zamboanga Peninsula (Region IX)" data-group="region">Region 09</option>
                        <option value="Northern Mindanao (Region X)" data-group="region">Region 10</option>
                        <option value="Davao Region (Region XI)" data-group="region">Region 11</option>
                        <option value="SOCCSKSARGEN (Region XII)" data-group="region">Region 12</option>
                        <option value="Caraga (Region XIII)" data-group="region">Region 13</option>
                        <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)"
                            data-group="region">
                            BARMM</option>
                    </optgroup>
                </select>
            </div>
        </div>


        <div wire:ignore class="tile-content position-relative overflow-hidden chart-container"
            style="height: 400px;">
            @if ($loader)
            <div class="loading-dots my-4 text-center">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>


            @endif
            <canvas id="sp-chart-days"></canvas>
        </div>
    </div>
    @if ($spNoOfDaysModal)
    <div class="modal fade show" id="byDaysModal" tabindex="-1" aria-modal="true" role="dialog" style="display: block;" aria-labelledby="byDaysModalLabel" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header position-relative flex-column align-items-start pb-0" style="border-bottom: none;">
                    <h5 class="modal-title mb-0 fw-bold text-primary" id="byDaysModalLabel">
                        I-REAP Subprojects in the Pipeline (Number of Subprojects by Status)
                    </h5>
                    <small class="text-warning fw-semibold" style="font-size: 1rem;">
                        {{ $modalSubtitle }}
                    </small>
                    <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" wire:click='$set("spNoOfDaysModal", false)' aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if (count($tableData) > 0)
                    <div style="overflow-x: auto;">
                        <table class="table table-hover fix-header-table small mb-0" id="subprojectTable" style="width: auto; min-width: 100%;">
                            <thead>
                                <tr>
                                    <th style="white-space: nowrap;">Cluster</th>
                                    <th style="white-space: nowrap;">Region</th>
                                    <th style="white-space: nowrap;">Province</th>
                                    <th style="white-space: nowrap;">City/Municipality</th>
                                    <th style="white-space: nowrap;">Proponent</th>
                                    <th style="white-space: nowrap;">SP Name</th>
                                    <th style="white-space: nowrap;">Type</th>
                                    <th style="white-space: nowrap;">Cost</th>
                                    <th style="white-space: nowrap;">Stage</th>
                                    <th style="white-space: nowrap;">Status</th>
                                    <th style="white-space: nowrap;">No. of days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tableData as $data)
                                <tr>
                                    <td style="white-space: nowrap;">{{ $data['cluster'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['region'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['province'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['city_municipality'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['proponent'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['project_name'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['project_type'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['cost'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['stage'] }}</td>
                                    <td style="white-space: nowrap;">{{ $data['specific_status'] }}</td>
                                    <td style="white-space: nowrap;">
                                        {{ round($data['date_difference']) }} days from

                                        @php
                                        $formattedDate = \Carbon\Carbon::parse($data['subproject_confirmed'])->format('M j, Y');
                                        @endphp

                                        @if($data['dataset_key'] === 'underBusinessPlanPreparation')
                                        Date of confirmation ({{ $formattedDate }})
                                        @elseif($data['dataset_key'] === 'forRPABApproval')
                                        FS / DED Preparation ({{ $formattedDate }})
                                        @elseif($data['dataset_key'] === 'rpabApproved')
                                        from RPAB Approval ({{ $formattedDate }})
                                        @else
                                        Date of confirmation ({{ $formattedDate }})
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p>No data found.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif

</div>
@script
<script>
    (() => {
        let chartInstanceDays = null;

        function renderDaysChart(chartData) {
            const canvas = document.getElementById('sp-chart-days');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');

            if (chartInstanceDays) {
                chartInstanceDays.destroy();
                chartInstanceDays = null;
            }

            const groupKeys = Object.keys(chartData);

            chartInstanceDays = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: groupKeys.map(key => chartData[key].title),
                    datasets: [{
                            label: 'Prescribed Timeline',
                            backgroundColor: '#0047e0',
                            data: groupKeys.map(key => chartData[key].prescribed_timeline),
                            borderRadius: 8,
                        },
                        {
                            label: 'Average No. of Days',
                            backgroundColor: '#fa2314',
                            data: groupKeys.map(key => chartData[key].average_difference_days),
                            borderRadius: 8,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`
                            }
                        },
                        datalabels: {
                            display: true,
                            color: '#000',
                            font: {
                                size: 14
                            },
                            align: 'end',
                            anchor: 'end',
                            formatter(value, context) {
                                if (context.datasetIndex === 1 && value > 0) {
                                    const key = groupKeys[context.dataIndex];
                                    const data = chartData[key];
                                    const label = (data?.bar_Label || '').replace(/\\n/g, '\n') || `${value} items`;
                                    return [`${value}`, label];
                                }
                                return value > 0 ? `${value}` : '';
                            }
                        }
                    },
                    onClick: (evt, elements) => {
                        if (!elements.length) return;
                        const element = elements[0];
                        const key = groupKeys[element.index];
                        const datasetIndex = element.datasetIndex;
                        Livewire.dispatch('barClicked', {
                            key,
                            type: datasetIndex
                        });
                    }
                },
                plugins: [ChartDataLabels]
            });
        }

        // Listen for a unique event name
        Livewire.on('generateChartDays', data => {
            setTimeout(() => {
                if (data[0] && data[0].chartData) {
                    renderDaysChart(data[0].chartData);
                }
            }, 50);
        });
    })();
</script>
@endscript