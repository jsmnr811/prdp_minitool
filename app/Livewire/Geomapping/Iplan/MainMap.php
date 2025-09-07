<?php

namespace App\Livewire\Geomapping\Iplan;

use Livewire\Component;
use App\Models\Commodity;
use Livewire\Attributes\On;
use App\Models\GeoCommodity;
use App\Models\Intervention;
use App\Models\CommodityGroup;

use App\Services\LeafletJSServices;
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

    public function mount(): void
    {
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
            $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)->with('commodity', 'geoInterventions.intervention')->get()->toArray();
        } else {
            $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)
                ->where('user_id', $user->id)
                ->whereNotIn('id', $this->temporaryForDeletion)
                ->with('commodity')
                ->get()
                ->toArray();
        }

        $this->selectedFilterCommoditites = $this->commodities->pluck('id')->toArray();
    }

    public function search(): void
    {
        if (strlen($this->query) < 3) {
            $this->results = [];
            return;
        }
        $this->results = app(LeafletJSServices::class)->searchQuery($this->query);
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
            $this->provinceGeo = GeoCommodity::where('province_id', $user->province_id)->whereNotIn('id', $this->temporaryForDeletion)->with('commodity')->get()->toArray();
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

        LivewireAlert::title('Updated!')->text('The commodities entries have been updated.')->success()->toast()->position('top-end')->show();

    }

    public function render()
    {
        return view('livewire.geomapping.iplan.main-map');
    }
}
