<x-layouts.geomapping.iplan.app>
    @push('breadcrumbs')
    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">
        Analytics Dashboard
    </li>
    @endpush

    <div class="row g-4">
        {{-- Filters --}}
        <div class="col-12">
            <div class="card shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom p-3">
                    <form method="GET" action="{{ route('geomapping.iplan.investment.analytics-dashboard') }}"
                        class="d-flex flex-nowrap align-items-center gap-3 overflow-x-auto p-2">

                        {{-- Region --}}
                        <select name="region_select" class="form-select form-select-sm w-auto">
                            <option value="all">All Regions</option>
                            @foreach (\App\Models\Region::orderBy('name')->get() as $regionOption)
                            <option value="{{ $regionOption->code }}"
                                {{ request('region_select') == $regionOption->code ? 'selected' : '' }}>
                                {{ $regionOption->abbr }}
                            </option>
                            @endforeach
                        </select>

                        {{-- Province --}}
                        <select name="province_select" class="form-select form-select-sm w-auto">
                            <option value="all">All Provinces</option>
                            @foreach (\App\Models\Province::where('region_code', '!=', 16)->whereHas('geoCommodities')->orderBy('name')->get() as $provinceOption)
                            <option value="{{ $provinceOption->code }}"
                                {{ request('province_select') == $provinceOption->code ? 'selected' : '' }}>
                                {{ $provinceOption->abbr }}
                            </option>
                            @endforeach
                        </select>

                        {{-- Commodity --}}
                        <select name="commodity_select" class="form-select form-select-sm w-auto">
                            <option value="all">All Commodities</option>
                            @foreach (\App\Models\Commodity::orderBy('name')->get() as $commodityOption)
                            <option value="{{ $commodityOption->id }}"
                                {{ request('commodity_select') == $commodityOption->id ? 'selected' : '' }}>
                                {{ $commodityOption->name }}
                            </option>
                            @endforeach
                        </select>

                        {{-- Intervention --}}
                        <select name="intervention_select" class="form-select form-select-sm w-auto">
                            <option value="all">All Interventions</option>
                            @foreach (\App\Models\Intervention::orderBy('name')->get() as $interventionOption)
                            <option value="{{ $interventionOption->id }}"
                                {{ request('intervention_select') == $interventionOption->id ? 'selected' : '' }}>
                                {{ $interventionOption->name }}
                            </option>
                            @endforeach
                        </select>

                        {{-- Funded Status --}}
                        <div class="d-flex gap-3 align-items-center">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="funded_status[]" value="funded"
                                    id="fundedCheck"
                                    {{ is_array(request('funded_status')) && in_array('funded', request('funded_status')) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="fundedCheck">Funded</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" name="funded_status[]" value="unfunded"
                                    id="unfundedCheck"
                                    {{ is_array(request('funded_status')) && in_array('unfunded', request('funded_status')) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="unfundedCheck">Unfunded</label>
                            </div>
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                            <a href="{{ route('geomapping.iplan.investment.analytics-dashboard') }}"
                                class="btn btn-light btn-sm">Reset</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        {{-- Table + Maps --}}
        <div class="col-12">
            <div class="row g-4 align-items-stretch">
                {{-- Table --}}
                <div class="col-lg-9 col-12 d-flex">
                    <div class="card shadow-sm rounded-3 flex-grow-1">
                        <div class="card-body p-3 d-flex flex-column h-100">
                            <div class="table-responsive flex-grow-1 overflow-auto">
                                <table class="table table-hover table-striped align-middle fs-7 mb-0">
                                    <thead class="sticky-top bg-white shadow-sm">
                                        <tr class="text-uppercase text-muted small">
                                            <th>Region</th>
                                            <th>Province</th>
                                            <th>Commodity</th>
                                            <th>Intervention</th>
                                            <th class="text-end">Fund Requirement</th>
                                            <th class="text-end">Funded</th>
                                            <th class="text-end">Unfunded</th>
                                        </tr>
                                        <tr class="fw-bold bg-light">
                                            <td colspan="4" class="text-end">TOTAL:</td>
                                            <td class="text-end text-primary">{{ number_format($totalFundRequirement, 2) }}</td>
                                            <td class="text-end text-success">{{ number_format($totalFunded, 2) }}</td>
                                            <td class="text-end text-danger">{{ number_format($totalUnfunded, 2) }}</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($provinces as $province)
                                        @php $matrixCount = $province->pcipMatrices->count(); @endphp
                                        @forelse ($province->pcipMatrices as $mIndex => $matrix)
                                        <tr>
                                            @if ($mIndex === 0)
                                            <td rowspan="{{ $matrixCount ?: 1 }}" class="align-middle fw-semibold bg-light">
                                                {{ $province->region->abbr ?? 'N/A' }}
                                            </td>
                                            <td rowspan="{{ $matrixCount ?: 1 }}" class="align-middle fw-semibold text-primary bg-light">
                                                {{ $province->abbr }}
                                            </td>
                                            @endif
                                            <td>{{ $matrix->commodity->name ?? '—' }}</td>
                                            <td>{{ $matrix->intervention->name ?? '—' }}</td>
                                            <td class="text-end">{{ number_format($matrix->funding_requirement, 2) }}</td>
                                            <td class="text-end">
                                                @if ($matrix->funded > 0)
                                                <span class="badge bg-success">{{ number_format($matrix->funded, 2) }}</span>
                                                @else
                                                <span class="text-muted">0.00</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if ($matrix->unfunded > 0)
                                                <span class="badge bg-danger">{{ number_format($matrix->unfunded, 2) }}</span>
                                                @else
                                                <span class="text-muted">0.00</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td class="align-middle fw-semibold bg-light">{{ $province->region->abbr ?? 'N/A' }}</td>
                                            <td class="align-middle fw-semibold text-primary bg-light">{{ $province->abbr }}</td>
                                            <td colspan="5" class="text-center text-muted">No interventions</td>
                                        </tr>
                                        @endforelse
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Maps --}}
                <div class="col-lg-3 col-12 d-flex flex-column">
                    <div class="card shadow-sm rounded-3 mb-3 p-2 flex-fill">
                        <div class="text-center fw-bold mb-1">Funded</div>
                        <div id="map-funded" class="w-100 h-100" style="min-height:250px; border-radius:0.5rem;"></div>
                    </div>

                    <div class="card shadow-sm rounded-3 p-2 flex-fill">
                        <div class="text-center fw-bold mb-1">Unfunded</div>
                        <div id="map-unfunded" class="w-100 h-100" style="min-height:250px; border-radius:0.5rem;"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    @endpush

    @php
    $geoCommoditiesFundedJson = isset($geoCommoditiesFunded) ? json_encode($geoCommoditiesFunded) : '[]';
    $geoCommoditiesUnfundedJson = isset($geoCommoditiesUnfunded) ? json_encode($geoCommoditiesUnfunded) : '[]';
    @endphp

    @push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        const mapOptions = {
            minZoom: 5,
            maxZoom: 10,
            maxBounds: [
                [4.5, 116.0],
                [21.0, 127.0]
            ],
            maxBoundsViscosity: 1.0,
            attributionControl: false
        };

        function addMarkersAndFitBounds(mapId, geoData, type) {
            const map = L.map(mapId, mapOptions);
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                subdomains: 'abcd',
                maxZoom: 19
            }).addTo(map);

            const markers = [];
            geoData.forEach(item => {
                const iconUrl = "{{ asset('icons') }}/" + item.icon;
                const commodityIcon = L.icon({
                    iconUrl,
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });
                const marker = L.marker(
                        [parseFloat(item.latitude), parseFloat(item.longitude)], {
                            icon: commodityIcon
                        }
                    )
                    .addTo(map)
                    .bindPopup(
                        `<b>${item.commodity}</b> (${item.province})<br>
                 Intervention: ${item.intervention}<br>
                 ${type}: ${type === 'Funded' ? item.funded : item.unfunded}`
                    );
                markers.push(marker);
            });

            if (markers.length > 0) {
                const group = L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            } else {
                map.setView([12.8797, 121.7740], 5);
            }

            return map;
        }

        const fundedData = {!!$geoCommoditiesFundedJson!!};
        const unfundedData = {!!$geoCommoditiesUnfundedJson!!};

        addMarkersAndFitBounds('map-funded', fundedData, 'Funded');
        addMarkersAndFitBounds('map-unfunded', unfundedData, 'Unfunded');
    </script>
    @endpush

</x-layouts.geomapping.iplan.app>