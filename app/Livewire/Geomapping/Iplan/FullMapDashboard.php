<?php

namespace App\Livewire\Geomapping\Iplan;

use App\Models\Region;
use Livewire\Component;
use App\Models\Province;
use Livewire\Attributes\On;
use App\Models\GeoCommodity;
use App\Events\GeoCommodityUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FullMapDashboard extends Component
{
    public ?array $provinceGeo = [];
    public ?string $userRole = null;
    public bool $isLoadingMap = true;
    public bool $isMapRendering = true;

    public function initData()
    {
        try {
            $user = Auth::guard('geomapping')->user();
            $this->userRole = $user->role ?? null;

            $this->provinceGeo = GeoCommodity::with('commodity', 'geoInterventions.intervention')->get()->toArray();
        } catch (\Exception $e) {
            Log::error('Mount error: ' . $e->getMessage());
        } finally {
            $this->isLoadingMap = false;
        }
    }

    #[On('echo:commodities-updates,geo.commodity.updated')]
    public function commoditiesUpdated()
    {
        dd('dasdsadd');
        sleep(2);
        
        $this->initData();
        $this->dispatch('provinceGeoUpdated', $this->provinceGeo);

    }

    public function buttonTest(){
        GeoCommodityUpdated::dispatch();
    }

    public function render()
    {

        $this->initData();

        return view('livewire.geomapping.iplan.full-map-dashboard', [
            'provinceGeo' => $this->provinceGeo,
        ]);
    }
}
