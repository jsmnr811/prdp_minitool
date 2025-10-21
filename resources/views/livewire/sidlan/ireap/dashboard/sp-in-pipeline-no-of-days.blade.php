<?php

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

new class extends Component {
    public $chartData = [];
    public $underBusinessPlanPreparationItems = [];
    public $consolidatedTableData = [];
    public $filterKey = 'All';
    public $loader = false;

    private ?SidlanGoogleSheetService $sheetService = null;

    public function mount(): void
    {
        $this->loader = true;
        $this->loadSheetData();
        $this->initChartData();
    }

    private function initChartData(): void
    {
        $this->chartData = $this->initData();
        $this->dispatch('generatePipelineChartNoOfDaysModal', ['chartData' => $this->chartData, 'consolidatedTableData' => $this->consolidatedTableData]);
        $this->loader = false;
    }

    private function initData(): array
    {
        $sets = [
            'underBusinessPlanPreparation' => [
                'title' => 'Under Business Plan Preparation',
                'timeline' => 204,
                'data' => $this->underBusinessPlanPreparation()
            ],
            'forRPABApproval' => [
                'title' => 'Under Review / For RPAB Approval',
                'timeline' => 114,
                'data' => $this->forRPABApproval()
            ],
            'rpabApproved' => [
                'title' => 'RPAB Approved (For NOL 1)',
                'timeline' => 120,
                'data' => $this->RPABApproved()
            ],
        ];

        $dataSets = [];
        foreach ($sets as $key => $info) {
            $data = $info['data'];
            $dataSets[$key] = [
                'title' => $info['title'],
                'prescribed_timeline' => $info['timeline'],
                'beyond_timeline_count' => $data['beyondTimelineCount'],
                'average_difference_days' => $data['average_difference_days'],
                'key' => $key,
                'bar_Label' => "({$data['beyondTimelineCount']} SPs)"
            ];

            $this->consolidatedTableData[$key] = [
                'subprojectItems' => $data['items'],
                'beyondTimelineItems' => $data['beyondTimeline']
            ];
        }

        $this->dataSets = $dataSets;
        return $dataSets;
    }

    private function loadSheetData(): void
    {
        $this->sheetService = new SidlanGoogleSheetService();
        $this->sheetData = collect($this->sheetService->getSheetData('ir-01-002'));
    }

    private function getCost(array $item): string
    {
        $fields = [
            'cost_nol_1',
            'rpab_approved_cost',
            'estimated_project_cost',
            'cost_during_validation',
            'indicative_project_cost'
        ];

        foreach ($fields as $field) {
            if (!empty($item[$field]) && is_numeric($item[$field]) && (float)$item[$field] > 0) {
                return '₱' . number_format((float)$item[$field], 2);
            }
        }
        return '-';
    }

    private function filterByKey(callable $condition): \Illuminate\Support\Collection
    {
        return $this->sheetData->filter(function ($item) use ($condition) {
            $baseCondition = $condition($item);

            return match (true) {
                $this->filterKey === 'All' => $baseCondition,
                in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao']) => $baseCondition && $item['cluster'] === $this->filterKey,
                default => $baseCondition && $item['region'] === $this->filterKey,
            };
        });
    }

    private function processDateDiff(\Illuminate\Support\Collection $items, string $dateField, int $timeline, string $datasetKey): array
    {
        $now = now();

        $processed = $items->map(function ($item) use ($dateField, $datasetKey, $now) {
            $date = Carbon::parse($item[$dateField]);
            return collect($item)->merge([
                'date_difference' => $date->diffInDays($now),
                'subproject_confirmed' => $date->format('M j, Y'),
                'dataset_key' => $datasetKey
            ]);
        });

        $beyondTimeline = $processed->filter(fn($item) => $item['date_difference'] > $timeline);
        $avgDiff = round($beyondTimeline->avg('date_difference') ?? 0);

        return [
            'items' => $processed,
            'count' => $processed->count(),
            'beyondTimeline' => $beyondTimeline,
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => $avgDiff
        ];
    }

    /**
     * Retrieve and prepare subprojects currently under business plan preparation.
     *
     * Conditions:
     * - Stage must be "Pre-procurement".
     * - Specific status must be "Subproject Confirmed".
     * - The field `subproject_confirmed` must not be empty.
     * - The field `rpco_technical_review_conducted` must be either empty or invalid.
     *
     * @return array Processed and formatted items currently under business plan preparation.
     */
    private function underBusinessPlanPreparation(): array
    {
        $items = $this->filterByKey(function ($item) {
            return $item['stage'] === 'Pre-procurement'
                && $item['specific_status'] === 'Subproject Confirmed'
                && !empty($item['subproject_confirmed'])
                && (empty($item['rpco_technical_review_conducted'])
                    || !strtotime($item['rpco_technical_review_conducted']));
        })->map(function ($item) {
            return array_merge(
                collect($item)->only([
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
                ])->toArray(),
                ['cost' => $this->getCost($item)]
            );
        });

        return $this->processDateDiff($items, 'subproject_confirmed', 204, 'underBusinessPlanPreparation');
    }

    /**
     * Retrieve and prepare items awaiting RPAB approval.
     *
     * Conditions:
     * - Stage must be "Pre-procurement".
     * - Specific status must be one of:
     *     - "RPCO Technical Review of Business Plan conducted"
     *     - "Business Plan Package for RPCO technical review submitted"
     * - The field `business_plan_packaged` must not be empty.
     * - The field `sp_rpab_approved` must be either empty or invalid.
     *
     * The resulting items are assigned an `rpabApprovalDate`, chosen from the first available date in priority order:
     *     1. sp_rpab_approved
     *     2. jtr_conducted
     *     3. rpco_technical_review_conducted
     *     4. business_plan_packaged
     *
     * @return array Processed and formatted items awaiting RPAB approval.
     */
    private function forRPABApproval(): array
    {
        $items = $this->filterByKey(function ($item) {
            $statusMatch = in_array($item['specific_status'], [
                'RPCO Technical Review of Business Plan conducted',
                'Business Plan Package for RPCO technical review submitted'
            ], true);

            $priorityDates = [
                'sp_rpab_approved',
                'jtr_conducted',
                'rpco_technical_review_conducted',
                'business_plan_packaged',
            ];

            $approvalDate = collect($priorityDates)->first(fn($f) => !empty($item[$f]));

            return $item['stage'] === 'Pre-procurement'
                && $statusMatch
                && $approvalDate
                && !empty($item['business_plan_packaged'])
                && (empty($item['sp_rpab_approved']) || !strtotime($item['sp_rpab_approved']));
        })->map(function ($item) {
            $priorityDates = [
                'sp_rpab_approved',
                'jtr_conducted',
                'rpco_technical_review_conducted',
                'business_plan_packaged',
            ];

            $rpabDate = collect($priorityDates)
                ->map(fn($f) => $item[$f] ?? null)
                ->first(fn($value) => !empty($value));

            return array_merge(
                collect($item)->only([
                    'cluster',
                    'region',
                    'province',
                    'city_municipality',
                    'proponent',
                    'project_name',
                    'project_type',
                    'stage',
                    'specific_status'
                ])->toArray(),
                [
                    'cost' => $this->getCost($item),
                    'rpabApprovalDate' => $rpabDate,
                    'subproject_confirmed' => $rpabDate,
                ]
            );
        });

        return $this->processDateDiff($items, 'rpabApprovalDate', 114, 'forRPABApproval');
    }

    /**
     * Retrieve and prepare RPAB-approved items.
     *
     * Conditions:
     * - Stage must be "Pre-procurement".
     * - Specific status must be one of:
     *     - "Joint Technical Review (JTR) conducted"
     *     - "SP approved by RPAB"
     *     - "Signing of the IMA"
     *     - "Subproject Issued with No Objection Letter 1"
     * - The field `jtr_conducted` must not be empty.
     *
     * The resulting items are assigned an `rpabApprovedDate`, chosen from the first available date in priority order:
     *     1. nol1_issued
     *     2. ima_signed_notarized
     *     3. sp_rpab_approved
     *     4. jtr_conducted
     *
     * @return array Processed and formatted RPAB-approved items.
     */
    private function RPABApproved(): array
    {
        $priorityDates = [
            'nol1_issued',
            'ima_signed_notarized',
            'sp_rpab_approved',
            'jtr_conducted',
        ];

        $items = $this->filterByKey(function ($item) {
            return $item['stage'] === 'Pre-procurement'
                && in_array($item['specific_status'], [
                    'Joint Technical Review (JTR) conducted',
                    'SP approved by RPAB',
                    'Signing of the IMA',
                    'Subproject Issued with No Objection Letter 1'
                ], true)
                && !empty($item['jtr_conducted']);
        })->map(function ($item) use ($priorityDates) {
            $rpabDate = collect($priorityDates)
                ->map(fn($f) => $item[$f] ?? null)
                ->first(fn($value) => !empty($value));

            return array_merge(
                collect($item)->only([
                    'cluster',
                    'region',
                    'province',
                    'city_municipality',
                    'proponent',
                    'project_name',
                    'project_type',
                    'stage',
                    'specific_status'
                ])->toArray(),
                [
                    'cost' => $this->getCost($item),
                    'rpabApprovedDate' => $rpabDate,
                ]
            );
        });

        return $this->processDateDiff($items, 'rpabApprovedDate', 120, 'rpabApproved');
    }


    public function updatedFilterKey(): void
    {
        $this->loader = true;
        $this->loadSheetData();
        $this->initChartData();
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
                        <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)" data-group="region">
                            BARMM</option>
                    </optgroup>
                </select>
            </div>
        </div>


        <div wire:ignore class="tile-content position-relative overflow-hidden chart-container" style="height: 400px;">
            <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100" id="sp-pipeline-no-of-days-in-the-current-status"></canvas>
        </div>
    </div>

</div>

@script
<script>
    window.chartInstanceDays = null;

    window.ChartTwo = function(chartData) {
        const canvas = document.getElementById('sp-pipeline-no-of-days-in-the-current-status');

        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        if (window.chartInstanceDays) {
            window.chartInstanceDays.destroy();
            window.chartInstanceDays = null;
        }

        const groupKeys = Object.keys(chartData);

        window.chartInstanceDays = new Chart(ctx, {
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
                                allValues.push(chartData[key].prescribed_timeline || 0);
                                allValues.push(chartData[key].average_difference_days || 0);
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

                                const label = (data?.bar_Label || '').replace(/\\n/g, '\n') ||
                                    `${value} items`;

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
                    // Prevent clicking when loading
                    if (window.isChartLoadingDays) return;

                    if (!elements.length) return;
                    const element = elements[0];
                    const index = element.index;
                    const key = groupKeys[index];
                    const datasetIndex = element.datasetIndex;

                    // Only allow clicks on the red bar (Average No. of Days, datasetIndex === 1)
                    if (datasetIndex !== 1) return;

                    const type = datasetIndex === 1;
                    const innerKey = type ? 'beyondTimelineItems' : 'subprojectItems';

                    console.log('SP Pipeline Days: Bar clicked:', {
                        key,
                        type,
                        innerKey,
                        chartDataAvailable: !!window.currentDaysChartData,
                        consolidatedDataAvailable: !!window.currentDaysConsolidatedTableData,
                        chartDataForKey: window.currentDaysChartData ? window.currentDaysChartData[key] : null,
                        consolidatedDataForKey: window.currentDaysConsolidatedTableData ? window.currentDaysConsolidatedTableData[key] : null
                    });

                    window.pipelineDaysTableData = window.currentDaysConsolidatedTableData[key][innerKey];
                    window.modalDaysSubtitle = window.currentDaysChartData[key].title + (type ? ' (No. of SPs Beyond Timeline)' : '');
                    window.modalDaysTitle = 'I-REAP Subprojects in the Pipeline (No. of Days in the Current Status)';

                    console.log('SP Pipeline Days: Table data extracted:', {
                        tableDataLength: window.pipelineDaysTableData ? Object.keys(window.pipelineDaysTableData).length : 0,
                        modalTitle: window.modalDaysTitle,
                        modalSubtitle: window.modalDaysSubtitle
                    });

                    $('#pipeline-days-modal').modal('show');

                    // Populate table after modal is shown
                    setTimeout(() => {
                        const tableContainer = $('#pipeline-days-modal .modal-body .table-responsive');
                        const subtitle = $('#pipeline-days-modal #modal-subtitle');

                        // Clear existing content
                        tableContainer.empty();

                        // Build the complete table HTML
                        let tableHtml = `
                                <table class="table table-hover small mb-0" style="width: 100%; table-layout: fixed;">
                                    <thead>
                                        <tr>
                                            <th class="text-wrap" style="width: 8%;">Cluster</th>
                                            <th class="text-wrap" style="width: 10%;">Region</th>
                                            <th class="text-wrap" style="width: 12%;">Province</th>
                                            <th class="text-wrap" style="width: 12%;">City/Municipality</th>
                                            <th class="text-wrap" style="width: 12%;">Proponent</th>
                                            <th class="text-wrap" style="width: 15%;">SP Name</th>
                                            <th class="text-wrap" style="width: 8%;">Type</th>
                                            <th class="text-wrap" style="width: 8%;">Cost</th>
                                            <th class="text-wrap" style="width: 8%;">Stage</th>
                                            <th class="text-wrap" style="width: 12%;">Status</th>
                                            <th class="text-wrap" style="width: 15%;">No. of days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            `;

                        if (window.pipelineDaysTableData && Object.keys(window.pipelineDaysTableData).length > 0) {
                            Object.values(window.pipelineDaysTableData).forEach((data) => {
                                tableHtml += `
                                        <tr>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.cluster || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.region || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.province || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.city_municipality || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.proponent || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.project_name || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.project_type || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.cost || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.stage || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">${data.specific_status || ''}</td>
                                            <td style="word-wrap: break-word; white-space: normal;">
                                                ${Math.round(data.date_difference || 0)} days from
                                                ${data.dataset_key === 'underBusinessPlanPreparation' ? 'Date of confirmation' :
                                                  data.dataset_key === 'forRPABApproval' ? 'FS / DED Preparation' :
                                                  data.dataset_key === 'rpabApproved' ? 'from RPAB Approval' : 'Date of confirmation'}
                                                (${data.subproject_confirmed ? new Date(data.subproject_confirmed).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : ''})
                                            </td>
                                        </tr>
                                    `;
                            });
                        }

                        tableHtml += `
                                    </tbody>
                                </table>
                            `;

                        // Append the complete table
                        tableContainer.html(tableHtml);

                        if (window.modalDaysTitle) {
                            $('#pipeline-days-modal #modal-title').text(window.modalDaysTitle);
                        }

                        if (window.modalDaysSubtitle) {
                            subtitle.text(window.modalDaysSubtitle);
                        }
                    }, 100);
                }
            },
            plugins: [ChartDataLabels]
        });
    };


    // Trigger chart only when Livewire dispatches
    Livewire.on('generatePipelineChartNoOfDaysModal', data => {
        console.log('SP Pipeline Days: Received Livewire data:', data);
        window.isChartLoadingDays = true;
        setTimeout(() => {
            if (data[0] && data[0].chartData) {
                window.currentDaysChartData = data[0].chartData;
                window.currentDaysConsolidatedTableData = data[0].consolidatedTableData;

                console.log('SP Pipeline Days: Chart data keys:', Object.keys(data[0].chartData));
                console.log('SP Pipeline Days: Consolidated data keys:', Object.keys(data[0].consolidatedTableData));

                // Debug each consolidated data structure
                Object.keys(data[0].consolidatedTableData).forEach(key => {
                    console.log(`SP Pipeline Days: ${key} consolidated data structure:`, {
                        hasSubprojectItems: !!data[0].consolidatedTableData[key].subprojectItems,
                        subprojectItemsCount: data[0].consolidatedTableData[key].subprojectItems ?
                            Object.keys(data[0].consolidatedTableData[key].subprojectItems).length : 0,
                        hasBeyondTimelineItems: !!data[0].consolidatedTableData[key].beyondTimelineItems,
                        beyondTimelineItemsCount: data[0].consolidatedTableData[key].beyondTimelineItems ?
                            Object.keys(data[0].consolidatedTableData[key].beyondTimelineItems).length : 0
                    });
                });

                window.ChartTwo(data[0].chartData);
                window.isChartLoadingDays = false;
            } else {
                console.error('SP Pipeline Days: Invalid data received from Livewire:', data);
            }
        }, 50);
    });
</script>
@endscript