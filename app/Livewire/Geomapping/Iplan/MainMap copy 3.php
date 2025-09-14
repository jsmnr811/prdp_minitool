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

            $this->loadProvinceGeoData($user);
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

    /**
     * Load province geo data based on user role
     */
    private function loadProvinceGeoData($user): void
    {
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
    }

    /**
     * Filter geo data by selected commodity IDs
     */
    private function filterGeoDataByCommodities(array $geoData, array $commodityIds): array
    {
        return array_values(
            array_filter($geoData, function ($item) use ($commodityIds) {
                return in_array($item['commodity_id'], $commodityIds);
            }),
        );
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
        $loadedProvinceGeo = $this->filterGeoDataByCommodities($this->provinceGeo, $this->selectedFilterCommoditites);
        $loadedTemporaryGeo = $this->filterGeoDataByCommodities($this->temporaryGeo, $this->selectedFilterCommoditites);
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
            $this->loadProvinceGeoData($user);
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
        $this->dispatch('savingStarted');

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
            LivewireAlert::title('Save Error')->text($e->getMessage())->error()->toast()->position('top-end')->show();
        } finally {
            $this->dispatch('savingEnded');
        }
    }


    /**
     * Handle zoom to location with validation
     */
    private function handleZoomToLocation($model, $id, $eventName, $extraData = [])
    {
        if ($id && $id !== -1) {
            $location = $model::where('code', $id)->first();
            if ($location && $this->isValidCoordinates($location->latitude, $location->longitude)) {
                $this->dispatch($eventName, array_merge([
                    'lat' => (float) $location->latitude,
                    'lng' => (float) $location->longitude,
                    'name' => $location->name,
                ], $extraData));
            } else {
                $type = strtolower(class_basename($model));
                Log::warning("Invalid or missing coordinates for {$type}: " . ($location ? $location->name : 'Unknown'));
                LivewireAlert::title(ucfirst($type) . ' Location Unavailable')
                    ->text("The coordinates for this {$type} are not available.")
                    ->warning()->toast()->position('top-end')->show();
                // Reset loading state if invalid

            }
        } else {
            // If id is null or -1, reset loading states
            $type = strtolower(class_basename($model));

        }
    }

    public function updatedSelectedProvinceId()
    {
        $this->handleZoomToLocation(Province::class, $this->selectedProvinceId, 'zoomToProvince');
        $this->dispatch('selectedProvinceChanged', $this->selectedProvinceId);
    }

    public function updatedSelectedRegionId()
    {
        $this->selectedProvinceId = null;

        if ($this->selectedRegionId === null || $this->selectedRegionId === -1 || $this->selectedRegionId === '') {
            // Reset to default view - show all provinces and reset map
            $this->allProvinces = Province::select('code', 'name', 'latitude', 'longitude')
                ->orderBy('name')
                ->get()
                ->toArray();

            $this->dispatch('resetMapView');
        } else {
            // Zoom to selected region
            $this->allProvinces = Province::where('region_code', $this->selectedRegionId)->orderBy('name')->get()->toArray();
            $this->handleZoomToLocation(Region::class, $this->selectedRegionId, 'zoomToRegion', ['code' => $this->selectedRegionId]);
        }

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

    /**
     * Generic method to load boundaries for provinces or regions
     */
    private function loadBoundaries($model, $property, $eventName, $userIdField = null)
    {
        try {
            $user = Auth::guard('geomapping')->user();
            $modelName = strtolower(class_basename($model));
            Log::info("Loading {$modelName} boundaries for user role: {$this->userRole}, user ID: {$user->id}");

            if (intval($this->userRole) === 1) {
                // Admin: load all boundaries with GeoJSON data
                $this->$property = $model::whereNotNull('boundary_geojson')
                    ->select('code', 'name', 'boundary_geojson', 'latitude', 'longitude')
                    ->get()
                    ->toArray();
                Log::info('Loaded ' . count($this->$property) . " {$modelName} boundaries for admin user (with GeoJSON)");
            } else {
                // Regular user: load only their boundary with GeoJSON
                $userBoundary = $model::where('id', $user->$userIdField)
                    ->whereNotNull('boundary_geojson')
                    ->select('code', 'name', 'boundary_geojson', 'latitude', 'longitude')
                    ->first();

                $this->$property = $userBoundary ? [$userBoundary->toArray()] : [];
                Log::info("Loaded {$modelName} boundary for user {$userIdField} ID: {$user->$userIdField}, found: " . ($userBoundary ? 'yes' : 'no'));
            }

            Log::info("Dispatching {$eventName} event with " . count($this->$property) . ' boundaries');
            $this->dispatch($eventName, $this->$property);
        } catch (\Exception $e) {
            Log::error("Load {$modelName} boundaries error: " . $e->getMessage());
            $this->$property = [];
        }
    }

    public function loadProvinceBoundaries()
    {
        $this->loadBoundaries(Province::class, 'provinceBoundaries', 'provinceBoundariesLoaded', 'province_id');
    }

    public function loadRegionBoundaries()
    {
        $this->loadBoundaries(Region::class, 'regionBoundaries', 'regionBoundariesLoaded', 'region_id');
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
