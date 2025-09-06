<x-layouts.geomapping.iplan.app>
    @push('breadcrumbs')
        <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">User Management
        </li>
    @endpush
    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card shadow-sm">
                <div class="card-header p-3 d-flex justify-content-end align-items-center gap-3">
                    <a href="{{ route('geomapping.iplan.export.users') }}" class="btn btn-success d-flex align-items-center">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Export CSV
                    </a>
                     <a href="{{ route('geomapping.iplan.investment.user-dashboard') }}" class="btn btn-success d-flex align-items-center">
                        <i class="bi bi-speedometer2 me-2"></i>User Dashboard
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['id' => 'model-table', 'class' => 'dataTables_length select']) }}
                    </div>
                </div>

            </div>
        </div>
    </div>


    @push('modals')
        <livewire:geomapping.iplan.user-list-modal />
    @endpush

    @push('styles')
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
        <!-- Buttons CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
            integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
        <link href="{{ asset('assets/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
        <style>
            /* Keep hover */
            table#model-table tbody tr:hover {
                background-color: #f1f1f1;
            }

            /* Remove click/focus/selected */
            table#model-table tbody tr:focus,
            table#model-table tbody tr:active,
            table#model-table tbody tr.selected {
                background-color: transparent !important;
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
        {{-- DataTable scripts --}}
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

        <script>
            Livewire.on('reloadDataTable', () => {
                $('#model-table').DataTable().ajax.reload();
            })
        </script>
    @endpush
</x-layouts.geomapping.iplan.app>
