<x-layouts.geomapping.iplan.app>
    <livewire:geomapping.iplan.main-map>
        @push('styles')
            <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        @endpush
        @push('scripts')
            <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
        @endpush
        @push('breadcrumbs')
            <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Dashboard
            </li>
        @endpush
</x-layouts.geomapping.iplan.app>
