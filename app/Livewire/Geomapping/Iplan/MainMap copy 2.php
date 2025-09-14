<?php

namespace App\Livewire\Geomapping\Iplan;

use App\Models\Region;
use Livewire\Component;
use App\Models\Province;
use App\Models\Commodity;
use Livewire\Attributes\On;
use App\Models\GeoCommodity;
use App\Models\Intervention;
use App\Models\CommodityGroup;

use App\Events\GeoCommodityUpdated;
use App\Services\LeafletJSServices;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;


class MainMap extends Component
{
    public string $query = '';
    public float $lat = 12.8797;
    public float $lon = 121.774;
    public ?array $results = [];
    public ?object $commodities = null;
    public ?object $interventions = null;
    public ?array $provinceGeo = [];
    public ?array $temporaryGeo = [];
    public ?array $temporaryForDeletion = [];
    public ?array $selectedInterventions = [];
    public  $selectedCommodity = null;
    public ?array $selectedFilterCommoditites = [];
    public ?string $userRole = null;
    public ?string $userGroup = null;
    public ?array $provinceBoundaries = [];
    public ?array $regionBoundaries = [];
    public ?int $selectedProvinceId = null;
    public ?int $selectedRegionId = null;
    public ?array $allProvinces = [];
    public bool $isLoadingMap = true;
    public bool $isMapRendering = true;
    public bool $isSearching = false;
    public bool $isSaving = false;
    public $zoomOption = ''; // 'region' or 'province'
    public $allRegions = [];     // Set this in mount() or render()


    public function mount(): void
    {
        try {
            $user = Auth::guard('geomapping')->user();

            $this->userRole = $user->role ?? null;
            $this->userGroup = $user->group_number ?? null;

            $this->interventions = Cache::rememberForever('interventions_all', function () {
                return Intervention::orderBy('name', 'asc')->get();
            });
            $this->commodities = Cache::rememberForever('commodities_all', function () {
                return Commodity::where('is_blocked', 0)->orderBy('name', 'asc')->get();
            });

            if (intval($this->userRole) === 1) {
                $this->provinceGeo = GeoCommodity::with('commodity', 'geoInterventions.intervention')->get()->toArray();
            } else {
                $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)
                    ->where('user_id', $user->id)
                    ->whereNotIn('id', $this->temporaryForDeletion)
                    ->with('commodity', 'geoInterventions.intervention')
                    ->get()
                    ->toArray();
            }

            $this->selectedFilterCommoditites = $this->commodities->pluck('id')->toArray();

            // Fetch all provinces for dropdown (role 1)
            $this->allProvinces = Province::select('code', 'name', 'latitude', 'longitude')
                ->orderBy('name')
                ->get()
                ->toArray();
            $this->allRegions = Region::select('code', 'name', 'latitude', 'longitude')
                ->where('code', '!=', '16')
                ->orderBy('order')
                ->get()
                ->toArray();

            // Load province boundaries directly for immediate availability
            $this->loadProvinceBoundaries();
            $this->loadRegionBoundaries();
        } catch (\Exception $e) {
            Log::error('Mount error: ' . $e->getMessage());
            LivewireAlert::title('Initialization Error')->text('Unable to load map data. Please refresh the page.')->error()->toast()->position('top-end')->show();
        } finally {
            $this->isLoadingMap = false;
        }
    }

    public function placeholder()
    {

        return view('livewire.geomapping.iplan.placeholder.main-map-placeholder');
    }
    public function search(): void
    {
        if (strlen($this->query) < 3) {
            $this->results = [];
            return;
        }

        $this->isSearching = true;
        try {
            $this->results = app(LeafletJSServices::class)->searchQuery($this->query);
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            $this->results = [];
            LivewireAlert::title('Search Error')->text('Unable to search locations. Please try again.')->error()->toast()->position('top-end')->show();
        } finally {
            $this->isSearching = false;
        }
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
        $loadedProvinceGeo = $this->provinceGeo;
        $loadedTemporaryGeo = $this->temporaryGeo;

        $loadedProvinceGeo = array_values(
            array_filter($this->provinceGeo, function ($item) {
                return in_array($item['commodity_id'], $this->selectedFilterCommoditites);
            }),
        );
        $loadedTemporaryGeo = array_values(
            array_filter($this->temporaryGeo, function ($item) {
                return in_array($item['commodity_id'], $this->selectedFilterCommoditites);
            }),
        );
        $this->dispatch('provinceGeoUpdated', $loadedProvinceGeo);
        $this->dispatch('temporaryGeoUpdated', $loadedTemporaryGeo);
    }
    public function updatedZoomOption($value)
    {

        if ($value === 'region') {
            $this->selectedProvinceId = null;
        } elseif ($value === 'province') {
            $this->selectedRegionId = null;
        } else {
            $this->selectedProvinceId = null;
            $this->selectedRegionId = null;
        }
    }
    #[On('deleteTempCommodity')]
    public function deleteTempCommodity($payload)
    {
        $user = Auth::guard('geomapping')->user();
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

            if (intval($this->userRole) === 1) {
                $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)->with('commodity', 'geoInterventions.intervention')->get()->toArray();
            } else {
                $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)
                    ->where('user_id', $user->id)
                    ->whereNotIn('id', $this->temporaryForDeletion)
                    ->with('commodity', 'geoInterventions.intervention')
                    ->get()
                    ->toArray();
            }
            $this->dispatch('provinceGeoUpdated', $this->provinceGeo);
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

        if (!$commodity) {
            return;
        }

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

        $updated = false;
        foreach ($this->temporaryGeo as $index => $entry) {
            if ($entry['commodity_id'] === $this->selectedCommodity && $entry['latitude'] === $this->lat && $entry['longitude'] === $this->lon) {
                $this->temporaryGeo[$index] = $newEntry;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            $this->temporaryGeo[] = $newEntry;
        }

        $this->dispatch('temporaryGeoUpdated', $this->temporaryGeo);
        $this->dispatch('removeMarkers');
        $this->dispatch('resetDropDown');

        $this->selectedCommodity = null;
        $this->selectedInterventions = [];
    }

    public function saveUpdates()
    {
        $this->isSaving = true;

        try {
            $user = Auth::guard('geomapping')->user();

            foreach ($this->temporaryGeo as $geo) {
                $geoCommodity = GeoCommodity::create([
                    'commodity_id' => $geo['commodity_id'],
                    'latitude' => $geo['latitude'],
                    'longitude' => $geo['longitude'],
                    'province_id' => $user->province_id,
                    'user_id' => $user->id,
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
            // GeoCommodityUpdated::dispatch();
            LivewireAlert::title('Updated!')->text('The commodities entries have been updated.')->success()->toast()->position('top-end')->show();

        } catch (\Exception $e) {
            Log::error('Save updates error: ' . $e->getMessage());
            LivewireAlert::title('Save Error')->text('Unable to save updates. Please try again.')->error()->toast()->position('top-end')->show();
        } finally {
            $this->isSaving = false;
        }
    }


    public function updatedSelectedProvinceId()
    {
        if ($this->selectedProvinceId && $this->selectedProvinceId !== -1) {
            $province = Province::find($this->selectedProvinceId);
            if ($province && $this->isValidCoordinates($province->latitude, $province->longitude)) {
                // Dispatch event to zoom to province
                $this->dispatch('zoomToProvince', [
                    'lat' => (float) $province->latitude,
                    'lng' => (float) $province->longitude,
                    'name' => $province->name,
                ]);
            } else {
                // Handle case where coordinates are missing or invalid
                Log::warning('Invalid or missing coordinates for province: ' . ($province ? $province->name : 'Unknown'));
                LivewireAlert::title('Province Location Unavailable')->text('The coordinates for this province are not available.')->warning()->toast()->position('top-end')->show();
            }
        }
        // Dispatch event to update polygons
        $this->dispatch('selectedProvinceChanged', $this->selectedProvinceId);
    }

    public function updatedSelectedRegionId()
    {
        $this->selectedProvinceId = null;
        $this->allProvinces = Province::where('region_code', $this->selectedRegionId)->orderBy('name')->get()->toArray();

        if ($this->selectedRegionId && $this->selectedRegionId !== -1) {
            $region = Region::where('code',$this->selectedRegionId)->first();
            if ($region && $this->isValidCoordinates($region->latitude, $region->longitude)) {
                // Dispatch event to zoom to region
                $this->dispatch('zoomToRegion', [
                    'lat' => (float) $region->latitude,
                    'lng' => (float) $region->longitude,
                    'name' => $region->name,
                    'code' => $region->code,
                ]);
            } else {
                // Handle case where coordinates are missing or invalid
                Log::warning('Invalid or missing coordinates for region: ' . ($region ? $region->name : 'Unknown'));
                LivewireAlert::title('Region Location Unavailable')->text('The coordinates for this region are not available.')->warning()->toast()->position('top-end')->show();
            }
        }

        // Dispatch event to update region polygons
        $this->dispatch('selectedRegionChanged', $this->selectedRegionId);
        $this->dispatch('selectedProvinceChanged', $this->selectedProvinceId);
    }




    /**
     * Validate latitude and longitude coordinates
     */
    private function isValidCoordinates($lat, $lng)
    {
        return !is_null($lat) && !is_null($lng) &&
            is_numeric($lat) && is_numeric($lng) &&
            $lat >= -90 && $lat <= 90 &&
            $lng >= -180 && $lng <= 180;
    }

    public function loadProvinceBoundaries()
    {
        try {
            $user = Auth::guard('geomapping')->user();
            Log::info('Loading province boundaries for user role: ' . $this->userRole . ', user ID: ' . $user->id);

            if (intval($this->userRole) === 1) {
                // Admin: load all province boundaries with GeoJSON data initially
                // This is needed for the map to display polygons correctly
                $this->provinceBoundaries = Province::whereNotNull('boundary_geojson')
                    ->select('code', 'name', 'boundary_geojson', 'latitude', 'longitude')
                    ->get()
                    ->toArray();
                Log::info('Loaded ' . count($this->provinceBoundaries) . ' province boundaries for admin user (with GeoJSON)');
            } else {
                // Regular user: load only their province boundary with GeoJSON
                $userProvince = Province::where('id', $user->province_id)
                    ->whereNotNull('boundary_geojson')
                    ->select('code', 'name', 'boundary_geojson', 'latitude', 'longitude')
                    ->first();

                $this->provinceBoundaries = $userProvince ? [$userProvince->toArray()] : [];
                Log::info('Loaded province boundary for user province ID: ' . $user->province_id . ', found: ' . ($userProvince ? 'yes' : 'no'));
            }

            Log::info('Dispatching provinceBoundariesLoaded event with ' . count($this->provinceBoundaries) . ' boundaries');
            $this->dispatch('provinceBoundariesLoaded', $this->provinceBoundaries);
        } catch (\Exception $e) {
            Log::error('Load province boundaries error: ' . $e->getMessage());
            $this->provinceBoundaries = [];
        }
    }

    public function loadRegionBoundaries()
    {
        try {
            $user = Auth::guard('geomapping')->user();
            Log::info('Loading region boundaries for user role: ' . $this->userRole . ', user ID: ' . $user->id);

            if (intval($this->userRole) === 1) {
                // Admin: load all region boundaries with GeoJSON data initially
                // This is needed for the map to display polygons correctly
                $this->regionBoundaries = Region::whereNotNull('boundary_geojson')
                    ->select('code', 'name', 'boundary_geojson', 'latitude', 'longitude')
                    ->get()
                    ->toArray();
                Log::info('Loaded ' . count($this->regionBoundaries) . ' region boundaries for admin user (with GeoJSON)');
            } else {
                // Regular user: load only their region boundary with GeoJSON
                $userRegion = Region::where('id', $user->region_id)
                    ->whereNotNull('boundary_geojson')
                    ->select('code', 'name', 'boundary_geojson', 'latitude', 'longitude')
                    ->first();

                $this->regionBoundaries = $userRegion ? [$userRegion->toArray()] : [];
                Log::info('Loaded region boundary for user region ID: ' . $user->region_id . ', found: ' . ($userRegion ? 'yes' : 'no'));
            }

            Log::info('Dispatching regionBoundariesLoaded event with ' . count($this->regionBoundaries) . ' boundaries');
            $this->dispatch('regionBoundariesLoaded', $this->regionBoundaries);
        } catch (\Exception $e) {
            Log::error('Load region boundaries error: ' . $e->getMessage());
            $this->regionBoundaries = [];
        }
    }


    public function render()
    {
        return view('livewire.geomapping.iplan.main-map', [
            'userRole' => $this->userRole,
            'allProvinces' => $this->allProvinces,
            'selectedProvinceId' => $this->selectedProvinceId,
            'regionBoundaries' => $this->regionBoundaries,
            'selectedRegionId' => $this->selectedRegionId,
        ]);
    }
}
