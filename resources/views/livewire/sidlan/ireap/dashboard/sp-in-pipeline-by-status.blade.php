<?php

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Carbon\Carbon;

new class extends Component {
    public $chartData = [];
    public $underBusinessPlanPreparationItems = [];
    public $subprojectModal = false;
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

    private function initChartData(): void
    {
        $this->chartData = $this->initData();
        $this->dispatch('generateChart', ['chartData' => $this->chartData]);
    }

    private function initData(): array
    {
        $sections = [
            'underBusinessPlanPreparation' => $this->underBusinessPlanPreparation(),
            'forRPABApproval' => $this->forRPABApproval(),
            'rpabApproved' => $this->RPABApproved(),
        ];

        $dataSets = [];

        foreach ($sections as $key => $data) {
            $titleMap = [
                'underBusinessPlanPreparation' => 'Under Business Plan Preparation',
                'forRPABApproval' => 'Under Review /  For RPAB Approval',
                'rpabApproved' => 'RPAB Approved (For NOL 1)',
            ];

            $timelineMap = [
                'underBusinessPlanPreparation' => 204,
                'forRPABApproval' => 114,
                'rpabApproved' => 120,
            ];

            $dataSets[$key] = [
                'title' => $titleMap[$key],
                'subject_count' => $data['count'],
                'beyond_timeline_count' => $data['beyondTimelineCount'],
                'key' => $key,
                'average_difference_days' => $data['average_difference_days'],
                'bar_Label' => $data['average_difference_days'] . " days vs \n {$timelineMap[$key]} days timeline",
            ];

            $this->consolidatedTableData[$key]['subprojectItems'] = $data['items'] ?? [];
            $this->consolidatedTableData[$key]['beyondTimelineItems'] = $data['beyondTimeline'] ?? [];
        }

        $this->dataSets = $dataSets;

        return $dataSets;
    }

    private function fetchDataFromSheet(string $sheetName): \Illuminate\Support\Collection
    {
        $service = new SidlanGoogleSheetService();
        return collect($service->getSheetData($sheetName));
    }

    private function calculateCost(array $item): string
    {
        foreach (['cost_nol_1', 'rpab_approved_cost', 'estimated_project_cost', 'cost_during_validation', 'indicative_project_cost'] as $field) {
            if (!empty($item[$field]) && is_numeric($item[$field]) && (float)$item[$field] > 0) {
                return '₱' . number_format((float)$item[$field], 2);
            }
        }
        return '-';
    }

    private function prepareItems(\Illuminate\Support\Collection $items, string $dateField, string $datasetKey, int $timelineDays): array
    {
        $now = now();

        $items = $items->map(function ($item) use ($dateField, $datasetKey, $now) {
            $date = Carbon::parse($item[$dateField]);
            return collect($item)
                ->merge([
                    'cost' => $this->calculateCost($item),
                    'date_difference' => $date->diffInRealDays($now),
                    $datasetKey === 'forRPABApproval' ? 'rpabApprovalDate' : 'subproject_confirmed' => $date->format('M j, Y'),
                    'dataset_key' => $datasetKey
                ]);
        });

        $beyondTimeline = $items->filter(fn($item) => Carbon::parse($item[$dateField])->diffInRealDays($now) > $timelineDays);

        $averageDifferenceDays = round($beyondTimeline->avg('date_difference') ?? 0);

        return [
            'items' => $items,
            'count' => $items->count(),
            'beyondTimeline' => $beyondTimeline,
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => $averageDifferenceDays,
        ];
    }

    private function underBusinessPlanPreparation(): array
    {
        $items = $this->fetchDataFromSheet('ir-01-002')->filter(function ($item) {
            $baseCondition = $item['stage'] === 'Pre-procurement'
                && $item['specific_status'] === 'Subproject Confirmed'
                && !empty($item['subproject_confirmed'])
                && empty($item['sp_rpab_approved']);

            if ($this->filterKey === 'All') return $baseCondition;
            if (in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) return $baseCondition && $item['cluster'] === $this->filterKey;
            return $baseCondition && $item['region'] === $this->filterKey;
        });

        return $this->prepareItems($items, 'subproject_confirmed', 'underBusinessPlanPreparation', 204);
    }

    private function forRPABApproval(): array
    {
        $priorityDates = ['ima_signed_notarized', 'sp_rpab_approved', 'jtr_conducted', 'rpco_technical_review_conducted', 'business_plan_packaged'];

        $items = $this->fetchDataFromSheet('ir-01-002')->filter(function ($item) use ($priorityDates) {
            $baseCondition = $item['stage'] === 'Pre-procurement'
                && in_array($item['specific_status'], ['RPCO Technical Review of Business Plan conducted', 'Business Plan Package for RPCO technical review submitted'])
                && collect($priorityDates)->first(fn($field) => !empty($item[$field]))
                && empty($item['nol1_issued']);

            if ($this->filterKey === 'All') return $baseCondition;
            if (in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) return $baseCondition && $item['cluster'] === $this->filterKey;
            return $baseCondition && $item['region'] === $this->filterKey;
        });

        $items = $items->map(function ($item) use ($priorityDates) {
            $dateField = collect($priorityDates)->first(fn($field) => !empty($item[$field]));
            $item['rpabApprovalDate'] = $item[$dateField] ?? null;
            return $item;
        });

        return $this->prepareItems($items, 'rpabApprovalDate', 'forRPABApproval', 114);
    }

    private function RPABApproved(): array
    {
        $items = $this->fetchDataFromSheet('ir-01-002')->filter(function ($item) {
            $baseCondition = $item['stage'] === 'Pre-procurement'
                && in_array($item['specific_status'], ['Joint Technical Review (JTR) conducted', 'SP approved by RPAB', 'Signing of the IMA', 'Subproject Issued with No Objection Letter 1'])
                && !empty($item['nol1_issued']);

            if ($this->filterKey === 'All') return $baseCondition;
            if (in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) return $baseCondition && $item['cluster'] === $this->filterKey;
            return $baseCondition && $item['region'] === $this->filterKey;
        });

        return $this->prepareItems($items, 'nol1_issued', 'rpabApproved', 120);
    }

    public function updatedFilterKey(): void
    {
        $this->loader = true;
        $this->initChartData();
    }

    #[On('barClicked')]
    public function barClicked($key, $type): void
    {
        $innerKey = $type ? 'beyondTimelineItems' : 'subprojectItems';
        $this->tableData = $this->consolidatedTableData[$key][$innerKey] ?? [];
        $this->modalSubtitle = $this->dataSets[$key]['title'] ?? '';
        if ($innerKey === 'beyondTimelineItems') {
            $this->modalSubtitle .= ' (No. of SPs Beyond Timeline)';
        }
        $this->subprojectModal = true;
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
            <span>I-REAP Subprojects Currently in the Pipeline (Number of Subprojects by Status)</span>
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
            <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100"
                id="subproject-chart"></canvas>
        </div>
    </div>
    @if ($subprojectModal)
    <div class="modal fade show" id="helloModal" tabindex="-1" aria-modal="true" role="dialog" style="display: block;" aria-labelledby="helloModalLabel" aria-hidden="false">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">

                <div class="modal-header position-relative flex-column align-items-start pb-0" style="border-bottom: none;">
                    <h5 class="modal-title mb-0 fw-bold text-primary" id="helloModalLabel">
                        I-REAP Subprojects in the Pipeline (Number of Subprojects by Status)
                    </h5>
                    <small class="text-warning fw-semibold" style="font-size: 1rem;">
                        {{ $modalSubtitle }}
                    </small>
                    <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" wire:click='$set("subprojectModal", false)' aria-label="Close"></button>
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
    // Keep chart instance globally
    window.chartInstance = null;

    window.ChartOne = function(chartData) {
        const canvas = document.getElementById('subproject-chart');

        if (!canvas) return; // prevent errors if canvas not in DOM

        const ctx = canvas.getContext('2d');

        // Destroy previous chart if exists
        if (window.chartInstance) {
            window.chartInstance.destroy();
            window.chartInstance = null;
        }

        const groupKeys = Object.keys(chartData);
        console.log(chartData);

        const averageDiff = (() => {
            let total = 0;
            let count = 0;
            groupKeys.forEach(k => {
                if (chartData[k].average_difference_days) {
                    total += chartData[k].average_difference_days;
                    count++;
                }
            });
            return count ? Math.round(total / count) : 0;
        })();

        window.chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: groupKeys.map(key => chartData[key].title),
                datasets: [{
                        label: 'No. of Subprojects',
                        backgroundColor: '#0047e0',
                        data: groupKeys.map(key => chartData[key].subject_count),
                        borderRadius: 8,
                    },
                    {
                        label: 'No. of Subprojects Beyond Timeline',
                        backgroundColor: '#fa2314',
                        data: groupKeys.map(key => chartData[key].beyond_timeline_count),
                        borderRadius: 8,

                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: (() => {
                            const allValues = [];
                            groupKeys.forEach(key => {
                                allValues.push(chartData[key].subject_count || 0);
                                allValues.push(chartData[key].beyond_timeline_count || 0);
                            });
                            const maxValue = Math.max(...allValues);
                            return maxValue + Math.ceil(maxValue * 0.2);
                        })()
                    }
                },
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
                        textAlign: 'center',
                        formatter: function(value, context) {
                            if (context.datasetIndex === 1 && value > 0) {
                                const datasetIndex = context.dataIndex;
                                const key = groupKeys[datasetIndex];
                                const data = chartData[key];

                                const label = (data?.bar_Label || '').replace(/\\n/g, '\n') || `${value} items`;

                                return [
                                    `${value}`,
                                    label
                                ];
                            }
                            return value > 0 ? `${value}` : '';
                        }


                    }
                },
                onClick: (evt, elements) => {
                    if (!elements.length) return;
                    const element = elements[0];
                    const index = element.index;
                    const key = groupKeys[index];
                    const datasetIndex = element.datasetIndex;
                    Livewire.dispatch('barClicked', {
                        key,
                        type: datasetIndex
                    });
                }
            },
            plugins: [ChartDataLabels]
        });
    };

    // Trigger chart only when Livewire dispatches
    Livewire.on('generateChart', data => {
        setTimeout(() => {
            if (data[0] && data[0].chartData) {
                window.ChartOne(data[0].chartData);
                $wire.set('loader', false);
            }
        }, 50); // 50ms delay ensures canvas exists
        // $wire.set('loader', false);
    });
</script>
@endscript