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

    public function updatedSelectedCommodity($value)
    {
        $this->addTempCommodity();
    }
    public function updatedselectedInterventions()
    {
        $this->addTempCommodity();
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

<div style="width:200px;">
    <div class="mb-2">
        <label for="commoditySelect" class="form-label fw-bold"> Commodity</label>
            <select id="commoditySelect" name="commoditySelect" wire:model.live="selectedCommodity" class="form-select">
            @foreach ($commodities as $commodity)
                <option value="{{ $commodity->id }}" {{ $commodity->id == $selectedCommodity ? 'selected' : '' }}>
                    {{ $commodity->name }}
                </option>
            @endforeach
            </select>
    </div>
    <div class="mb-2">
        <label for="interventionSelect" class="form-label fw-bold">Intervention</label>
        <select id="interventionSelect" name="interventionSelect" wire:model.live="selectedInterventions" class="form-select" multiple>
            @foreach ($interventions as $intervention)
                <option value="{{ $intervention->id }}"
                    {{ in_array($intervention->id, $selectedInterventions) ? 'selected' : '' }}>
                    {{ $intervention->name }}
                </option>
            @endforeach
            </select>
    </div>

</div>
