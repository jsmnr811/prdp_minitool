<?php

use App\Models\Commodity;
use Livewire\Attributes\On;
use App\Models\GeoCommodity;
use App\Models\Intervention;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component {
    public string $query = '';
    public array $results = [];
    public float $lat = 12.8797;
    public float $lon = 121.774;
    public $commodities = [];
    public $interventions = [];
    public $provinceGeo = [];
    public $temporaryGeo = [];
    public $temporaryForDeletion = [];
    public $selectedInterventions = [];
    public $selectedCommodity = null;
    public $selectedFilterCommoditites = [];

    public function mount(): void
    {
        $this->commodities = Commodity::orderBy('name', 'asc')->get();
        $this->interventions = Intervention::orderBy('name', 'asc')->get();
        $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)->with('commodity', 'geoInterventions.intervention')->get()->toArray();
        $this->selectedFilterCommoditites = $this->commodities->pluck('id')->toArray();
    }

    public function search(): void
    {
        if (strlen($this->query) < 3) {
            $this->results = [];
            return;
        }

        $response = Http::withHeaders([
            'User-Agent' => 'I-REAP_BIDDING (mojicamarcallen@gmail.com)',
            'Accept-Language' => 'en',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $this->query,
            'format' => 'json',
            'addressdetails' => 1,
            'limit' => 5,
            'countrycodes' => 'ph',
            'viewbox' => '116.931885,21.321780,126.604385,4.215806',
            'bounded' => 1,
        ]);

        $data = $response->json();
        $this->results = is_array($data) ? $data : [];
    }

    public function updatedSelectedCommodity()
    {
        $this->addTempCommodity();
    }
    public function updatedselectedInterventions()
    {
        $this->addTempCommodity();
    }
    public function updatedSelectedFilterCommoditites()
    {
        // Keep only provinceGeo items with commodity_id in selectedFilterCommoditites
        $this->provinceGeo = array_values(
            array_filter($this->provinceGeo, function ($item) {
                return in_array($item['commodity_id'], $this->selectedFilterCommoditites);
            }),
        );

        // Keep only temporaryGeo items with commodity_id in selectedFilterCommoditites
        $this->temporaryGeo = array_values(
            array_filter($this->temporaryGeo, function ($item) {
                return in_array($item['commodity_id'], $this->selectedFilterCommoditites);
            }),
        );

        $this->dispatch('temporaryGeoUpdated', $this->temporaryGeo);
        $this->dispatch('provinceGeoUpdated', $this->provinceGeo);
    }

    #[On('deleteTempCommodity')]
    public function deleteTempCommodity($payload)
    {
        $id = $payload['id'] ?? null;
        if (!$id) {
            return;
        }

        if ($payload['isTemp']) {
            $this->temporaryGeo = array_values(
                array_filter($this->temporaryGeo, function ($item) use ($id) {
                    return $item['commodity']['id'] != $id;
                }),
            );
            $this->dispatch('temporaryGeoUpdated', $this->temporaryGeo);
        } else {
            array_push($this->temporaryForDeletion, $id);
            $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)->whereNotIn('id', $this->temporaryForDeletion)->with('commodity')->get()->toArray();
            $this->dispatch('provinceGeoUpdated', $this->provinceGeo);
        }
    }

    public function saveUpdates()
    {
        foreach ($this->temporaryGeo as $geo) {
            $geoCommodity = GeoCommodity::create([
                'commodity_id' => $geo['commodity_id'],
                'latitude' => $geo['latitude'],
                'longitude' => $geo['longitude'],
                'province_id' => 1,
            ]);

            if (!empty($geo['geo_interventions']) && is_array($geo['geo_interventions'])) {
                foreach ($geo['geo_interventions'] as $interventionEntry) {
                    $geoCommodity->geoInterventions()->create([
                        'intervention_id' => $interventionEntry['intervention_id'],
                    ]);
                }
            }
        }

        foreach ($this->temporaryForDeletion as $id) {
            GeoCommodity::find($id)?->delete(); // triggers deletion of related geo_interventions
        }

        $this->temporaryForDeletion = [];
        $this->temporaryGeo = [];
        $this->lat = 0;
        $this->lon = 0;

        LivewireAlert::title('Updated!')->text('The commodities entries have been updated.')->success()->toast()->position('top-end')->show();
    }

    //if auto:

    public function addTempCommodity()
    {
        $this->validate([
            'selectedCommodity' => 'required',
            'lat' => 'required',
            'lon' => 'required',
            'selectedInterventions' => 'required',
        ]);

        $commodity = Commodity::find($this->selectedCommodity);
        $interventions = Intervention::whereIn('id', $this->selectedInterventions)->get();

        if (!$commodity) {
            return; // Or throw an error if you want
        }

        // Prepare new entry data
        $newEntry = [
            'commodity_id' => $this->selectedCommodity,
            'latitude' => $this->lat,
            'longitude' => $this->lon,
            'commodity' => [
                'id' => $commodity->id,
                'name' => $commodity->name,
                'icon' => $commodity->icon,
            ],
            'geo_interventions' => $interventions
                ->map(function ($intervention) {
                    return [
                        'intervention_id' => $intervention->id,
                        'intervention' => [
                            'id' => $intervention->id,
                            'name' => $intervention->name,
                            'created_at' => $intervention->created_at,
                            'updated_at' => $intervention->updated_at,
                        ],
                    ];
                })
                ->toArray(),
        ];

        // Check if entry with same commodity and lat/lon exists
        $updated = false;
        foreach ($this->temporaryGeo as $index => $entry) {
            if ($entry['commodity_id'] === $this->selectedCommodity && $entry['latitude'] === $this->lat && $entry['longitude'] === $this->lon) {
                // Update existing entry
                $this->temporaryGeo[$index] = $newEntry;
                $updated = true;
                break;
            }
        }

        // If no existing entry found, add new
        if (!$updated) {
            $this->temporaryGeo[] = $newEntry;
        }

        $this->dispatch('temporaryGeoUpdated', $this->temporaryGeo);
        $this->dispatch('removeMarkers');
        $this->dispatch('resetDropDown');

        $this->selectedCommodity = null;
        $this->selectedInterventions = [];
    }
};
?>
<div class="row g-4" wire:ignore x-data="window.mapSearch(@js($provinceGeo), @js($temporaryGeo))" x-init="initMap()">
    <!-- Map and Location Section -->
    <div class="col-lg-9">
        <div class="card shadow-sm p-4 h-100">
            <h5 class="mb-3 fw-semibold">📍 Select Your Location</h5>
            <!-- Search Bar -->
            <!-- Relative container to anchor the floating dropdown -->
            <div class="position-relative mb-3">
                <!-- Search Input -->
                <input type="text" class="form-control form-control-lg" x-model="query" @input.debounce.500="onInput"
                    autocomplete="off" id="location-search" placeholder="Search for a city, region..."
                    aria-label="Location search">

                <!-- Floating Search Results -->
                <div x-show="open && results.length"
                    class="search-results position-absolute top-100 start-0 w-100 border rounded bg-white shadow-sm "
                    style="max-height: 200px; overflow-y: auto; z-index:9999">
                    <template x-for="(res, idx) in results" :key="idx">
                        <div @click="selectResult(res)" class="p-2 cursor-pointer border-bottom hover:bg-light"
                            :title="res.display_name" style="cursor:pointer">
                            <span x-text="res.display_name"></span>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Map Display -->
            <div id="map" class="rounded shadow-sm" style="height: 600px;"></div>
            <div class="row text-body mt-2" x-show="hasMarker">
                <div class="col-md-6">
                    <div class="d-flex align-items-center" style="font-size: 9px;">
                        <span class="fw-semibold text-dark me-2">Latitude:</span>
                        <span x-text="$wire.lat ? $wire.lat.toFixed(6) : '-'"></span>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="d-flex align-items-center" style="font-size: 9px;">
                        <span class="fw-semibold text-dark me-2">Longitude:</span>
                        <span x-text="$wire.lon ? $wire.lon.toFixed(6) : '-'"></span>
                    </div>
                </div>
            </div>
            <small class="text-muted d-block mt-3">
                Alternatively, simply click on the map to pin a precise location.
            </small>
        </div>
    </div>

    <!-- Filter and Toggle Sidebar -->
    <div class="col-lg-3 ">
        <div class="card shadow-sm p-4 h-100 ">
            <h5 class="fs-6 mb-3 fw-bold">🗺️ Toggle Map Layers</h5>
            <hr class="mt-2">

            <div class="row row-cols-1 row-cols-md-2 g-2 ">
                @foreach ($commodities as $commodity)
                    <div class="col">
                        <div class="form-check form-switch d-flex align-items-center">
                            <input class="form-check-input" type="checkbox" id="commodity-{{ $commodity->id }}"
                                wire:model.live="selectedFilterCommoditites" value="{{ $commodity->id }}">
                            <label class="form-check-label ms-2 d-flex align-items-center"
                                for="commodity-{{ $commodity->id }}">
                                <img class="marker-circle me-2" src="{{ asset('commodity-icons/' . $commodity->icon) }}"
                                    onerror="this.onerror=null;this.src='{{ asset('icons/commodity-icons/default.png') }}';"
                                    alt="{{ $commodity->name }}" width="30" height="30" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="{{ $commodity->name }}">
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@script
    <script>
        window.mapSearch = function(provinceGeo, temporaryGeo) {
            return {
                query: '',
                results: [],
                open: false,
                map: null,
                marker: null,
                hasMarker: false,
                selectedLabel: '',
                lat: 12.8797,
                lon: 121.7740,
                provinceGeo,
                temporaryGeo,
                markersProvince: [],
                markersTemporary: [],

                initMap() {
                    const philippinesBounds = L.latLngBounds(
                        L.latLng(4.215806, 116.931885), // Southwest
                        L.latLng(21.321780, 126.604385) // Northeast
                    );

                    this.map = L.map('map', {
                        maxBounds: philippinesBounds,
                        maxBoundsViscosity: 1.0,
                        minZoom: 5,
                        maxZoom: 18
                    }).setView([this.lat, this.lon], 6);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                    }).addTo(this.map);

                    this.addProvinceMarkers();
                    this.addTemporaryMarkers();

                    // Listen for Livewire updates to temporaryGeo
                    Livewire.on('temporaryGeoUpdated', (newTemporaryGeo) => {
                        console.log('Raw temporaryGeo:', newTemporaryGeo);
                        this.temporaryGeo = newTemporaryGeo.flat ? newTemporaryGeo.flat() : newTemporaryGeo;
                        this.addTemporaryMarkers();
                    });

                    Livewire.on('provinceGeoUpdated', (newProvinceGeo) => {
                        console.log('Raw provinceGeo:', newProvinceGeo);
                        this.provinceGeo = newProvinceGeo.flat ? newProvinceGeo.flat() : newProvinceGeo;
                        this.addProvinceMarkers();
                    });

                    Livewire.on('removeMarkers', () => {
                        this.lat = 0;
                        this.lon = 0;
                        this.placeMarker(0, 0);
                    });


                    // Map click for manual pin
                    this.map.on('click', (e) => {
                        const {
                            lat,
                            lng
                        } = e.latlng;
                        this.lat = lat;
                        this.lon = lng;
                        this.$wire.set('lat', lat);
                        this.$wire.set('lon', lng);
                        this.reverseGeocode(lat, lng, true);
                    });
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'))
                    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                        new bootstrap.Tooltip(tooltipTriggerEl)
                    })
                },

                addProvinceMarkers() {
                    this.markersProvince.forEach(m => this.map.removeLayer(m));
                    this.markersProvince = [];

                    this.provinceGeo.forEach(entry => {
                        if (entry.latitude && entry.longitude && entry.commodity) {
                            const icon = this.createIcon(entry.commodity.icon);
                            const marker = L.marker([entry.latitude, entry.longitude], {
                                    icon
                                })
                                .addTo(this.map)
                                .bindPopup(`
                    <div class="text-center" style="min-width: 150px;">
                        <div class="fw-bold mb-2 text-primary">
                            ${entry.commodity.name}
                        </div>
                       <div class="small text-muted my-2 text-start">
                            <ul class="mb-0 ps-3">
                            ${
                                entry.geo_interventions && entry.geo_interventions.length > 0
                                ? entry.geo_interventions.map(i => `<li>${i.intervention?.name || ''}</li>`).join('')
                                : '<li>No interventions</li>'
                            }
                        </ul>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-center">
                        <button onclick="window.deleteTempCommodity(${entry.id}, 0)"
                            class="btn btn-sm btn-outline-danger btn-icon d-flex align-items-center gap-1">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        </div>
                    </div>
                `);
                            this.markersProvince.push(marker);
                        }
                    });
                },

                addTemporaryMarkers() {
                    // Remove old temporary markers
                    this.markersTemporary.forEach(m => this.map.removeLayer(m));
                    this.markersTemporary = [];

                    // Flatten in case of nested array
                    const geoPoints = Array.isArray(this.temporaryGeo[0]) ? this.temporaryGeo.flat() : this
                        .temporaryGeo;

                    geoPoints.forEach(entry => {
                        if (entry.latitude && entry.longitude && entry.commodity) {
                            const icon = this.createIcon(entry.commodity.icon);
                            const marker = L.marker([entry.latitude, entry.longitude], {
                                    icon
                                })
                                .addTo(this.map)
                                .bindPopup(`
                    <div class="text-center" style="min-width: 150px;">
                        <div class="fw-bold mb-2 text-primary">
                            ${entry.commodity.name} (Temporary)
                        </div>
                       <div class="small text-muted my-2 text-start">
                            <ul class="mb-0 ps-3">
                                ${
                                    entry.geo_interventions && entry.geo_interventions.length > 0
                                    ? entry.geo_interventions.map(i => `<li>${i.intervention?.name || ''}</li>`).join('')
                                    : '<li>No interventions</li>'
                                }
                            </ul>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-center">
                        <button onclick="window.deleteTempCommodity(${entry.commodity.id}, 1)"
                            class="btn btn-sm btn-outline-danger btn-icon d-flex align-items-center gap-1">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                        </div>
                    </div>
                `);
                            this.markersTemporary.push(marker);
                        }
                    });
                },

                createIcon(iconPath, commodityId = null) {
                    const finalUrl = iconPath.startsWith('http') ? iconPath : `/commodity-icons/${iconPath}`;

                    return L.divIcon({
                        className: 'custom-marker-icon position-relative',
                        html: `
            <div class="marker-circle" >
                <img src="${finalUrl}" alt="Icon"
                     onerror="this.onerror=null;this.src='/icons/commodity-icons/default.png';"
                     style="width: 32px; height: 32px; border-radius: 50%;"/>
            </div>
        `,
                        iconSize: [32, 32],
                        iconAnchor: [16, 32],
                        popupAnchor: [0, -32]
                    });
                },

                onInput() {
                    if (this.query.length < 3) {
                        this.results = [];
                        this.open = this.hasMarker;
                        return;
                    }

                    this.$wire.set('query', this.query);
                    this.$wire.search().then(() => {
                        this.results = this.$wire.results;
                        this.open = this.results.length > 0 || this.hasMarker;
                    });
                },

                selectResult(res) {
                    this.lat = parseFloat(res.lat);
                    this.lon = parseFloat(res.lon);
                    this.$wire.set('lat', this.lat);
                    this.$wire.set('lon', this.lon);
                    this.query = res.display_name;
                    this.selectedLabel = res.display_name;
                    if (!this.map) {
                        console.warn('Map is not initialized yet.');
                        return;
                    }

                    this.map.setView([this.lat, this.lon], 14);
                    this.placeMarker(this.lat, this.lon, res.display_name);

                    this.open = false;
                    this.results = [];
                },

                placeMarker(lat, lon, label = '') {
                    if (lat === 0 && lon === 0) {
                        if (this.marker) {
                            this.map.removeLayer(this.marker);
                            this.marker = null;
                            this.hasMarker = false;
                        }
                        return;
                    }

                    const commodityOptions = @js($commodities).map(c =>
                        `<option value="${c.id}">${c.name}</option>`
                    ).join('');

                    const interventionOptions = @js($interventions).map(i =>
                        `<option value="${i.id}">${i.name}</option>`
                    ).join('');

                    const popupContent = `
                                <div style="min-width: 300px;">
                                    <label for="commodity-select" class="form-label fw-bold mb-1">Commodity</label>
                                    <select id="commodity-select" class="form-select  mb-3 text-start">
                                        <option value="">Select commodity</option>
                                        ${commodityOptions}
                                    </select>

                                    <label for="intervention-select" class="form-label fw-bold mb-1">Interventions</label>
                                    <select id="intervention-select" class="form-select  mb-3 text-start" multiple>
                                        ${interventionOptions}
                                    </select>

                                    <div class="d-grid mt-2">
                                        <button id="add-commodity-btn" class="btn btn-sm btn-success">Add</button>
                                    </div>
                                </div>
                            `;


                    if (this.marker) {
                        this.marker.setLatLng([lat, lon]);
                        this.marker.setPopupContent(popupContent);
                        this.marker.openPopup();
                    } else {
                        this.marker = L.marker([lat, lon], {
                                draggable: true
                            })
                            .addTo(this.map)
                            .bindPopup(popupContent);

                        this.marker.on('popupopen', (e) => {
                            const popupEl = e.popup.getElement();

                            const commoditySelect = popupEl.querySelector('#commodity-select');
                            const interventionSelect = popupEl.querySelector('#intervention-select');
                            const addBtn = popupEl.querySelector('#add-commodity-btn');

                            let selectedCommodity = null;
                            let selectedInterventions = [];

                            if (commoditySelect) {
                                $(commoditySelect).select2({
                                    dropdownParent: $(popupEl),
                                    width: '100%',
                                    placeholder: 'Select commodity'
                                });

                                $(commoditySelect).on('change', function() {
                                    selectedCommodity = this.value;
                                    console.log('[Select2] Commodity selected:', selectedCommodity);
                                });
                            }

                            if (interventionSelect) {
                                $(interventionSelect).select2({
                                    dropdownParent: $(popupEl),
                                    width: '100%',
                                    placeholder: 'Select interventions',
                                    multiple: true
                                });

                                $(interventionSelect).on('change', function() {
                                    selectedInterventions = $(this).val() || [];
                                    console.log('[Select2] Interventions selected:',
                                        selectedInterventions);
                                });
                            }

                            if (addBtn) {
                                addBtn.addEventListener('click', () => {
                                    if (!selectedCommodity || selectedInterventions.length === 0) {
                                        alert(
                                            'Please select a commodity and at least one intervention.'
                                        );
                                        return;
                                    }
                                    this.$wire.set('selectedCommodity', selectedCommodity);
                                    this.$wire.set('selectedInterventions', selectedInterventions);
                                    this.$wire.set('lat', lat);
                                    this.$wire.set('lon', lon);
                                    this.$wire.addTempCommodity();
                                    this.map.closePopup();
                                    console.log('[Livewire] Added:', {
                                        selectedCommodity,
                                        selectedInterventions,
                                        lat,
                                        lon
                                    });
                                });
                            }

                            this.$wire.set('lat', lat);
                            this.$wire.set('lon', lon);
                        });

                        this.marker.on('dragend', (e) => {
                            const newLatLng = e.target.getLatLng();
                            this.lat = newLatLng.lat;
                            this.lon = newLatLng.lng;

                            this.$wire.set('lat', this.lat);
                            this.$wire.set('lon', this.lon);

                            // Refresh the popup at new location
                            this.placeMarker(this.lat, this.lon);
                        });
                    }

                    this.hasMarker = true;
                    this.selectedLabel = label;
                },
                reverseGeocode(lat, lon, updateMap = false) {
                    fetch(
                            `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json&addressdetails=1&countrycodes=ph`
                        )
                        .then(res => res.json())
                        .then(data => {
                            const name = data.display_name || `Lat: ${lat.toFixed(5)}, Lng: ${lon.toFixed(5)}`;
                            this.query = name;
                            this.$wire.set('query', name);
                            this.selectedLabel = name;

                            this.results = [];
                            this.open = false;

                            if (updateMap) {
                                this.map.setView([lat, lon], this.map.getZoom());
                                this.placeMarker(lat, lon, name);
                            }
                        })
                        .catch(err => {
                            console.error("Reverse geocoding failed:", err);
                            const fallback = `Lat: ${lat.toFixed(5)}, Lng: ${lon.toFixed(5)}`;
                            this.query = fallback;
                            this.selectedLabel = fallback;

                            this.results = [];
                            this.open = false;

                            if (updateMap) {
                                this.placeMarker(lat, lon, fallback);
                            }
                        });
                }
            }
        }
        window.deleteTempCommodity = function(commodityId, isTemp) {
            if (!commodityId) return;
            Livewire.dispatch('deleteTempCommodity', {
                payload: {
                    id: commodityId,
                    isTemp: isTemp
                }
            });
        }
    </script>
@endscript
