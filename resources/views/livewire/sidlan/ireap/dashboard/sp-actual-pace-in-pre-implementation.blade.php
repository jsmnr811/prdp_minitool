<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;

/**
 * SIDLAN Pre-Implementation Progress Tracking Component
 *
 * This Livewire Volt component generates chart data for three
 * key pre-implementation stages:
 *   1. Under Business Plan Preparation
 *   2. Under Review / For RPAB Approval
 *   3. NOL 1 Issuance
 *
 * Data is sourced from Google Sheets via SidlanGoogleSheetService.
 */
new class extends Component {
    /** @var array Holds chart dataset for the front-end visualization */
    public $chartData = [];

    /** @var array Holds processed table data for all stages */
    public $consolidatedTableData = [];

    /** @var string Selected filter key (cluster or region) */
    public $filterKey = 'All';

    /** @var bool Loader indicator for front-end display */
    public $loader = false;

    /** @var SidlanGoogleSheetService|null Google Sheet data service instance */
    private ?SidlanGoogleSheetService $sheetService = null;

    /** @var \Illuminate\Support\Collection Loaded sheet data */
    private $sheetData;

    /**
     * Mounts the component on initialization.
     * Loads sheet data and initializes chart data.
     */
    public function mount(): void
    {
        $this->loader = true;
        $this->loadSheetData();
        $this->initChartData();
    }

    /**
     * Initializes chart datasets and triggers the front-end event.
     */
    private function initChartData(): void
    {
        $this->chartData = $this->initData();
        $this->dispatch('generatePreImplChart', ['chartData' => $this->chartData]);
        $this->loader = false;
    }

    /**
     * Loads and caches data from the Google Sheet.
     */
    private function loadSheetData(): void
    {
        $this->sheetService = new SidlanGoogleSheetService();
        $this->sheetData = collect($this->sheetService->getSheetData('ir-01-002'));
    }

    /**
     * Builds and processes all datasets for the chart and tables.
     *
     * @return array Prepared chart data
     */
    private function initData(): array
    {
        $sets = [
            'underBusinessPlanPreparation' => [
                'title' => 'Under Business Plan Preparation',
                'timeline' => 204,
                'data' => $this->underBusinessPlanPreparation(),
                'avg_key' => 'average_days_to_package'
            ],
            'forRPABApproval' => [
                'title' => 'Under Review / For RPAB Approval',
                'timeline' => 114,
                'data' => $this->forRPABApproval(),
                'avg_key' => 'average_difference_days'
            ],
            'nol1Issuance' => [
                'title' => 'NOL 1 Issuance',
                'timeline' => 120,
                'data' => $this->nol1Issuance(),
                'avg_key' => 'average_days_jtr_to_nol1'
            ],
        ];

        $dataSets = [];

        foreach ($sets as $key => $info) {
            $data = $info['data'];
            $averageDays = $data[$info['avg_key']] ?? 0;

            $dataSets[$key] = [
                'title' => $info['title'],
                'prescribed_timeline' => $info['timeline'],
                'average_difference_days' => $averageDays,
                'key' => $key,
                'bar_label' => "{$averageDays}"
            ];

            // Store detailed items for table display
            $this->consolidatedTableData[$key] = [
                'subprojectItems' => $data['items']
            ];
        }

        return $dataSets;
    }

    /**
     * Returns the first valid cost found among known cost fields.
     *
     * @param array $item Project data row
     * @return string Formatted cost value or '-'
     */
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

    /**
     * Filters sheet data by region or cluster, applying a given condition.
     *
     * @param callable $condition Filter callback
     * @return \Illuminate\Support\Collection Filtered items
     */
    private function filterByKey(callable $condition): \Illuminate\Support\Collection
    {
        return $this->sheetData->filter(function ($item) use ($condition) {
            $baseCondition = $condition($item);

            return match (true) {
                $this->filterKey === 'All' => $baseCondition,
                in_array($this->filterKey, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao']) =>
                    $baseCondition && $item['cluster'] === $this->filterKey,
                default =>
                    $baseCondition && $item['region'] === $this->filterKey,
            };
        });
    }

    /**
     * Safely computes date difference in days between two dates.
     *
     * @param string|null $start Start date (Y-m-d)
     * @param string|null $end End date (Y-m-d)
     * @return float|null Difference in days or null if invalid
     */
    private function dateDiff(?string $start, ?string $end): ?float
    {
        if (empty($start) || empty($end)) return null;

        $startDate = strtotime(trim($start));
        $endDate = strtotime(trim($end));

        if (!$startDate || !$endDate) return null;

        $days = ($endDate - $startDate) / 86400;
        return $days >= 0 ? $days : null;
    }

    /**
     * Stage 1: Computes duration between Subproject Confirmation and Business Plan Packaging.
     *
     * @return array Computed metrics and item data
     */
    private function underBusinessPlanPreparation(): array
    {
        $items = $this->filterByKey(fn($i) =>
            !empty($i['subproject_confirmed']) && !empty($i['business_plan_packaged'])
        )->map(function ($item) {
            $daysDiff = $this->dateDiff($item['subproject_confirmed'], $item['business_plan_packaged']);

            return array_merge(collect($item)->only([
                'cluster', 'region', 'province', 'city_municipality', 'proponent',
                'project_name', 'subproject_confirmed', 'business_plan_packaged',
                'project_type', 'stage', 'specific_status'
            ])->toArray(), [
                'cost' => $this->getCost($item),
                'days_to_package' => $daysDiff
            ]);
        });

        $averageDays = round($items->pluck('days_to_package')->filter()->avg() ?? 0);

        return [
            'items' => $items->toArray(),
            'count' => $items->count(),
            'average_days_to_package' => $averageDays
        ];
    }

    /**
     * Stage 2: Computes duration between Business Plan Packaging and JTR Conducted.
     *
     * @return array Computed metrics and item data
     */
    private function forRPABApproval(): array
    {
        $items = $this->filterByKey(fn($i) =>
            $i['stage'] === 'Pre-procurement'
            && in_array($i['specific_status'], [
                'RPCO Technical Review of Business Plan conducted',
                'Business Plan Package for RPCO technical review submitted',
                'Joint Technical Review (JTR) conducted',
                'SP approved by RPAB'
            ], true)
            && !empty($i['business_plan_packaged'])
            && !empty($i['jtr_conducted'])
        )->map(function ($item) {
            $daysDiff = $this->dateDiff($item['business_plan_packaged'], $item['jtr_conducted']);

            return array_merge(collect($item)->only([
                'cluster', 'region', 'province', 'city_municipality', 'proponent',
                'project_name', 'project_type', 'stage', 'specific_status',
                'business_plan_packaged', 'jtr_conducted'
            ])->toArray(), [
                'cost' => $this->getCost($item),
                'days_to_jtr' => $daysDiff
            ]);
        });

        $averageDays = round($items->pluck('days_to_jtr')->filter()->avg() ?? 0);

        return [
            'items' => $items->toArray(),
            'count' => $items->count(),
            'average_difference_days' => $averageDays
        ];
    }

    /**
     * Stage 3: Computes duration between JTR Conducted and NOL1 Issued.
     *
     * @return array Computed metrics and item data
     */
    private function nol1Issuance(): array
    {
        $items = $this->filterByKey(fn($i) =>
            $i['stage'] === 'Pre-procurement'
            && in_array($i['specific_status'], [
                'Joint Technical Review (JTR) conducted',
                'SP approved by RPAB',
                'Signing of the IMA',
                'Subproject Issued with No Objection Letter 1'
            ], true)
            && !empty($i['jtr_conducted'])
            && !empty($i['nol1_issued'])
        )->map(function ($item) {
            $daysDiff = $this->dateDiff($item['jtr_conducted'], $item['nol1_issued']);

            return array_merge(collect($item)->only([
                'cluster', 'region', 'province', 'city_municipality', 'proponent',
                'project_name', 'project_type', 'stage', 'specific_status',
                'jtr_conducted', 'nol1_issued'
            ])->toArray(), [
                'cost' => $this->getCost($item),
                'days_jtr_to_nol1' => $daysDiff
            ]);
        });

        $averageDays = round($items->pluck('days_jtr_to_nol1')->filter()->avg() ?? 0);

        return [
            'items' => $items->toArray(),
            'count' => $items->count(),
            'average_days_jtr_to_nol1' => $averageDays
        ];
    }

    /**
     * Reactively updates the chart when the filter key is changed.
     */
    public function updatedFilterKey(): void
    {
        $this->loader = true;
        $this->loadSheetData();
        $this->initChartData();
    }

    /**
     * Returns a placeholder view while the component is loading.
     *
     * @return View
     */
    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.placeholder.section-2');
    }
};

?>
{{-- =========================================================
     I-REAP Subprojects Actual Pace in Pre-Implementation
     Blade View
     ---------------------------------------------------------
     Displays a clustered bar chart comparing prescribed
     timelines vs. actual average durations for subprojects
     in various pre-implementation stages.
     Data and updates are powered by Livewire and Chart.js.
========================================================= --}}

<div>
    <div class="tile-container h-100 d-flex flex-column">

        {{-- ======= Section Header / Filters ======= --}}
        <div class="tile-title d-flex flex-column flex-lg-row row-gap-2 justify-content-between align-items-start"
            style="font-size: 1.2rem;">
            <span>I-REAP Subprojects Actual Pace in Pre-Implementation</span>

            {{-- Filter dropdown to switch between "All", cluster, or region --}}
            <div class="d-flex flex-row gap-2 align-items-center small">
                <div class="fw-normal">Show:</div>
                <select wire:model.live="filterKey" class="form-select filter-dropdown pe-lg-5">
                    <option value="All">All</option>

                    {{-- Clusterwide group --}}
                    <optgroup label="Clusterwide">
                        <option value="Luzon A">Luzon A</option>
                        <option value="Luzon B">Luzon B</option>
                        <option value="Visayas">Visayas</option>
                        <option value="Mindanao">Mindanao</option>
                    </optgroup>

                    {{-- Regionwide group --}}
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
                            BARMM
                        </option>
                    </optgroup>
                </select>
            </div>
        </div>

        {{-- ======= Chart Container ======= --}}
        {{-- Chart.js canvas. Livewire updates this via "generatePreImplChart" event. --}}
        <div wire:ignore class="tile-content position-relative overflow-hidden chart-container" style="height: 400px;">
            <canvas class="tile-chart position-absolute top-0 start-0 w-100 h-100"
                id="sp-actual-pace-pre-implementation-chart"></canvas>
        </div>
    </div>
</div>

{{-- =========================================================
     Chart.js Script Block
     ---------------------------------------------------------
     Handles initialization, destruction, and dynamic rendering
     of the Pre-Implementation Pace chart.
     The chart compares Prescribed Timeline vs. Actual Averages.
========================================================= --}}
@script
<script>
    // Persistent chart instance reference to avoid duplication
    window.chartInstancePreImpl = null;

    /**
     * Renders the Pre-Implementation Chart using Chart.js.
     *
     * @param {Object} chartData - Chart dataset passed from Livewire.
     * Each key represents a stage (e.g., underBusinessPlanPreparation),
     * containing:
     *   - title: string
     *   - prescribed_timeline: number
     *   - average_difference_days: number
     */
    window.ChartPreImpl = function(chartData) {
        const canvas = document.getElementById('sp-actual-pace-pre-implementation-chart');
        if (!canvas) return; // Abort if canvas not found (rarely during hot reload)

        const ctx = canvas.getContext('2d');

        // Destroy old chart if it exists to prevent memory leaks
        if (window.chartInstancePreImpl) {
            window.chartInstancePreImpl.destroy();
            window.chartInstancePreImpl = null;
        }

        // Extract keys from chartData (e.g., ['underBusinessPlanPreparation', 'forRPABApproval', 'nol1Issuance'])
        const groupKeys = Object.keys(chartData);

        // Initialize Chart.js instance
        window.chartInstancePreImpl = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: groupKeys.map(key => chartData[key].title),
                datasets: [
                    {
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
                layout: { padding: { top: 10 } },
                scales: {
                    y: {
                        beginAtZero: true,
                        /**
                         * Dynamically calculate y-axis max value based on the
                         * highest value among all datasets (+20% padding).
                         */
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
                    legend: { display: true },
                    tooltip: {
                        callbacks: {
                            // Tooltip label format: "Prescribed Timeline: 120"
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    },
                    /**
                     * ChartDataLabels plugin configuration.
                     * Displays numeric labels above each bar.
                     */
                    datalabels: {
                        display: true,
                        color: '#000',
                        font: { size: 14 },
                        align: 'end',
                        anchor: 'end',
                        textAlign: 'center',
                        formatter: (value, context) => {
                            // Only show labels for Average dataset (index 1)
                            if (context.datasetIndex === 1 && value > 0) {
                                return `${value}`;
                            }
                            return value > 0 ? `${value}` : '';
                        }
                    }
                },
            },
            plugins: [ChartDataLabels] // Register datalabel plugin
        });
    };

    /**
     * Livewire Event Listener
     * ---------------------------------------------------------
     * Waits for 'generatePreImplChart' event dispatched from
     * the backend component. This event contains the chart data
     * payload that updates the Chart.js visualization.
     */
    Livewire.on('generatePreImplChart', data => {
        window.isChartLoadingPreImpl = true;
        setTimeout(() => {
            if (data[0] && data[0].chartData) {
                window.currentPreImplChartData = data[0].chartData;
                window.ChartPreImpl(data[0].chartData);
                window.isChartLoadingPreImpl = false;
            }
        }, 50); 
    });
</script>
@endscript
