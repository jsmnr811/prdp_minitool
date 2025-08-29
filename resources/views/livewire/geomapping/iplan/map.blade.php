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

    public function mount(): void
    {
        $this->commodities = Commodity::orderBy('name', 'asc')->get();
        $this->interventions = Intervention::orderBy('name', 'asc')->get();
        $this->provinceGeo = GeoCommodity::where('province_id', 1)->with('commodity', 'geoInterventions.intervention')->get()->toArray();
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

    #[On('updateSelectedCommodity')]
    public function updateSelectedCommodity($value)
    {
        if ($value) {
            $this->selectedCommodity = $value;
        }
    }

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

        if ($commodity) {
            $this->temporaryGeo[] = [
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

            $this->dispatch('temporaryGeoUpdated', $this->temporaryGeo);
            $this->dispatch('removeMarkers');
            $this->dispatch('resetDropDown');

            $this->selectedCommodity = null;
            $this->selectedInterventions = [];
        }
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
            $this->provinceGeo = GeoCommodity::where('province_id', 1)->whereNotIn('id', $this->temporaryForDeletion)->with('commodity')->get()->toArray();
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
};
?>
<section class="section-padding ">
    <div class="container">
        <h2 class="mb-10 text-center">Pin Your Farm, Optimize Your Yield</h2>
        <div class="row justify-content-center" wire:ignore x-data="window.mapSearch(@js($provinceGeo), @js($temporaryGeo))" x-init="initMap()">
            <div class="col-lg-10">
                <!-- Location Input / Map Interaction -->
                <div class="bg-white rounded-4 shadow-md p-4 mb-4">
                    <h3 class="h4 mb-3">Select Your Location</h3>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control search-input" x-model="query"
                            @input.debounce.500="onInput" autocomplete="off"
                            placeholder="Enter location (e.g., city, region) or click on the map..."
                            aria-label="Location search input">
                        <button class="btn search-button" type="button">Find</button>
                    </div>
                    <p class="text-sm text-gray-500 mb-0">
                        You can type a location above or **pin a precise spot directly on the map below.**
                    </p>
                    <div x-show="open && results.length" class="search-results border rounded bg-white mb-2"
                        style="max-height: 200px; overflow-y: auto;">
                        <template x-for="(res, idx) in results" :key="idx">
                            <div @click="selectResult(res)" class="p-2 cursor-pointer border-bottom hover:bg-light"
                                :title="res.display_name" style="cursor:pointer">
                                <span x-text="res.display_name"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Placeholder for your Map -->
                <div class="card shadow-sm rounded-4 shadow-md  mb-4">

                    <div class="card-body p-4">
                        <div wire:ignore id="map"></div>
                    </div>
                </div>
                <!-- Commodity and Interventions Selection -->
                <div x-show="hasMarker" class="bg-white rounded-lg shadow-md p-4 mb-4">
                    <h3 class="h4 mb-3">Input by Commodity & Intervention</h3>
                    <div class="mb-4 p-3 border rounded bg-light">
                        <h5 class="mb-3">📍 Location Information</h5>

                        <div class="mb-3">
                            <div class="d-flex align-items-start">
                                <span class="fw-semibold text-dark me-2" style="min-width: 90px;">Location:</span>
                                <span class="text-body text-break"
                                    x-text="$wire.query ? $wire.query : 'No location selected'"></span>
                            </div>
                        </div>

                        <div class="row text-body">
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="fw-semibold text-dark me-2" style="min-width: 90px;">Latitude:</span>
                                    <span x-text="$wire.lat ? $wire.lat.toFixed(6) : '-'"></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="d-flex align-items-center">
                                    <span class="fw-semibold text-dark me-2" style="min-width: 90px;">Longitude:</span>
                                    <span x-text="$wire.lon ? $wire.lon.toFixed(6) : '-'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="commoditySelect" class="form-label font-weight-bold">Select Commodity:</label>
                        <x-select2 name="commoditySelect" id="commoditySelect" class="search-input" :options="$commodities"
                            wireModel='selectedCommodity'>
                            @foreach ($commodities as $commodity)
                                <option value="{{ $commodity->id }}"
                                    {{ $commodity->id == $selectedCommodity ? 'selected' : '' }}>{{ $commodity->name }}
                                </option>
                            @endforeach
                        </x-select2>
                    </div>

                    <div class="mb-3">
                        <label for="interventionSelect" class="form-label font-weight-bold">Select
                            Intervention:</label>
                        <x-select2-multiple multi="true" name="interventionSelect" id="interventionSelect"
                            class="search-input" wireModel='selectedInterventions'>
                            @foreach ($interventions as $intervention)
                                <option value="{{ $intervention->id }}"
                                    {{ in_array($intervention->id, $selectedInterventions) ? 'selected' : '' }}>
                                    {{ $intervention->name }}
                                </option>
                            @endforeach
                        </x-select2-multiple>
                    </div>
                    <div class="row mb-3">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success " wire:click="addTempCommodity"><i
                                    class="bi bi-plus-circle me-2"></i>Add Entry</button>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-4 shadow-md p-4 mb-4">
                    <button class="btn search-button w-100" type="button" wire:click="saveUpdates">Save
                        Updates</button>

                    <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                        <strong>How to use this form:</strong><br>
                        - Pin or search for a location on the map to activate the input form.<br>
                        - Select the relevant agricultural commodity and one or more interventions.<br>
                        - Click <strong>"Add Entry"</strong> to add the selection to the map.<br>
                        - To remove an entry, click its pin on the map and then click the <strong>trash icon (<i
                                class="bi bi-trash-fill text-danger"></i>)</strong>.<br>
                        - <strong>Remember to click "Save Updates" to apply all changes.</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@script
    <script>
        $(document).ready(function() {
            const select = $('#commodity-dropdown');
            select.select2({
                theme: 'bootstrap-5',
                placeholder: 'Select Commodity',
                allowClear: true
            });

            // Listen to change and update Livewire manually
            select.on('change', function(e) {
                let selectedValue = $(this).val();
                Livewire.dispatch('updateSelectedCommodity', {
                    value: selectedValue
                });
            });
            Livewire.on('resetDropDown', () => {
                const select = $('#interventionSelect,#commoditySelect');
                select.val(null).trigger('change');
            });
        });

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
                },

                addProvinceMarkers() {
                    // Remove old markers
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

                    const popupContent = label || 'Pinned Location';

                    if (this.marker) {
                        this.marker.setLatLng([lat, lon]);
                        this.marker.setPopupContent(popupContent);
                        this.marker.openPopup(); // safe here if already added
                    } else {
                        this.marker = L.marker([lat, lon], {
                                draggable: true
                            })
                            .addTo(this.map)
                            .bindPopup(popupContent);

                        // ✅ Fix: safely open popup only after marker is added
                        this.marker.on('add', () => {
                            this.marker.openPopup();
                        });

                        this.marker.on('dragend', (e) => {
                            const newLatLng = e.target.getLatLng();
                            this.lat = newLatLng.lat;
                            this.lon = newLatLng.lng;
                            this.$wire.set('lat', this.lat);
                            this.$wire.set('lon', this.lon);
                            this.reverseGeocode(this.lat, this.lon, true);
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
