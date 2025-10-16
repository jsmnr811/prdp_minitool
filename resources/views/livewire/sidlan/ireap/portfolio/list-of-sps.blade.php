<?php

use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Services\SidlanGoogleSheetService;
use Livewire\Attributes\On;

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



    public $approvedTableData = [];
    public $approvedStages = [];

    public $pipelineTableData = [];
    public $pipelinedStages = [];

    public $overallTableData = [];
    public $overallStages = [];

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

        // --- PIPELINED ---
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
            return in_array($stage, ['Implementation', 'For procurement', 'Completed']) && $hasNol1;
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

        // ============================================================
        // APPROVED TABLE DATA
        // ============================================================
        $this->approvedStages = $approvedItems->pluck('stage')->unique()->values()->all();

        $this->approvedTableData = $this->buildTableData($approvedItems);

        // ============================================================
        // PIPELINED TABLE DATA
        // ============================================================
        $this->pipelinedStages = $pipelineItems->pluck('stage')->unique()->values()->all();

        $this->pipelineTableData = $this->buildTableData($pipelineItems);

        // ============================================================
        // OVERALL TABLE DATA (Approved + Pipelined Combined)
        // ============================================================
        $combinedItems = $approvedItems->merge($pipelineItems);

        // Collect all unique stages across both sets
        $overallStages = collect($this->approvedStages)
            ->merge($this->pipelinedStages)
            ->unique()
            ->values()
            ->all();

        // Build unified table data
        $this->overallTableData = $this->buildTableData($combinedItems);
        $this->overallStages = $overallStages;
    }

    private function buildTableData($items)
    {
        // Define the custom cluster order
        $clusterOrder = ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao'];

        // Group and compute table data
        $grouped = $items
            ->groupBy(['cluster', 'region', 'province'])
            ->map(function ($regions, $cluster) {
                return collect($regions)->map(function ($provinces, $region) {
                    return collect($provinces)->map(function ($provinceItems, $province) {
                        $stages = collect($provinceItems)->groupBy('stage')->map(function ($stageItems) {
                            return [
                                'count' => $stageItems->count(),
                                'cost' => $stageItems->sum(
                                    fn($item) => floatval($item['cost_during_validation'] ?? $item['sp_indicative_cost'] ?? 0)
                                ),
                            ];
                        });

                        return [
                            'cluster' => $provinceItems->first()['cluster'] ?? '',
                            'region' => $provinceItems->first()['region'] ?? '',
                            'province' => $provinceItems->first()['province'] ?? '',
                            'stages' => $stages,
                            'total_count' => $stages->sum('count'),
                            'total_cost' => $stages->sum('cost'),
                        ];
                    })->values();
                });
            });

        // Flatten and sort by cluster using custom order
        return collect($grouped)
            ->values()
            ->flatten(2)
            ->sortBy(function ($item) use ($clusterOrder) {
                $cluster = $item['cluster'] ?? '';
                $index = array_search(ucwords(strtolower($cluster)), array_map('ucwords', $clusterOrder));
                return $index !== false ? $index : 999; // unknown clusters go last
            })
            ->values()
            ->toArray();
    }

    #[On('export')]
    public function export(string $type = 'portfolio')
    {
        $data = match ($type) {
            'approved' => $this->approvedTableData,
            'pipelined' => $this->pipelineTableData,
            default => $this->overallTableData,
        };

        $fileName = "ireap_{$type}_" . now()->format('Ymd_His') . ".xlsx";

        return response()->streamDownload(function () use ($data, $type) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('D2-Portfolio');


            $approvedStages = ['Implementation', 'For procurement', 'Completed'];
            $pipelinedStages = $this->pipelinedStages ?? [];

            if ($type === 'approved') $pipelinedStages = [];
            if ($type === 'pipelined') $approvedStages = [];

            // --- Title & Subtitle ---
            $title = "PRDP Scale-Up Rural Infrastructure Development Subprojects Portfolio (I-REAP)";
            $dateTime = now()->format('M d, Y h:i:s A');
            $typeLabel = match ($type) {
                'approved' => 'Approved Portfolio',
                'pipelined' => 'Pipelined Portfolio',
                default => 'Overall Portfolio',
            };
            $subtitle = "As of {$dateTime} - {$typeLabel}";

            $totalColumns = 3 + (count($approvedStages) + count($pipelinedStages)) * 2 + 2;
            $highestColLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($totalColumns);

            // Title
            $sheet->setCellValue("A1", $title);
            $sheet->mergeCells("A1:{$highestColLetter}1");
            $sheet->getStyle("A1")->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle("A1")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A1:{$highestColLetter}1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            $sheet->getStyle("A1:{$highestColLetter}1")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

            // Subtitle
            $sheet->setCellValue("A2", $subtitle);
            $sheet->mergeCells("A2:{$highestColLetter}2");
            $sheet->getStyle("A2")->getFont()->setBold(true)->setSize(12);
            $sheet->getStyle("A2")->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A2:{$highestColLetter}2")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE);
            $sheet->getStyle("A2:{$highestColLetter}2")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_NONE);

            $rowIndex = 5;

            // --- Header Rows ---
            $sheet->setCellValue("A{$rowIndex}", "AREAS PER CLUSTER");
            $sheet->mergeCells("A{$rowIndex}:C" . ($rowIndex + 1));
            $colIndex = 4;

            // Approved Subprojects header
            $sheet->mergeCells(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex .
                    ":" .
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + count($approvedStages) * 2 - 1) . $rowIndex
            );
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, "APPROVED SUBPROJECTS");
            $colIndex += count($approvedStages) * 2;

            // Pipelined Subprojects header
            if (count($pipelinedStages) > 0) {
                $sheet->mergeCells(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex .
                        ":" .
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + count($pipelinedStages) * 2 - 1) . $rowIndex
                );
                $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, "PIPELINED SUBPROJECTS");
                $colIndex += count($pipelinedStages) * 2;
            }

            // Total header
            $sheet->mergeCells(
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex .
                    ":" .
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . ($rowIndex + 1)
            );
            $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, "TOTAL");

            $rowIndex++;

            // Stage names header
            $colIndex = 4;
            foreach (array_merge($approvedStages, $pipelinedStages) as $stage) {
                $sheet->mergeCells(
                    \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . $rowIndex .
                        ":" .
                        \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . $rowIndex
                );
                $sheet->setCellValueByColumnAndRow($colIndex, $rowIndex, strtoupper($stage));
                $colIndex += 2;
            }
            $rowIndex++;

            // No./Cost row
            $headers = ['CLUSTER', 'REGION', 'PROVINCE'];
            foreach (array_merge($approvedStages, $pipelinedStages) as $_) {
                $headers[] = 'NO.';
                $headers[] = 'COST';
            }
            $headers[] = 'NO.';
            $headers[] = 'COST';
            $sheet->fromArray($headers, null, "A{$rowIndex}");

            // Apply header styling (light blue fill, bold, borders)
            $highestColumn = $sheet->getHighestColumn();
            $sheet->getStyle("A5:{$highestColumn}{$rowIndex}")->applyFromArray([
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'C5D9F1'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            // Column width
            $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
            for ($i = 1; $i <= $highestColumnIndex; $i++) {
                $sheet->getColumnDimension(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i))->setWidth(20);
            }

            $rowIndex++;

            // --- DATA ROWS ---
            $grandTotalCols = [];
            $grouped = collect($data)->groupBy(['cluster', 'region']);

            foreach ($grouped as $cluster => $regions) {
                $clusterStart = $rowIndex;
                $clusterSubtotalCols = [];

                foreach ($regions as $region => $rows) {
                    $regionStart = $rowIndex;
                    foreach ($rows as $row) {
                        $colIndex = 1;
                        $sheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, $cluster);
                        $sheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, $region);
                        $sheet->setCellValueByColumnAndRow($colIndex++, $rowIndex, $row['province']);

                        $stageColumns = array_merge($approvedStages, $pipelinedStages);
                        $colPointer = 4;
                        foreach ($stageColumns as $stage) {
                            $count = $row['stages'][$stage]['count'] ?? 0;
                            $cost = $row['stages'][$stage]['cost'] ?? 0;

                            $sheet->setCellValueByColumnAndRow($colPointer, $rowIndex, $count);
                            $sheet->setCellValueByColumnAndRow($colPointer + 1, $rowIndex, $cost);

                            $sheet->getStyleByColumnAndRow($colPointer, $rowIndex)->getNumberFormat()->setFormatCode('0');
                            $sheet->getStyleByColumnAndRow($colPointer + 1, $rowIndex)->getNumberFormat()->setFormatCode('#,##0.00');

                            $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->getAlignment()
                                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP)
                                ->setWrapText(true);

                            $clusterSubtotalCols[$colPointer] = ($clusterSubtotalCols[$colPointer] ?? 0) + $count;
                            $clusterSubtotalCols[$colPointer + 1] = ($clusterSubtotalCols[$colPointer + 1] ?? 0) + $cost;
                            $grandTotalCols[$colPointer] = ($grandTotalCols[$colPointer] ?? 0) + $count;
                            $grandTotalCols[$colPointer + 1] = ($grandTotalCols[$colPointer + 1] ?? 0) + $cost;

                            $colPointer += 2;
                        }

                        // Total columns
                        $totalCount = $row['total_count'] ?? 0;
                        $totalCost = $row['total_cost'] ?? 0;
                        $sheet->setCellValueByColumnAndRow($colPointer, $rowIndex, $totalCount);
                        $sheet->setCellValueByColumnAndRow($colPointer + 1, $rowIndex, $totalCost);
                        $sheet->getStyleByColumnAndRow($colPointer, $rowIndex)->getNumberFormat()->setFormatCode('0');
                        $sheet->getStyleByColumnAndRow($colPointer + 1, $rowIndex)->getNumberFormat()->setFormatCode('#,##0.00');

                        $clusterSubtotalCols[$colPointer] = ($clusterSubtotalCols[$colPointer] ?? 0) + $totalCount;
                        $clusterSubtotalCols[$colPointer + 1] = ($clusterSubtotalCols[$colPointer + 1] ?? 0) + $totalCost;
                        $grandTotalCols[$colPointer] = ($grandTotalCols[$colPointer] ?? 0) + $totalCount;
                        $grandTotalCols[$colPointer + 1] = ($grandTotalCols[$colPointer + 1] ?? 0) + $totalCost;

                        // Optional: borders for data row
                        $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                        $rowIndex++;
                    }

                    // Merge region
                    $sheet->mergeCells("B{$regionStart}:B" . ($rowIndex - 1));
                }

                // Merge cluster including subtotal row
                $sheet->mergeCells("A{$clusterStart}:A{$rowIndex}");

                // --- Subtotal row ---
                $sheet->setCellValueByColumnAndRow(2, $rowIndex, "SUB-TOTAL");
                $sheet->mergeCells("B{$rowIndex}:C{$rowIndex}");
                $colIndex = 4;
                foreach ($clusterSubtotalCols as $col => $val) {
                    $sheet->setCellValueByColumnAndRow($col, $rowIndex, $val);
                    $relativeCol = $col - 3;
                    $isCost = ($relativeCol % 2 == 0);
                    $sheet->getStyleByColumnAndRow($col, $rowIndex)->getNumberFormat()->setFormatCode($isCost ? '#,##0.00' : '0');
                }

                // Subtotal row styling: gray fill, borders
                $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->applyFromArray([
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9D9D9'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $rowIndex++;
            }

            // --- Grand total row ---
            $sheet->setCellValueByColumnAndRow(1, $rowIndex, "GRAND TOTAL");
            $sheet->mergeCells("A{$rowIndex}:C{$rowIndex}");
            $colIndex = 4;
            foreach ($grandTotalCols as $col => $val) {
                $sheet->setCellValueByColumnAndRow($col, $rowIndex, $val);
                $relativeCol = $col - 3;
                $isCost = ($relativeCol % 2 == 0);
                $sheet->getStyleByColumnAndRow($col, $rowIndex)->getNumberFormat()->setFormatCode($isCost ? '#,##0.00' : '0');
            }

            // Grand total row styling: gray fill, borders
            $sheet->getStyle("A{$rowIndex}:{$highestColumn}{$rowIndex}")->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
                'font' => ['bold' => true],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ]);

            // --- Save ---
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName);
    }


    public function placeholder(): View
    {
        return view('livewire.sidlan.ireap.portfolio.placeholder.counter');
    }
};

?>


@php
$approvedStages = $approvedStages ?? ['Implementation', 'For procurement', 'Completed'];
$pipelinedStages = $pipelinedStages ?? ['Pre-Procurement'];
@endphp

<div id="ireap-portfolio-wrapper" class="col-12" wire:ignore>
    <div class="tile-container">
        <div class="tile-title">List of I-REAP Scale-Up Subprojects</div>

        <!-- Tabs -->
        <div class="d-flex flex-row gap-2 mb-2">
            <button class="btn border border-primary ireap-portfolio-nav active" data-target="portfolio">Overall Portfolio</button>
            <button class="btn border border-primary ireap-portfolio-nav" data-target="approved">Approved</button>
            <button class="btn border border-primary ireap-portfolio-nav" data-target="pipelined">Pipelined</button>
            <div class="border-start border-2 border-primary d-none d-lg-block"></div>
            <button id="ireap-portfolio-export" class="btn border border-primary active" style="display: inline-flex; align-items: center;   background-color: #0d6efd; color: #fff; border: 1px solid #0d6efd;"">
                <span class="btn-normal" style="display: inline-flex; align-items: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-download me-1 small" viewBox="0 0 16 16">
                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"></path>
                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"></path>
                    </svg>
                    Export
                </span>
                <span class="btn-loading" style="display:none; align-items: center;">
                    <i class="fa fa-spinner fa-spin me-1"></i> Downloading...
                </span>
            </button>
        </div>

        {{-- OVERALL TABLE --}}
        <div class="ireap-portfolio-content" data-content="portfolio">
            <div class="tile-content">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table content-table text-center" style="width:100%; border-collapse: collapse;   height: 100%;">
                        <thead style=" position: sticky; top: 0;">
                            <tr>
                                <th rowspan="2" colspan="3">Areas Per Cluster</th>
                                <th colspan="6">APPROVED SUBPROJECTS</th>
                                <th colspan="{{ count($pipelinedStages)*2 }}">PIPELINED SUBPROJECTS</th>
                                <th rowspan="2" colspan="2">Total</th>
                            </tr>
                            <tr>
                                <th colspan="2">Implementation</th>
                                <th colspan="2">For procurement</th>
                                <th colspan="2">Completed</th>
                                @foreach ($pipelinedStages as $stage)
                                <th colspan="2">{{ $stage }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th>Cluster</th>
                                <th>Region</th>
                                <th>Province</th>
                                {{-- Approved --}}
                                <th>No.</th>
                                <th>Cost</th>
                                <th>No.</th>
                                <th>Cost</th>
                                <th>No.</th>
                                <th>Cost</th>
                                {{-- Pipelined --}}
                                @foreach ($pipelinedStages as $stage)
                                <th>No.</th>
                                <th>Cost</th>
                                @endforeach
                                <th>No.</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $approvedStages = ['Implementation', 'For procurement', 'Completed'];

                            $groupedByCluster = collect($overallTableData)
                            ->groupBy('cluster')
                            ->sortBy(fn($_, $cluster) => array_search($cluster, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao']));

                            $grandTotal = ['count' => 0, 'cost' => 0, 'stages' => []];
                            @endphp

                            @foreach ($groupedByCluster as $cluster => $clusterGroup)
                            @php
                            $clusterRowspan = $clusterGroup->groupBy('region')->flatten(1)->count() + 1;

                            $clusterSubtotal = [
                            'count' => $clusterGroup->sum('total_count'),
                            'cost' => $clusterGroup->sum('total_cost'),
                            'stages' => [],
                            ];

                            foreach (array_merge($approvedStages, $pipelinedStages) as $stage) {
                            $clusterSubtotal['stages'][$stage] = [
                            'count' => $clusterGroup->sum(fn($r) => $r['stages'][$stage]['count'] ?? 0),
                            'cost' => $clusterGroup->sum(fn($r) => $r['stages'][$stage]['cost'] ?? 0),
                            ];
                            }

                            $grandTotal['count'] += $clusterSubtotal['count'];
                            $grandTotal['cost'] += $clusterSubtotal['cost'];

                            foreach ($clusterSubtotal['stages'] as $stage => $vals) {
                            $grandTotal['stages'][$stage]['count'] = ($grandTotal['stages'][$stage]['count'] ?? 0) + $vals['count'];
                            $grandTotal['stages'][$stage]['cost'] = ($grandTotal['stages'][$stage]['cost'] ?? 0) + $vals['cost'];
                            }
                            @endphp

                            @foreach ($clusterGroup->groupBy('region') as $region => $regionGroup)
                            @php $regionRowspan = $regionGroup->count(); @endphp

                            @foreach ($regionGroup as $i => $row)
                            <tr>
                                {{-- CLUSTER --}}
                                @if ($loop->parent->first && $i === 0)
                                <td rowspan="{{ $clusterRowspan }}" class="align-middle font-bold border border-gray-300 bg-gray-50">
                                    {{ $cluster }}
                                </td>
                                @endif

                                {{-- REGION --}}
                                @if ($i === 0)
                                <td rowspan="{{ $regionRowspan }}" class="align-middle border border-gray-200">
                                    {{ $region }}
                                </td>
                                @endif

                                {{-- PROVINCE --}}
                                <td class="border border-gray-200 text-left">{{ $row['province'] }}</td>

                                {{-- APPROVED STAGES --}}
                                @foreach ($approvedStages as $stage)
                                <td class="border border-gray-200 text-center">{{ $row['stages'][$stage]['count'] ?? 0 }}</td>
                                <td class="border border-gray-200 text-right" style="text-align:right;">{{ number_format($row['stages'][$stage]['cost'] ?? 0, 2) }}</td>
                                @endforeach

                                {{-- PIPELINED STAGES --}}
                                @foreach ($pipelinedStages as $stage)
                                <td class="border border-gray-200 text-center">{{ $row['stages'][$stage]['count'] ?? 0 }}</td>
                                <td class="border border-gray-200 text-right" style="text-align:right;">{{ number_format($row['stages'][$stage]['cost'] ?? 0, 2) }}</td>
                                @endforeach

                                {{-- TOTAL --}}
                                <td class="border border-gray-200 text-center font-semibold">{{ $row['total_count'] }}</td>
                                <td class="border border-gray-200 text-right font-semibold" style="text-align:right;">{{ number_format($row['total_cost'], 2) }}</td>
                            </tr>
                            @endforeach
                            @endforeach

                            {{-- CLUSTER SUBTOTAL ROW (Grayed) --}}
                            <tr style="background-color:#f3f4f6; font-weight:600; border-top:2px solid #d1d5db; height:32px;">
                                <td colspan="2" style="text-align:right; padding-left:8px; color:#374151; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    Sub-total
                                </td>
                                @foreach ($approvedStages as $stage)
                                <td style="text-align:center; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    {{ $clusterSubtotal['stages'][$stage]['count'] ?? 0 }}
                                </td>
                                <td style="text-align:right; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    {{ number_format($clusterSubtotal['stages'][$stage]['cost'] ?? 0, 2) }}
                                </td>
                                @endforeach
                                @foreach ($pipelinedStages as $stage)
                                <td style="text-align:center; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    {{ $clusterSubtotal['stages'][$stage]['count'] ?? 0 }}
                                </td>
                                <td style="text-align:right; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    {{ number_format($clusterSubtotal['stages'][$stage]['cost'] ?? 0, 2) }}
                                </td>
                                @endforeach
                                <td style="text-align:center; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    {{ $clusterSubtotal['count'] }}
                                </td>
                                <td style="text-align:right; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    {{ number_format($clusterSubtotal['cost'], 2) }}
                                </td>
                            </tr>
                            @endforeach

                            {{-- GRAND TOTAL ROW (Darker Gray) --}}
                            <tr style="background-color:#e5e7eb; font-weight:700; border-top:3px solid #9ca3af; height:32px;">
                                <td colspan="3" style="text-align:right; padding-left:8px; color:#111827; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    TOTAL / OVERALL
                                </td>
                                @foreach ($approvedStages as $stage)
                                <td style="text-align:center; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    {{ $grandTotal['stages'][$stage]['count'] ?? 0 }}
                                </td>
                                <td style="text-align:right; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    {{ number_format($grandTotal['stages'][$stage]['cost'] ?? 0, 2) }}
                                </td>
                                @endforeach
                                @foreach ($pipelinedStages as $stage)
                                <td style="text-align:center; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    {{ $grandTotal['stages'][$stage]['count'] ?? 0 }}
                                </td>
                                <td style="text-align:right; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    {{ number_format($grandTotal['stages'][$stage]['cost'] ?? 0, 2) }}
                                </td>
                                @endforeach
                                <td style="text-align:center; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    {{ $grandTotal['count'] }}
                                </td>
                                <td style="text-align:right; background-color:#e5e7eb; border:1px solid #9ca3af; font-weight: bold;">
                                    {{ number_format($grandTotal['cost'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- APPROVED TABLE --}}
        <div class="ireap-portfolio-content d-none" data-content="approved">
            <div class="tile-content">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table content-table text-center" style="width:100%; border-collapse: collapse;   height: 100%;">
                        <thead style=" position: sticky; top: 0;">
                            <tr>
                                <th rowspan="2" colspan="3">Areas Per Cluster</th>
                                <th colspan="6">APPROVED SUBPROJECTS</th>
                                <th rowspan="2" colspan="2">Total</th>
                            </tr>
                            <tr>
                                <th colspan="2">Implementation</th>
                                <th colspan="2">For Procurement</th>
                                <th colspan="2">Completed</th>
                            </tr>
                            <tr>
                                <th>Cluster</th>
                                <th>Region</th>
                                <th>Province</th>
                                <th>No.</th>
                                <th>Cost</th>
                                <th>No.</th>
                                <th>Cost</th>
                                <th>No.</th>
                                <th>Cost</th>
                                <th>No.</th>
                                <th>Cost</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                            $approvedStages = ['Implementation', 'For procurement', 'Completed'];

                            $groupedByCluster = collect($approvedTableData)
                            ->groupBy('cluster')
                            ->sortBy(fn($_, $c) => array_search($c, ['Luzon A','Luzon B','Visayas','Mindanao']));

                            $grandTotal = ['count' => 0, 'cost' => 0, 'stages' => []];
                            @endphp

                            @foreach ($groupedByCluster as $cluster => $clusterGroup)
                            @php
                            $clusterRowspan = $clusterGroup->groupBy('region')->flatten(1)->count() + 1;
                            $clusterSubtotal = [
                            'count' => $clusterGroup->sum('total_count'),
                            'cost' => $clusterGroup->sum('total_cost'),
                            'stages' => []
                            ];
                            foreach ($approvedStages as $stage) {
                            $clusterSubtotal['stages'][$stage] = [
                            'count' => $clusterGroup->sum(fn($r) => $r['stages'][$stage]['count'] ?? 0),
                            'cost' => $clusterGroup->sum(fn($r) => $r['stages'][$stage]['cost'] ?? 0),
                            ];
                            }
                            $grandTotal['count'] += $clusterSubtotal['count'];
                            $grandTotal['cost'] += $clusterSubtotal['cost'];
                            foreach ($approvedStages as $stage) {
                            $grandTotal['stages'][$stage]['count'] = ($grandTotal['stages'][$stage]['count'] ?? 0) + $clusterSubtotal['stages'][$stage]['count'];
                            $grandTotal['stages'][$stage]['cost'] = ($grandTotal['stages'][$stage]['cost'] ?? 0) + $clusterSubtotal['stages'][$stage]['cost'];
                            }
                            @endphp

                            @foreach ($clusterGroup->groupBy('region') as $region => $regionGroup)
                            @php $regionRowspan = $regionGroup->count(); @endphp

                            @foreach ($regionGroup as $i => $row)
                            <tr class="border-b border-gray-200">
                                {{-- CLUSTER --}}
                                @if ($loop->parent->first && $i === 0)
                                <td rowspan="{{ $clusterRowspan }}" class="align-middle font-bold border border-gray-300 bg-gray-50">{{ $cluster }}</td>
                                @endif

                                {{-- REGION --}}
                                @if ($i === 0)
                                <td rowspan="{{ $regionRowspan }}" class="align-middle border border-gray-200">{{ $region }}</td>
                                @endif

                                {{-- PROVINCE --}}
                                <td class="align-middle border border-gray-200 text-left">{{ $row['province'] }}</td>

                                {{-- STAGES --}}
                                @foreach ($approvedStages as $stage)
                                <td class="border border-gray-200 text-center">{{ $row['stages'][$stage]['count'] ?? 0 }}</td>
                                <td class="border border-gray-200 text-right" style="text-align:right;">{{ number_format($row['stages'][$stage]['cost'] ?? 0, 2) }}</td>
                                @endforeach

                                {{-- TOTALS --}}
                                <td class="border border-gray-200 text-center font-semibold">{{ $row['total_count'] ?? 0 }}</td>
                                <td class="border border-gray-200 text-right font-semibold" style="text-align:right;">{{ number_format($row['total_cost'] ?? 0, 2) }}</td>
                            </tr>
                            @endforeach
                            @endforeach

                            {{-- SUBTOTAL ROW --}}
                            <tr style="background-color:#f3f4f6; font-weight:600; border-top:2px solid #d1d5db; height:32px;">
                                <td colspan="2" style="text-align:right; padding-left:8px; color:#374151; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    Sub-total
                                </td>
                                @foreach ($approvedStages as $stage)
                                <td style="text-align:center; background-color:#f3f4f6; border:1px solid #d1d5db;">{{ $clusterSubtotal['stages'][$stage]['count'] }}</td>
                                <td style="text-align:right; background-color:#f3f4f6; border:1px solid #d1d5db;">{{ number_format($clusterSubtotal['stages'][$stage]['cost'], 2) }}</td>
                                @endforeach
                                <td style="text-align:center; background-color:#f3f4f6; border:1px solid #d1d5db;">{{ $clusterSubtotal['count'] }}</td>
                                <td style="text-align:right; background-color:#f3f4f6; border:1px solid #d1d5db;">{{ number_format($clusterSubtotal['cost'], 2) }}</td>
                            </tr>
                            @endforeach

                            {{-- GRAND TOTAL --}}
                            <tr style="background-color:#e5e7eb; font-weight:700; border-top:3px solid #9ca3af; height:32px;">
                                <td colspan="3" style="font-weight:bold; text-align:right; padding-left:8px; background-color:#e5e7eb; border:1px solid #9ca3af;">TOTAL / OVERALL</td>
                                @foreach ($approvedStages as $stage)
                                <td style="font-weight:bold; text-align:center; background-color:#e5e7eb; border:1px solid #9ca3af;">{{ $grandTotal['stages'][$stage]['count'] ?? 0 }}</td>
                                <td style="font-weight:bold; text-align:right; background-color:#e5e7eb; border:1px solid #9ca3af;">{{ number_format($grandTotal['stages'][$stage]['cost'] ?? 0, 2) }}</td>
                                @endforeach
                                <td style="font-weight:bold; text-align:center; background-color:#e5e7eb; border:1px solid #9ca3af;">{{ $grandTotal['count'] }}</td>
                                <td style="font-weight:bold; text-align:right; background-color:#e5e7eb; border:1px solid #9ca3af;">{{ number_format($grandTotal['cost'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- PIPELINED TABLE --}}
        <div class="ireap-portfolio-content d-none" data-content="pipelined">
            <div class="tile-content">
                <div class="table-responsive" style="max-height: 600px;">
                    <table class="table content-table text-center" style="width:100%; border-collapse: collapse;  height: 100%;">
                        <thead style=" position: sticky; top: 0;">
                            <tr>
                                <th rowspan="2" colspan="3">Areas Per Cluster</th>
                                <th colspan="{{ count($pipelinedStages)*2 }}">PIPELINED SUBPROJECTS</th>
                                <th rowspan="2" colspan="2">Total</th>
                            </tr>
                            <tr>
                                @foreach ($pipelinedStages as $stage)
                                <th colspan="2">{{ $stage }}</th>
                                @endforeach
                            </tr>
                            <tr>
                                <th>Cluster</th>
                                <th>Region</th>
                                <th>Province</th>
                                @foreach ($pipelinedStages as $stage)
                                <th>No.</th>
                                <th>Cost</th>
                                @endforeach
                                <th>No.</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $groupedByCluster = collect($pipelineTableData)
                            ->groupBy('cluster')
                            ->sortBy(function ($_, $cluster) {
                            return array_search($cluster, ['Luzon A', 'Luzon B', 'Visayas', 'Mindanao']);
                            });

                            $grandTotal = [
                            'count' => 0,
                            'cost' => 0,
                            'stages' => [],
                            ];
                            @endphp

                            @foreach ($groupedByCluster as $cluster => $clusterGroup)
                            @php
                            // Add 1 for the subtotal row
                            $clusterRowspan = $clusterGroup->groupBy('region')->flatten(1)->count() + 1;

                            // Compute cluster subtotal
                            $clusterSubtotal = [
                            'count' => $clusterGroup->sum('total_count'),
                            'cost' => $clusterGroup->sum('total_cost'),
                            'stages' => [],
                            ];

                            foreach ($pipelinedStages as $stage) {
                            $clusterSubtotal['stages'][$stage] = [
                            'count' => $clusterGroup->sum(fn($r) => $r['stages'][$stage]['count'] ?? 0),
                            'cost' => $clusterGroup->sum(fn($r) => $r['stages'][$stage]['cost'] ?? 0),
                            ];
                            }

                            // Update grand totals
                            $grandTotal['count'] += $clusterSubtotal['count'];
                            $grandTotal['cost'] += $clusterSubtotal['cost'];
                            foreach ($pipelinedStages as $stage) {
                            $grandTotal['stages'][$stage]['count'] = ($grandTotal['stages'][$stage]['count'] ?? 0) + $clusterSubtotal['stages'][$stage]['count'];
                            $grandTotal['stages'][$stage]['cost'] = ($grandTotal['stages'][$stage]['cost'] ?? 0) + $clusterSubtotal['stages'][$stage]['cost'];
                            }
                            @endphp

                            @foreach ($clusterGroup->groupBy('region') as $region => $regionGroup)
                            @php $regionRowspan = $regionGroup->count(); @endphp

                            @foreach ($regionGroup as $provinceIndex => $row)
                            <tr class="border-b border-gray-200">
                                {{-- CLUSTER --}}
                                @if ($loop->parent->first && $provinceIndex === 0)
                                <td rowspan="{{ $clusterRowspan }}" class="align-middle font-bold bg-gray-50 border border-gray-200">
                                    {{ $cluster }}
                                </td>
                                @endif

                                {{-- REGION --}}
                                @if ($provinceIndex === 0)
                                <td rowspan="{{ $regionRowspan }}" class="align-middle border border-gray-200">
                                    {{ $region }}
                                </td>
                                @endif

                                {{-- PROVINCE --}}
                                <td class="border border-gray-200">{{ $row['province'] }}</td>

                                {{-- STAGES --}}
                                @foreach ($pipelinedStages as $stage)
                                <td class="border border-gray-200 text-center">{{ $row['stages'][$stage]['count'] ?? 0 }}</td>
                                <td class="border border-gray-200 text-right">{{ number_format($row['stages'][$stage]['cost'] ?? 0, 2) }}</td>
                                @endforeach

                                {{-- TOTAL --}}
                                <td class="border border-gray-200 text-center font-semibold">{{ $row['total_count'] }}</td>
                                <td class="border border-gray-200 text-right font-semibold">{{ number_format($row['total_cost'], 2) }}</td>
                            </tr>
                            @endforeach
                            @endforeach

                            {{-- CLUSTER SUBTOTAL ROW --}}
                            <tr style="background-color:#f3f4f6; font-weight:600; border-top:2px solid #d1d5db; height:32px;">
                                <td colspan="2" style="text-align:right; padding-left:8px; color:#374151; background-color:#f3f4f6; border:1px solid #d1d5db;">
                                    Sub-total
                                </td>
                                @foreach ($pipelinedStages as $stage)
                                <td style="border:1px solid #d1d5db; background-color:#f3f4f6; color:#374151; text-align:center;">
                                    {{ $clusterSubtotal['stages'][$stage]['count'] }}
                                </td>
                                <td style="border:1px solid #d1d5db; background-color:#f3f4f6; color:#374151; text-align:right;">
                                    {{ number_format($clusterSubtotal['stages'][$stage]['cost'], 2) }}
                                </td>
                                @endforeach
                                <td style="border:1px solid #d1d5db; background-color:#f3f4f6; color:#374151; text-align:center;">
                                    {{ $clusterSubtotal['count'] }}
                                </td>
                                <td style="border:1px solid #d1d5db; background-color:#f3f4f6; color:#374151; text-align:right;">
                                    {{ number_format($clusterSubtotal['cost'], 2) }}
                                </td>
                            </tr>
                            @endforeach

                            {{-- GRAND TOTAL ROW --}}
                            <tr class="font-bold uppercase"
                                style="background-color:#e5e7eb; border-top:3px solid #9ca3af; height:32px;">
                                <td colspan="3"
                                    style="font-weight:bold; text-align:right; padding-left:8px; color:#111827; background-color:#e5e7eb; border:1px solid #9ca3af;">
                                    Total / Overall
                                </td>
                                @foreach ($pipelinedStages as $stage)
                                <td style="font-weight:bold; border:1px solid #9ca3af; background-color:#e5e7eb; text-align:center; color:#111827;">
                                    {{ $grandTotal['stages'][$stage]['count'] ?? 0 }}
                                </td>
                                <td style="font-weight:bold; border:1px solid #9ca3af; background-color:#e5e7eb; text-align:right; color:#111827;">
                                    {{ number_format($grandTotal['stages'][$stage]['cost'], 2) }}
                                </td>
                                @endforeach
                                <td style="font-weight:bold; border:1px solid #9ca3af; background-color:#e5e7eb; text-align:center; color:#111827;">
                                    {{ $grandTotal['count'] }}
                                </td>
                                <td style="font-weight:bold; border:1px solid #9ca3af; background-color:#e5e7eb; text-align:right; color:#111827;">
                                    {{ number_format($grandTotal['cost'], 2) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const buttons = document.querySelectorAll('#ireap-portfolio-wrapper .ireap-portfolio-nav');
        const exportBtn = document.querySelector('#ireap-portfolio-export');
        const btnNormal = exportBtn.querySelector('.btn-normal');
        const btnLoading = exportBtn.querySelector('.btn-loading');

        // Tab buttons
        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                buttons.forEach(b => {
                    b.classList.remove('active');
                    b.style.backgroundColor = '';
                    b.style.color = '';
                    b.style.borderColor = '';
                });

                btn.classList.add('active');
                btn.style.backgroundColor = '#0d6efd';
                btn.style.color = '#fff';
                btn.style.borderColor = '#0d6efd';

                const target = btn.dataset.target;
                document.querySelectorAll('#ireap-portfolio-wrapper .ireap-portfolio-content')
                    .forEach(tc => tc.classList.add('d-none'));
                document.querySelector(`#ireap-portfolio-wrapper [data-content="${target}"]`)
                    .classList.remove('d-none');
            });
        });

        // Set initial active tab
        const overallBtn = document.querySelector('#ireap-portfolio-wrapper .ireap-portfolio-nav[data-target="portfolio"]');
        overallBtn.classList.add('active');
        overallBtn.style.backgroundColor = '#0d6efd';
        overallBtn.style.color = '#fff';
        overallBtn.style.borderColor = '#0d6efd';
        document.querySelectorAll('#ireap-portfolio-wrapper .ireap-portfolio-content')
            .forEach(tc => tc.classList.add('d-none'));
        document.querySelector('#ireap-portfolio-wrapper [data-content="portfolio"]')
            .classList.remove('d-none');

        // Export button click
        exportBtn.addEventListener('click', () => {
            const activeBtn = document.querySelector('#ireap-portfolio-wrapper .ireap-portfolio-nav.active');
            const type = activeBtn?.dataset.target || 'portfolio';

            // Show loading state
            btnNormal.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            exportBtn.disabled = true;

            // Dispatch Livewire export event
            Livewire.dispatch('export', {
                type
            });

            // Re-apply active styling after Livewire refresh (optional)
            setTimeout(() => {
                activeBtn?.classList.add('active');
            }, 50);

            // Reset button after delay (adjust based on download time)
            setTimeout(() => {
                btnNormal.style.display = 'inline-flex';
                btnLoading.style.display = 'none';
                exportBtn.disabled = false;
            }, 3000);
        });
    });
</script>