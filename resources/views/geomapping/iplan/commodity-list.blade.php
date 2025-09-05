<x-layouts.investmentForum2025.app title="Manage Users">
    <section class="bg-white dark:bg-gray-900 space-y-10">
        <div class="py-8 px-4 mx-auto max-w-7xl lg:py-8">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Manage Commodity</span>
                    <div class="d-flex gap-2">
                        @if(Auth::guard('geomapping')->check() && Auth::guard('geomapping')->user()->role == '1')
                        <div class="dropdown">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="geomappingDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                Manage
                            </a>

                            <ul class="dropdown-menu" aria-labelledby="geomappingDropdown">
                                <li><a class="dropdown-item" href="{{ route('geomapping.iplan.investment.user-list') }}">Manage User</a></li>
                                <li><a class="dropdown-item" href="{{ route('geomapping.iplan.investment.intervention-list') }}">Manage Intervention</a></li>
                            </ul>
                        </div>
                        @endif

                        <a href="{{ route('geomapping.iplan.landing') }}" class="btn btn-outline-primary">
                            Go to 🗺️ Map
                        </a>

                        <livewire:geomapping.iplan.commodity-list-modal />

                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['id' => 'model-table', 'class' => 'dataTables_length select']) }}
                    </div>
                </div>
            </div>

        </div>
    </section>

    @push('modals')
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-..."
        crossorigin="anonymous"></script>

    {{-- DataTable scripts --}}
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        Livewire.on('reloadDataTable', () => {
            $('#model-table').DataTable().ajax.reload();
        })
    </script>
    @endpush
</x-layouts.investmentForum2025.app>