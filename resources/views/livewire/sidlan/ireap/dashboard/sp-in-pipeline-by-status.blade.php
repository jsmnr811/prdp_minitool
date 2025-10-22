<?php

use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Carbon\Carbon;

/**
 * Component: Pipeline Chart (SP by Status)
 *
 * Responsibilities:
 *  - Load and normalize sheet data (IR-01-002).
 *  - Compute three datasets:
 *      1. underBusinessPlanPreparation
 *      2. forRPABApproval
 *      3. rpabApproved
 *  - Prepare aggregated chart payload and consolidated table rows.
 *  - Expose a Livewire event that front-end JS listens to for rendering.
 *
 * Notes:
 *  - Uses Carbon for safe date parsing and diff calculations.
 *  - Keeps UI and chart behavior identical to the original.
 */
new class extends Component {
    /** @var array Chart payload sent to front-end */
    public array $chartData = [];

    /** @var array Consolidated table data used for modal detail view */
    public array $consolidatedTableData = [];

    /** @var array Table data currently shown in modal (JS-populated) */
    public array $tableData = [];

    /** @var string Filter key (All | cluster | region) */
    public string $filterKey = 'All';

    /** @var bool Loading indicator */
    public bool $loader = false;

    /** @var string Modal title and subtitle placeholders */
    public string $modalTitle = '';
    public string $modalSubtitle = '';

    /** @var array Prepared datasets for the chart */
    public array $dataSets = [];

    /** @var array Context used when building modal table */
    public array $tableContext = [];

    /** @var SidlanGoogleSheetService|null Google Sheet service instance */
    private ?SidlanGoogleSheetService $sheetService = null;

    /**
     * Mount lifecycle hook.
     * Initialize sheet service and chart data.
     */
    public function mount(): void
    {
        $this->sheetService = new SidlanGoogleSheetService();
        $this->loader = true;
        $this->initChartData();
    }

    /**
     * Load and dispatch chart data to front-end.
     */
    private function initChartData(): void
    {
        $this->chartData = $this->initData();
        $this->dispatch('generatePipelineChartSPByStatusModal', [
            'chartData' => $this->chartData,
            'consolidatedTableData' => $this->consolidatedTableData
        ]);
        $this->loader = false;
    }

    /**
     * Build the three data sections and the final chart payload.
     *
     * @return array
     */
    private function initData(): array
    {
        $sections = [
            'underBusinessPlanPreparation' => $this->underBusinessPlanPreparation(),
            'forRPABApproval' => $this->forRPABApproval(),
            'rpabApproved' => $this->RPABApproved(),
        ];

        $titleMap = [
            'underBusinessPlanPreparation' => 'Under Business Plan Preparation',
            'forRPABApproval' => 'Under Review / For RPAB Approval',
            'rpabApproved' => 'RPAB Approved (For NOL 1)',
        ];

        $timelineMap = [
            'underBusinessPlanPreparation' => 204,
            'forRPABApproval' => 114,
            'rpabApproved' => 120,
        ];

        $this->dataSets = [];
        $this->consolidatedTableData = [];

        foreach ($sections as $key => $data) {
            $average = $data['average_difference_days'] ?? 0;
            $count = $data['count'] ?? 0;
            $beyondCount = $data['beyondTimelineCount'] ?? 0;

            $this->dataSets[$key] = [
                'title' => $titleMap[$key],
                'subject_count' => $count,
                'beyond_timeline_count' => $beyondCount,
                'key' => $key,
                'average_difference_days' => $average,
                'bar_Label' => "{$average} days vs\n{$timelineMap[$key]} days timeline",
            ];

            $this->consolidatedTableData[$key] = [
                'subprojectItems' => $data['items'] ?? [],
                'beyondTimelineItems' => $data['beyondTimeline'] ?? [],
            ];
        }

        return $this->dataSets;
    }

    /**
     * Fetch sheet data and return a cleaned collection.
     *
     * @param string $sheetName
     * @return \Illuminate\Support\Collection
     */
    private function fetchDataFromSheet(string $sheetName): \Illuminate\Support\Collection
    {
        // Ensure service is available
        if (!$this->sheetService) {
            $this->sheetService = new SidlanGoogleSheetService();
        }

        $rows = collect($this->sheetService->getSheetData($sheetName));

        // Normalize keys (trim only keys and string values)
        return $rows->map(function ($row) {
            $clean = [];
            foreach ($row as $key => $value) {
                $cleanKey = trim($key);
                $cleanValue = is_string($value) ? trim($value) : $value;
                $clean[$cleanKey] = $cleanValue;
            }
            return $clean;
        });
    }

    /**
     * Calculate displayable cost string from known fields.
     *
     * @param array $item
     * @return string
     */
    private function calculateCost(array $item): string
    {
        $fields = ['cost_nol_1', 'rpab_approved_cost', 'estimated_project_cost', 'cost_during_validation', 'indicative_project_cost'];

        foreach ($fields as $field) {
            if (!empty($item[$field]) && is_numeric($item[$field]) && (float) $item[$field] > 0) {
                return '₱' . number_format((float) $item[$field], 2);
            }
        }

        return '-';
    }

    /**
     * Generic items preparation helper:
     *  - Attach cost, parsed date, date_difference, formatted_date, dataset_key
     *  - Identify beyond-timeline items
     *  - Compute average days (only from beyond-timeline items)
     *
     * @param \Illuminate\Support\Collection $items
     * @param string $dateField Field name containing the date to compare
     * @param string $datasetKey Identifier for dataset
     * @param int $timelineDays Threshold days for "beyond timeline"
     * @return array
     */
    private function prepareItems(\Illuminate\Support\Collection $items, string $dateField, string $datasetKey, int $timelineDays): array
    {
        $now = Carbon::now();

        // Map items: attach computed metadata, handle invalid/missing dates gracefully
        $prepared = $items->map(function ($item) use ($dateField, $datasetKey, $now) {
            $dateValue = $item[$dateField] ?? null;
            $parsed = null;
            $dateDiff = null;
            $formatted = null;

            if (!empty($dateValue)) {
                try {
                    $parsed = Carbon::parse($dateValue);
                    $dateDiff = $parsed->diffInRealDays($now);
                    $formatted = $parsed->format('M j, Y');
                } catch (\Exception $e) {
                    $parsed = null;
                }
            }

            return array_merge($item, [
                'cost' => $this->calculateCost($item),
                'date_difference' => $dateDiff,
                'formatted_date' => $formatted,
                'dataset_key' => $datasetKey,
            ]);
        });

        // Identify beyond-timeline (only where date_difference is numeric)
        $beyondTimeline = $prepared->filter(
            fn($it) => is_numeric($it['date_difference']) && $it['date_difference'] > $timelineDays
        );

        // ✅ Average based only on beyond-timeline items
        $avg = $beyondTimeline
            ->pluck('date_difference')
            ->filter(fn($v) => is_numeric($v))
            ->avg();

        $avgRounded = is_null($avg) ? 0 : round($avg);

        return [
            'items' => $prepared->values()->toArray(),
            'count' => $prepared->count(),
            'beyondTimeline' => $beyondTimeline->values()->toArray(),
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => $avgRounded, // only beyond-timeline average
        ];
    }


    /**
     * Filter helper to enforce the currently selected filterKey (cluster or region)
     * Returns true if the item should be included under the current filter.
     *
     * @param array $item
     * @return bool
     */
    private function passesFilter(array $item): bool
    {
        if ($this->filterKey === 'All') {
            return true;
        }

        $filter = trim($this->filterKey);
        $cluster = isset($item['cluster']) ? trim($item['cluster']) : null;
        $region = isset($item['region']) ? trim($item['region']) : null;

        // If the filterKey matches cluster groups, compare cluster
        if (in_array($filter, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) {
            return $cluster === $filter;
        }

        // Otherwise match region if provided
        if (!empty($region)) {
            return $region === $filter;
        }

        return false;
    }

    /**
     * Prepare items under Business Plan Preparation.
     *
     * Conditions checked (source data keys expected exactly as in sheet):
     *  - stage === 'Pre-procurement'
     *  - specific_status === 'Subproject Confirmed'
     *  - subproject_confirmed exists (date)
     *  - rpco_technical_review_conducted empty or invalid
     */
    private function underBusinessPlanPreparation(): array
    {
        $rows = $this->fetchDataFromSheet('ir-01-002');

        $items = $rows->filter(function ($item) {
            $baseCondition = ($item['stage'] ?? '') === 'Pre-procurement'
                && ($item['specific_status'] ?? '') === 'Subproject Confirmed'
                && !empty($item['subproject_confirmed'])
                && (empty($item['rpco_technical_review_conducted']) || !strtotime($item['rpco_technical_review_conducted']));

            return $baseCondition && $this->passesFilter($item);
        });

        return $this->prepareItems($items, 'subproject_confirmed', 'underBusinessPlanPreparation', 204);
    }

    /**
     * Prepare items awaiting RPAB approval.
     *
     * Priority for determining "rpabApprovalDate":
     *   sp_rpab_approved, jtr_conducted, rpco_technical_review_conducted, business_plan_packaged
     *
     * Conditions:
     *  - stage === 'Pre-procurement'
     *  - specific_status ∈ [RPCO Technical Review..., Business Plan Package...]
     *  - business_plan_packaged not empty
     *  - sp_rpab_approved empty or invalid (still awaiting)
     */
    private function forRPABApproval(): array
    {
        $priorityDates = ['sp_rpab_approved', 'jtr_conducted', 'rpco_technical_review_conducted', 'business_plan_packaged'];

        $rows = $this->fetchDataFromSheet('ir-01-002');

        $items = $rows->filter(function ($item) {
            $cond = ($item['stage'] ?? '') === 'Pre-procurement'
                && in_array($item['specific_status'] ?? '', [
                    'RPCO Technical Review of Business Plan conducted',
                    'Business Plan Package for RPCO technical review submitted'
                ], true)
                && !empty($item['business_plan_packaged'])
                && (empty($item['sp_rpab_approved']) || !strtotime($item['sp_rpab_approved']));

            return $cond && $this->passesFilter($item);
        })
            ->map(function ($item) use ($priorityDates) {
                foreach ($priorityDates as $f) {
                    if (!empty($item[$f])) {
                        $item['rpabApprovalDate'] = $item[$f];
                        break;
                    }
                }
                $item['rpabApprovalDate'] = $item['rpabApprovalDate'] ?? null;
                return $item;
            });

        return $this->prepareItems($items, 'rpabApprovalDate', 'forRPABApproval', 114);
    }

    /**
     * Prepare RPABApproved items (for NOL1 issuance).
     *
     * Priority for determining rpabApprovedDate:
     *  nol1_issued, ima_signed_notarized, sp_rpab_approved, jtr_conducted
     *
     * Conditions:
     *  - stage === 'Pre-procurement'
     *  - specific_status ∈ [JTR conducted, SP approved by RPAB, Signing of the IMA, Subproject Issued with NOL1]
     *  - jtr_conducted exists
     */
    private function RPABApproved(): array
    {
        $priorityDates = ['nol1_issued', 'ima_signed_notarized', 'sp_rpab_approved', 'jtr_conducted'];

        $rows = $this->fetchDataFromSheet('ir-01-002');

        $items = $rows->filter(function ($item) {
            $cond = ($item['stage'] ?? '') === 'Pre-procurement'
                && in_array($item['specific_status'] ?? '', [
                    'Joint Technical Review (JTR) conducted',
                    'SP approved by RPAB',
                    'Signing of the IMA',
                    'Subproject Issued with No Objection Letter 1'
                ], true)
                && !empty($item['jtr_conducted']);

            return $cond && $this->passesFilter($item);
        })
            ->map(function ($item) use ($priorityDates) {
                foreach ($priorityDates as $f) {
                    if (!empty($item[$f])) {
                        $item['rpabApprovedDate'] = $item[$f];
                        break;
                    }
                }
                $item['rpabApprovedDate'] = $item['rpabApprovedDate'] ?? null;
                return $item;
            });

        return $this->prepareItems($items, 'rpabApprovedDate', 'rpabApproved', 120);
    }

    /**
     * React to filter changes. Recompute datasets and optionally update tableData if modal context exists.
     */
    public function updatedFilterKey(): void
    {
        $this->loader = true;

        $this->chartData = $this->initData();
        $this->dispatch('generatePipelineChartSPByStatusModal', [
            'chartData' => $this->chartData,
            'consolidatedTableData' => $this->consolidatedTableData
        ]);

        // If currently viewing a modal table (tableContext provided), update tableData
        if (!empty($this->tableContext)) {
            $datasetKey = $this->tableContext['key'] ?? null;
            $isBeyond = $this->tableContext['type'] ?? false;

            if ($datasetKey) {
                $innerKey = $isBeyond ? 'beyondTimelineItems' : 'subprojectItems';
                $this->tableData = $this->consolidatedTableData[$datasetKey][$innerKey] ?? [];
            }
        }

        $this->loader = false;
    }

    /**
     * Placeholder view used during loading.
     *
     * @return View
     */
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
                        <option value="Bangsamoro Autonomous Region of Muslim Mindanao (BARMM)" data-group="region">
                            BARMM</option>
                    </optgroup>
                </select>
            </div>
        </div>

        <div wire:ignore class="tile-content position-relative overflow-hidden chart-container" style="height: 400px;">
            <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100" id="subproject-chart"></canvas>
        </div>
    </div>
</div>

@script
<script>
    // Single global instance reference for the chart
    window.pipelineChartInstance = null;

    /**
     * Render the pipeline status chart.
     * chartData: object where keys are dataset keys and each value contains:
     *  - title
     *  - subject_count
     *  - beyond_timeline_count
     *  - average_difference_days
     *  - bar_Label
     *
     * consolidatedTableData: object used later by the modal
     */
    window.ChartOne = function(chartData) {
        const canvas = document.getElementById('subproject-chart');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');

        // Destroy existing instance to prevent duplicates
        if (window.pipelineChartInstance) {
            window.pipelineChartInstance.destroy();
            window.pipelineChartInstance = null;
        }

        const groupKeys = Object.keys(chartData || {});
        if (!groupKeys.length) return;

        // Build datasets
        const subjectCounts = groupKeys.map(k => chartData[k].subject_count || 0);
        const beyondCounts = groupKeys.map(k => chartData[k].beyond_timeline_count || 0);

        // Compute suggested max for y-axis (+20% buffer)
        const maxVal = Math.max(...subjectCounts, ...beyondCounts, 0);
        const suggestedMax = maxVal + Math.ceil(maxVal * 0.2);

        window.pipelineChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: groupKeys.map(k => chartData[k].title),
                datasets: [{
                        label: 'No. of Subprojects',
                        backgroundColor: '#0047e0',
                        data: subjectCounts,
                        borderRadius: 8,
                    },
                    {
                        label: 'No. of Subprojects Beyond Timeline',
                        backgroundColor: '#fa2314',
                        data: beyondCounts,
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
                        suggestedMax
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
                            // For the "beyond timeline" dataset (index 1), show two lines:
                            // - the count
                            // - descriptive bar_Label (if provided)
                            if (context.datasetIndex === 1 && value > 0) {
                                const idx = context.dataIndex;
                                const key = groupKeys[idx];
                                const data = chartData[key] || {};
                                const label = (data.bar_Label || '').replace(/\\n/g, '\n') || `${value} items`;
                                return [String(value), label];
                            }
                            return value > 0 ? String(value) : '';
                        }
                    }
                },
                onClick: (evt, elements) => {
                    if (!elements || !elements.length) return;

                    // Only handle clicks when chart has data
                    const el = elements[0];
                    const index = el.index;
                    const datasetIndex = el.datasetIndex;
                    const key = groupKeys[index];

                    const isBeyond = (datasetIndex === 1);
                    const innerKey = isBeyond ? 'beyondTimelineItems' : 'subprojectItems';

                    // Avoid errors if consolidated data isn't present
                    const consolidated = window.currentConsolidatedTableData || {};
                    const tableData = (consolidated[key] && consolidated[key][innerKey]) ? consolidated[key][innerKey] : [];

                    // Set global variables used by modal population
                    window.pipelineTableData = tableData;
                    window.modalSubtitle = (chartData[key] && chartData[key].title) ? chartData[key].title + (isBeyond ? ' (No. of SPs Beyond Timeline)' : '') : '';
                    window.modalTitle = 'I-REAP Subprojects in the Pipeline (Number of Subprojects by Status)';

                    // Show Bootstrap modal and populate table after modal opens
                    $('#pipeline-by-status-modal').modal('show');

                    setTimeout(() => {
                        const tableContainer = $('#pipeline-by-status-modal .modal-body .table-responsive');
                        const subtitle = $('#modal-subtitle');

                        tableContainer.empty();

                        let tableHtml = `
                            <table class="table table-hover small mb-0" style="width:100%; table-layout: fixed;">
                                <thead>
                                    <tr>
                                        <th style="width:8%;">Cluster</th>
                                        <th style="width:10%;">Region</th>
                                        <th style="width:12%;">Province</th>
                                        <th style="width:12%;">City/Municipality</th>
                                        <th style="width:12%;">Proponent</th>
                                        <th style="width:15%;">SP Name</th>
                                        <th style="width:8%;">Type</th>
                                        <th style="width:8%;">Cost</th>
                                        <th style="width:8%;">Stage</th>
                                        <th style="width:12%;">Status</th>
                                        <th style="width:15%;">No. of days</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        if (Array.isArray(window.pipelineTableData) && window.pipelineTableData.length) {
                            window.pipelineTableData.forEach((d) => {
                                const days = d.date_difference ? Math.round(d.date_difference) : '';
                                const labelContext = d.dataset_key === 'underBusinessPlanPreparation' ? 'Date of confirmation' :
                                    d.dataset_key === 'forRPABApproval' ? 'FS / DED Preparation' :
                                    d.dataset_key === 'rpabApproved' ? 'RPAB Approval' : 'Date';
                                const formattedDate = d.formatted_date || '';
                                tableHtml += `
                                    <tr>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.cluster || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.region || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.province || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.city_municipality || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.proponent || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.project_name || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.project_type || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.cost || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.stage || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">${d.specific_status || ''}</td>
                                        <td style="word-wrap: break-word; white-space: normal;">
                                            ${days ? `${days} days from ${labelContext} (${formattedDate})` : ''}
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        tableHtml += `</tbody></table>`;
                        tableContainer.html(tableHtml);

                        if (window.modalTitle) $('#modal-title').text(window.modalTitle);
                        if (window.modalSubtitle) subtitle.text(window.modalSubtitle);
                    }, 100);
                }
            },
            plugins: [ChartDataLabels]
        });
    };

    // Listen for Livewire dispatch and render chart
    Livewire.on('generatePipelineChartSPByStatusModal', payload => {
        // payload[0] contains chartData + consolidatedTableData
        if (!payload || !payload[0]) return;

        const data = payload[0];
        window.currentChartData = data.chartData || {};
        window.currentConsolidatedTableData = data.consolidatedTableData || {};

        window.ChartOne(window.currentChartData);

        // Ensure Livewire loader state is turned off if present
        if (typeof $wire !== 'undefined') {
            try {
                $wire.set('loader', false);
            } catch (e) {
                /* ignore */
            }
        }
    });
</script>
@endscript