<x-layouts.investmentForum2025.app title="Manage Users">
    <section class="bg-white dark:bg-gray-900  space-y-10 ">
        <div class="py-8 px-4 mx-auto max-w-7xl lg:py-8">

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Manage Users</span>
                    <a href="{{ route('geomapping.iplan.landing') }}" class="btn btn-outline-primary">Go to 🗺️ Map</a>
                </div>
                <div class="card-body">
                    {{ $dataTable->table() }}
                </div>
            </div>

        </div>
    </section>
    @push('modals')
    <livewire:geomapping.iplan.user-list-modal />
    @endpush
    @push('scripts')
    <script src="{{ asset('assets/datatables/datatables.bundle.js') }}"></script>
    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
    <script>
        Livewire.on('reloadDataTable', () => {
            $('#model-table').DataTable().ajax.reload();
        })
    </script>
    @endpush
    @push('styles')
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
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">
    <link href="{{ asset('assets/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>

    @endpush
</x-layouts.investmentForum2025.app>