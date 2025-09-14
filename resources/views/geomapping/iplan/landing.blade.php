<x-layouts.geomapping.iplan.app>
    <livewire:geomapping.iplan.main-map lazy>
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
            <style>
    @media (max-width: 576px) {
        #map {
            height: 40vh !important;
            min-height: 250px !important;
            max-height: 400px !important;
        }

        .form-select {
            font-size: 16px;
            /* Prevent zoom on iOS */
        }

        .btn {
            min-height: 44px;
            /* Better touch targets */
        }
    }

    @media (min-width: 577px) and (max-width: 768px) {
        #map {
            height: 45vh !important;
            min-height: 300px !important;
            max-height: 500px !important;
        }
    }
</style>
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
        @endpush
        @push('breadcrumbs')
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Dashboard
            </li>
        @endpush
</x-layouts.geomapping.iplan.app>
