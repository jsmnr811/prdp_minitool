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
    public $subprojectModal = false;
    public $consolidatedTableData = [];
    public $tableData = [];
    public $filterKey = 'All';
    public $loader = false;
    public string $modalTitle = '';
    public string $modalSubtitle = '';
    public array $dataSets = [];
    public $tableContext = [];
    public $filteredData =[];

    private ?SidlanGoogleSheetService $sheetService = null;

    public function mount(): void
    {
        // Initialize the service here safely
        $this->sheetService = new SidlanGoogleSheetService();
        $this->loader = true;
        $this->initChartData();
    }

    private function initChartData(): void
    {
        $this->chartData = $this->initData();
        $this->dispatch('generatePipelineChartSPByStatusModal', ['chartData' => $this->chartData]);
        $this->loader = false;
    }

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

        foreach ($sections as $key => $data) {
            $this->dataSets[$key] = [
                'title' => $titleMap[$key],
                'subject_count' => $data['count'],
                'beyond_timeline_count' => $data['beyondTimelineCount'],
                'key' => $key,
                'average_difference_days' => $data['average_difference_days'],
                'bar_Label' => "{$data['average_difference_days']} days vs \n {$timelineMap[$key]} days timeline",
            ];

            $this->consolidatedTableData[$key] = [
                'subprojectItems' => $data['items'] ?? [],
                'beyondTimelineItems' => $data['beyondTimeline'] ?? [],
            ];
        }

        return $this->dataSets;
    }

    private function fetchDataFromSheet(string $sheetName): \Illuminate\Support\Collection
    {
        if (!$this->sheetService) {
            $this->sheetService = new SidlanGoogleSheetService();
        }

        $data = collect($this->sheetService->getSheetData($sheetName));

        return $data->map(function ($row) {
            $clean = [];
            foreach ($row as $key => $value) {
                $cleanKey = trim($key);
                $cleanValue = is_string($value) ? trim($value) : $value;
                $clean[$cleanKey] = $cleanValue;
            }
            return $clean;
        });
    }

    private function calculateCost(array $item): string
    {
        $fields = ['cost_nol_1', 'rpab_approved_cost', 'estimated_project_cost', 'cost_during_validation', 'indicative_project_cost'];

        foreach ($fields as $field) {
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

            return array_merge($item, [
                'cost' => $this->calculateCost($item),
                'date_difference' => $date->diffInRealDays($now),
                'formatted_date' => $date->format('M j, Y'),
                'dataset_key' => $datasetKey,
            ]);
        });

        $beyondTimeline = $items->filter(fn($item) => Carbon::parse($item[$dateField])->diffInRealDays($now) > $timelineDays);

        return [
            'items' => $items,
            'count' => $items->count(),
            'beyondTimeline' => $beyondTimeline,
            'beyondTimelineCount' => $beyondTimeline->count(),
            'average_difference_days' => round($beyondTimeline->avg('date_difference') ?? 0),
        ];
    }

    private function passesFilter(array $item): bool
    {
        if ($this->filterKey === 'All') return true;

        $filter = trim($this->filterKey);

        $cluster = isset($item['cluster']) ? trim($item['cluster']) : null;
        $region  = isset($item['region']) ? trim($item['region']) : null;

        if (in_array($filter, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'])) {
            return $cluster === $filter;
        }

        if (!empty($region)) {
            return $region === $filter;
        }

        return false;
    }

    private function underBusinessPlanPreparation(): array
    {
        $items = $this->fetchDataFromSheet('ir-01-002')->filter(function ($item) {
            $baseCondition = $item['stage'] === 'Pre-procurement'
                && $item['specific_status'] === 'Subproject Confirmed'
                && !empty($item['subproject_confirmed'])
                && (empty($item['sp_rpab_approved']) || !strtotime($item['sp_rpab_approved']));

            return $baseCondition && $this->passesFilter($item);
        });

        return $this->prepareItems($items, 'subproject_confirmed', 'underBusinessPlanPreparation', 204);
    }

    private function forRPABApproval(): array
    {
        $priorityDates = ['ima_signed_notarized', 'sp_rpab_approved', 'jtr_conducted', 'rpco_technical_review_conducted', 'business_plan_packaged'];

        $items = $this->fetchDataFromSheet('ir-01-002')->filter(function ($item) use ($priorityDates) {
            $baseCondition = $item['stage'] === 'Pre-procurement'
                && in_array($item['specific_status'], [
                    'RPCO Technical Review of Business Plan conducted',
                    'Business Plan Package for RPCO technical review submitted',
                ])
                && collect($priorityDates)->first(fn($field) => !empty($item[$field]))
                && empty($item['nol1_issued']);

            return $baseCondition && $this->passesFilter($item);
        })->map(function ($item) use ($priorityDates) {
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
                && in_array($item['specific_status'], [
                    'Joint Technical Review (JTR) conducted',
                    'SP approved by RPAB',
                    'Signing of the IMA',
                    'Subproject Issued with No Objection Letter 1',
                ])
                && !empty($item['nol1_issued']);

            return $baseCondition && $this->passesFilter($item);
        });

        return $this->prepareItems($items, 'nol1_issued', 'rpabApproved', 120);
    }

    public function updatedFilterKey(): void
    {
        $this->loader = true;

        $this->chartData = $this->initData();
        $this->dispatch('generatePipelineChartSPByStatusModal', ['chartData' => $this->chartData]);

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

    #[On('barClicked')]
    public function barClicked($key, $type): void
    {
        $this->loader = true;
        // $this->chartData = $this->initData();

        $innerKey = $type ? 'beyondTimelineItems' : 'subprojectItems';

        $this->tableData = $this->consolidatedTableData[$key][$innerKey] ?? [];

        $this->modalSubtitle = $this->dataSets[$key]['title'] ?? '';
        if ($innerKey === 'beyondTimelineItems') {
            $this->modalSubtitle .= 'dasdasdasdasd (No. of SPs Beyond Timeline)';
        }
        $this->filteredData = $this->tableData;
        $this->tableContext = [
            'key' => $key,
            'type' => $type,
        ];

        $this->loader = false;
        $this->subprojectModal=true;
        
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
    <!-- ✅ Modal (Livewire + Alpine synced) -->
    @if($subprojectModal)
    <div

        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div
            class="modal-dialog modal-dialog-centered modal-xl bg-white rounded-3 shadow-lg overflow-hidden"
            @click.away="$wire.set('subprojectModal', false)">
            <div class="modal-content border-0">

                <div class="modal-header position-relative flex-column align-items-start pb-0 border-0">
                    <h5 class="modal-title mb-0 fw-bold text-primary">
                        I-REAP Subprojects in the Pipeline (Number of Subprojects by Status)
                    </h5>
                    <small class="text-warning fw-semibold" style="font-size: 1rem;">
                        {{ $modalSubtitle }}
                    </small>
                    <button
                        type="button"
                        class="btn-close position-absolute top-0 end-0 mt-2 me-2"
                        wire:click="$set('subprojectModal', false)"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    @if (count($filteredData) > 0)
                    <div style="overflow-x: auto;">
                        <table class="table table-hover fix-header-table small mb-0" style="min-width: 100%;">
                            <thead>
                                <tr>
                                    <th>Cluster</th>
                                    <th>Region</th>
                                    <th>Province</th>
                                    <th>City/Municipality</th>
                                    <th>Proponent</th>
                                    <th>SP Name</th>
                                    <th>Type</th>
                                    <th>Cost</th>
                                    <th>Stage</th>
                                    <th>Status</th>
                                    <th>No. of days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($filteredData as $data)
                                <tr>
                                    <td>{{ $data['cluster'] }}</td>
                                    <td>{{ $data['region'] }}</td>
                                    <td>{{ $data['province'] }}</td>
                                    <td>{{ $data['city_municipality'] }}</td>
                                    <td>{{ $data['proponent'] }}</td>
                                    <td>{{ $data['project_name'] }}</td>
                                    <td>{{ $data['project_type'] }}</td>
                                    <td>{{ $data['cost'] }}</td>
                                    <td>{{ $data['stage'] }}</td>
                                    <td>{{ $data['specific_status'] }}</td>
                                    <td>
                                        {{ round($data['date_difference']) }} days from
                                        @if($data['dataset_key'] === 'underBusinessPlanPreparation')
                                        Date of confirmation ({{ $data['formatted_date'] }})
                                        @elseif($data['dataset_key'] === 'forRPABApproval')
                                        FS / DED Preparation ({{ $data['formatted_date'] }})
                                        @elseif($data['dataset_key'] === 'rpabApproved')
                                        RPAB Approval ({{ $data['formatted_date'] }})
                                        @else
                                        Date of confirmation ({{ $data['formatted_date'] }})
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p>No data found for this category.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
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
    Livewire.on('generatePipelineChartSPByStatusModal', data => {
        setTimeout(() => {
            if (data[0] && data[0].chartData) {
                window.ChartOne(data[0].chartData);
                $wire.set('loader', false);
            }
        }, 50);
    });
</script>
@endscript