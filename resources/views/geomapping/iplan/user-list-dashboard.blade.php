<x-layouts.geomapping.iplan.app>
    @push('breadcrumbs')
        <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">User Dashboard
        </li>
    @endpush
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header p-3 d-flex justify-content-end align-items-center gap-3">
                    <button  id="exportExcel"  class="btn btn-success d-flex align-items-center">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export CSV
                    </button>

                </div>
                <div class="card-body">
                    <livewire:geomapping.iplan.user-dashboard-header lazy>
                        <div class="table-responsive">
                            <table
                                class="table align-middle table-row-dashed fs-7 mb-0 dataTable no-footer align-center"
                                id="model-table">
                                <thead>
                                    <tr>
                                        <th rowspan=2>Region</th>
                                        <th rowspan=2>Province</th>
                                        <th colspan="6">Registered LGU Participants</th>
                                        <th colspan="6">Verified LGU Participants</th>
                                    </tr>
                                    <tr>
                                        <th class="bg-primary">Provincial Governor</th>
                                        <th class="bg-primary">SP Committee on Agriculture</th>
                                        <th class="bg-primary">PPDO</th>
                                        <th class="bg-primary">Provincial Agriculturist</th>
                                        <th class="bg-primary">Provincial Veterenarian</th>
                                        <th class="bg-primary">PPMIU Head</th>
                                        <th class="bg-success">Provincial Governor</th>
                                        <th class="bg-success">SP Committee on Agriculture</th>
                                        <th class="bg-success">PPDO</th>
                                        <th class="bg-success">Provincial Agriculturist</th>
                                        <th class="bg-success">Provincial Veterenarian</th>
                                        <th class="bg-success">PPMIU Head</th>
                                    </tr>
                                </thead>

                                {{-- Overall Total Row (Sticky) --}}
                                @php
                                    $overallTotals = [
                                        'governor_reg' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount('Provincial Local Government Units', 'Governor'),
                                            ),
                                        ),
                                        'sp_committee_reg' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'SP Committee on Agriculture',
                                                ),
                                            ),
                                        ),
                                        'ppdo_reg' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount('Provincial Local Government Units', 'PPDO'),
                                            ),
                                        ),
                                        'agriculturist_reg' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'Provincial Agriculturist',
                                                ),
                                            ),
                                        ),
                                        'veterenarian_reg' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'Provincial Veterenarian',
                                                ),
                                            ),
                                        ),
                                        'ppmiu_reg' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'PPMIU Head',
                                                ),
                                            ),
                                        ),
                                        'governor_ver' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'Governor',
                                                    true,
                                                ),
                                            ),
                                        ),
                                        'sp_committee_ver' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'SP Committee on Agriculture',
                                                    true,
                                                ),
                                            ),
                                        ),
                                        'ppdo_ver' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'PPDO',
                                                    true,
                                                ),
                                            ),
                                        ),
                                        'agriculturist_ver' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'Provincial Agriculturist',
                                                    true,
                                                ),
                                            ),
                                        ),
                                        'veterenarian_ver' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'Provincial Veterenarian',
                                                    true,
                                                ),
                                            ),
                                        ),
                                        'ppmiu_ver' => $regions->sum(
                                            fn($r) => $r->provinces->sum(
                                                fn($p) => $p->paxCount(
                                                    'Provincial Local Government Units',
                                                    'PPMIU Head',
                                                    true,
                                                ),
                                            ),
                                        ),
                                    ];
                                @endphp

                                <thead class="sticky-total">
                                    <tr class="table-info fw-bold">
                                        <td class="text-center"><strong>OVERALL TOTAL</strong></td>
                                        <td class="text-center"><strong>ALL PROVINCES</strong></td>
                                        <td class="text-center bg-primary-light">
                                            <strong>{{ $overallTotals['governor_reg'] }}</strong>
                                        </td>
                                        <td class="text-center bg-primary-light">
                                            <strong>{{ $overallTotals['sp_committee_reg'] }}</strong>
                                        </td>
                                        <td class="text-center bg-primary-light">
                                            <strong>{{ $overallTotals['ppdo_reg'] }}</strong>
                                        </td>
                                        <td class="text-center bg-primary-light">
                                            <strong>{{ $overallTotals['agriculturist_reg'] }}</strong>
                                        </td>
                                        <td class="text-center bg-primary-light">
                                            <strong>{{ $overallTotals['veterenarian_reg'] }}</strong>
                                        </td>
                                        <td class="text-center bg-primary-light">
                                            <strong>{{ $overallTotals['ppmiu_reg'] }}</strong>
                                        </td>
                                        <td class="text-center bg-success-light">
                                            <strong>{{ $overallTotals['governor_ver'] }}</strong>
                                        </td>
                                        <td class="text-center bg-success-light">
                                            <strong>{{ $overallTotals['sp_committee_ver'] }}</strong>
                                        </td>
                                        <td class="text-center bg-success-light">
                                            <strong>{{ $overallTotals['ppdo_ver'] }}</strong>
                                        </td>
                                        <td class="text-center bg-success-light">
                                            <strong>{{ $overallTotals['agriculturist_ver'] }}</strong>
                                        </td>
                                        <td class="text-center bg-success-light">
                                            <strong>{{ $overallTotals['veterenarian_ver'] }}</strong>
                                        </td>
                                        <td class="text-center bg-success-light">
                                            <strong>{{ $overallTotals['ppmiu_ver'] }}</strong>
                                        </td>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($regions as $region)
                                        @php
                                            $regionProvinces = $region->provinces;
                                            $provinceCount = $regionProvinces->count();

                                            // Calculate region totals
                                            $regionTotals = [
                                                'governor_reg' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'Governor',
                                                    ),
                                                ),
                                                'sp_committee_reg' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'SP Committee on Agriculture',
                                                    ),
                                                ),
                                                'ppdo_reg' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount('Provincial Local Government Units', 'PPDO'),
                                                ),
                                                'agriculturist_reg' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'Provincial Agriculturist',
                                                    ),
                                                ),
                                                'veterenarian_reg' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'Provincial Veterenarian',
                                                    ),
                                                ),
                                                'ppmiu_reg' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'PPMIU Head',
                                                    ),
                                                ),
                                                'governor_ver' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'Governor',
                                                        true,
                                                    ),
                                                ),
                                                'sp_committee_ver' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'SP Committee on Agriculture',
                                                        true,
                                                    ),
                                                ),
                                                'ppdo_ver' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'PPDO',
                                                        true,
                                                    ),
                                                ),
                                                'agriculturist_ver' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'Provincial Agriculturist',
                                                        true,
                                                    ),
                                                ),
                                                'veterenarian_ver' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'Provincial Veterenarian',
                                                        true,
                                                    ),
                                                ),
                                                'ppmiu_ver' => $regionProvinces->sum(
                                                    fn($p) => $p->paxCount(
                                                        'Provincial Local Government Units',
                                                        'PPMIU Head',
                                                        true,
                                                    ),
                                                ),
                                            ];
                                        @endphp

                                        @forelse($regionProvinces as $index => $province)
                                            <tr>
                                                @if ($index === 0)
                                                    <td rowspan="{{ $provinceCount + 1 }}"
                                                        class="align-middle fw-bold bg-light">
                                                        {{ $region->name }}
                                                    </td>
                                                @endif
                                                <td>{{ $province->name }}</td>
                                                <td class="bg-primary-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'Governor') }}
                                                </td>
                                                <td class="bg-primary-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'SP Committee on Agriculture') }}
                                                </td>
                                                <td class="bg-primary-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'PPDO') }}
                                                </td>
                                                <td class="bg-primary-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'Provincial Agriculturist') }}
                                                </td>
                                                <td class="bg-primary-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'Provincial Veterenarian') }}
                                                </td>
                                                <td class="bg-primary-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'PPMIU Head') }}
                                                </td>
                                                <td class="bg-success-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'Governor', true) }}
                                                </td>
                                                <td class="bg-success-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'SP Committee on Agriculture', true) }}
                                                </td>
                                                <td class="bg-success-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'PPDO', true) }}
                                                </td>
                                                <td class="bg-success-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'Provincial Agriculturist', true) }}
                                                </td>
                                                <td class="bg-success-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'Provincial Veterenarian', true) }}
                                                </td>
                                                <td class="bg-success-light">
                                                    {{ $province->paxCount('Provincial Local Government Units', 'PPMIU Head', true) }}
                                                </td>
                                            </tr>
                                        @empty
                                        @endforelse

                                        {{-- Region Total Row --}}
                                        @if ($regionProvinces->count() > 0)
                                            <tr class="table-warning fw-bold">
                                                <td class="text-end"><strong>{{ $region->name }} TOTAL:</strong></td>
                                                <td class="text-center bg-primary-light">
                                                    <strong>{{ $regionTotals['governor_reg'] }}</strong>
                                                </td>
                                                <td class="text-center bg-primary-light">
                                                    <strong>{{ $regionTotals['sp_committee_reg'] }}</strong>
                                                </td>
                                                <td class="text-center bg-primary-light">
                                                    <strong>{{ $regionTotals['ppdo_reg'] }}</strong>
                                                </td>
                                                <td class="text-center bg-primary-light">
                                                    <strong>{{ $regionTotals['agriculturist_reg'] }}</strong>
                                                </td>
                                                <td class="text-center bg-primary-light">
                                                    <strong>{{ $regionTotals['veterenarian_reg'] }}</strong>
                                                </td>
                                                <td class="text-center bg-primary-light">
                                                    <strong>{{ $regionTotals['ppmiu_reg'] }}</strong>
                                                </td>
                                                <td class="text-center bg-success-light">
                                                    <strong>{{ $regionTotals['governor_ver'] }}</strong>
                                                </td>
                                                <td class="text-center bg-success-light">
                                                    <strong>{{ $regionTotals['sp_committee_ver'] }}</strong>
                                                </td>
                                                <td class="text-center bg-success-light">
                                                    <strong>{{ $regionTotals['ppdo_ver'] }}</strong>
                                                </td>
                                                <td class="text-center bg-success-light">
                                                    <strong>{{ $regionTotals['agriculturist_ver'] }}</strong>
                                                </td>
                                                <td class="text-center bg-success-light">
                                                    <strong>{{ $regionTotals['veterenarian_ver'] }}</strong>
                                                </td>
                                                <td class="text-center bg-success-light">
                                                    <strong>{{ $regionTotals['ppmiu_ver'] }}</strong>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>

                        </div>
                </div>
            </div>

        </div>
    </div>

    @push('modals')
    @endpush

    @push('styles')
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
        <!-- Buttons CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" />
        <link href="{{ asset('assets/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
        <style>
            /* Table styling with borders */
            table#model-table {
                border-collapse: collapse;
                border: 2px solid #dee2e6;
            }

            table#model-table th,
            table#model-table td {
                border: 1px solid #dee2e6;
                padding: 8px 12px;
                text-align: center;
                vertical-align: middle;
            }

            /* Sticky header styling */
            .table-responsive {
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
            }

            table#model-table thead {
                position: sticky;
                top: 0;
                z-index: 10;
                background: white;
            }

            .sticky-total {
                position: sticky;
                top: 60px;
                /* Adjust based on header height */
                z-index: 9;
                background: white;
            }

            /* Custom background colors */
            .bg-primary-light {
                background-color: rgba(13, 110, 253, 0.1) !important;
            }

            .bg-success-light {
                background-color: rgba(25, 135, 84, 0.1) !important;
            }

            /* Header colors */
            .bg-primary {
                background-color: #0d6efd !important;
                color: white !important;
            }

            .bg-success {
                background-color: #198754 !important;
                color: white !important;
            }

            /* Keep hover */
            table#model-table tbody tr:hover {
                background-color: #f8f9fa !important;
            }

            table#model-table tbody tr:hover .bg-primary-light {
                background-color: rgba(13, 110, 253, 0.2) !important;
            }

            table#model-table tbody tr:hover .bg-success-light {
                background-color: rgba(25, 135, 84, 0.2) !important;
            }

            /* Remove click/focus/selected */
            table#model-table tbody tr:focus,
            table#model-table tbody tr:active,
            table#model-table tbody tr.selected {
                background-color: transparent !important;
            }

            /* Region cell styling */
            table#model-table .align-middle.fw-bold.bg-light {
                background-color: #f8f9fa !important;
                font-weight: bold !important;
                border-right: 2px solid #dee2e6 !important;
            }

            /* Total row styling */
            table#model-table .table-warning {
                background-color: #fff3cd !important;
                border-top: 2px solid #ffc107 !important;
                border-bottom: 2px solid #ffc107 !important;
            }

            /* Overall total row styling */
            table#model-table .table-info {
                background-color: #cff4fc !important;
                border: 2px solid #0dcaf0 !important;
            }

            /* Style only the Show entries dropdown */
            .dataTables_length select {
                width: auto !important;
                display: inline-block;
                padding: 0.375rem 1.75rem 0.375rem 0.75rem;
                font-size: 1rem;
                line-height: 1.5;
                border-radius: 0.375rem;
                border: 1px solid #ced4da;
                background: #fff url("data:image/svg+xml;charset=UTF8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 4 5'%3e%3cpath fill='none' stroke='%236c757d' stroke-width='.5' d='M2 0L0 2h4zm0 5L0 3h4z'/%3e%3c/svg%3e") no-repeat right 0.75rem center/8px 10px;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                color: #212529;
            }
        </style>
    @endpush

    @push('scripts')
        <!-- jQuery first -->
        <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

        <script src="{{ asset('assets/datatables/datatables.bundle.js') }}"></script>

        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

        <!-- Buttons JS -->
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

        <!-- SheetJS for Excel export -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
        {{-- DataTable scripts --}}

        <script>
            document.getElementById('exportExcel').addEventListener('click', function() {
                const table = document.getElementById('model-table');
                const wb = XLSX.utils.book_new();

                // Create worksheet data array
                const wsData = [];

                // Add headers
                wsData.push([
                    'Region', 'Province',
                    'Provincial Governor (Reg)', 'SP Committee Chairperson (Reg)', 'PPDC (Reg)',
                    'Provincial Agriculturist (Reg)', 'Provincial Veterinarian (Reg)', 'PPMIU (Reg)',
                    'Provincial Governor (Ver)', 'SP Committee Chairperson (Ver)', 'PPDC (Ver)',
                    'Provincial Agriculturist (Ver)', 'Provincial Veterinarian (Ver)', 'PPMIU (Ver)'
                ]);

                // Add overall total row from sticky header
                const stickyTotal = table.querySelector('.sticky-total tr');
                if (stickyTotal) {
                    const totalCells = stickyTotal.querySelectorAll('td');
                    const overallTotalRow = [];
                    totalCells.forEach(cell => {
                        overallTotalRow.push(cell.textContent.trim());
                    });
                    wsData.push(overallTotalRow);
                }

                // Process table rows
                const tbody = table.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');
                let currentRegion = '';

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    if (cells.length > 0) {
                        const rowData = [];

                        // Check if this is a total row
                        if (row.classList.contains('table-warning')) {
                            // Total row - use current region name
                            rowData.push(currentRegion + ' TOTAL');
                            // Skip the first cell (region total label) and get the data cells
                            for (let i = 1; i < cells.length; i++) {
                                rowData.push(cells[i].textContent.trim());
                            }
                        } else {
                            // Regular province row
                            let regionName = '';
                            let provinceName = '';
                            let dataStartIndex = 0;

                            // Check if first cell has rowspan (region cell)
                            if (cells[0].hasAttribute('rowspan')) {
                                // This is the first province in a region
                                regionName = cells[0].textContent.trim();
                                currentRegion = regionName;
                                provinceName = cells[1].textContent.trim();
                                dataStartIndex = 2;
                            } else {
                                // This is a subsequent province in the same region
                                regionName = currentRegion;
                                provinceName = cells[0].textContent.trim();
                                dataStartIndex = 1;
                            }

                            // Build the row data
                            rowData.push(regionName);
                            rowData.push(provinceName);

                            // Add the data cells
                            for (let i = dataStartIndex; i < cells.length; i++) {
                                rowData.push(cells[i].textContent.trim());
                            }
                        }

                        wsData.push(rowData);
                    }
                });

                // Create worksheet
                const ws = XLSX.utils.aoa_to_sheet(wsData);

                // Set column widths
                ws['!cols'] = [{
                        wch: 20
                    }, // Region
                    {
                        wch: 25
                    }, // Province
                    {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, // Registered columns
                    {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    }, {
                        wch: 15
                    } // Verified columns
                ];

                // Apply styling to header row
                const headerStyle = {
                    font: {
                        bold: true,
                        color: {
                            rgb: "FFFFFF"
                        }
                    },
                    fill: {
                        fgColor: {
                            rgb: "366092"
                        }
                    },
                    alignment: {
                        horizontal: "center",
                        vertical: "center"
                    }
                };

                // Apply header styling
                for (let col = 0; col < 14; col++) {
                    const cellRef = XLSX.utils.encode_cell({
                        r: 0,
                        c: col
                    });
                    if (!ws[cellRef]) ws[cellRef] = {
                        v: ""
                    };
                    ws[cellRef].s = headerStyle;
                }

                // Apply styling to total rows
                wsData.forEach((row, rowIndex) => {
                    if (row[1] && row[1].includes('TOTAL')) {
                        for (let col = 0; col < row.length; col++) {
                            const cellRef = XLSX.utils.encode_cell({
                                r: rowIndex,
                                c: col
                            });
                            if (!ws[cellRef]) ws[cellRef] = {
                                v: ""
                            };
                            ws[cellRef].s = {
                                font: {
                                    bold: true
                                },
                                fill: {
                                    fgColor: {
                                        rgb: "FFF3CD"
                                    }
                                },
                                alignment: {
                                    horizontal: "center"
                                }
                            };
                        }
                    }
                });

                // Add worksheet to workbook
                XLSX.utils.book_append_sheet(wb, ws, "User Dashboard");

                // Generate filename with current date
                const now = new Date();
                const filename =
                    `User_Dashboard_${now.getFullYear()}-${(now.getMonth()+1).toString().padStart(2,'0')}-${now.getDate().toString().padStart(2,'0')}.xlsx`;

                // Save file
                XLSX.writeFile(wb, filename);
            });
        </script>
    @endpush
</x-layouts.geomapping.iplan.app>
