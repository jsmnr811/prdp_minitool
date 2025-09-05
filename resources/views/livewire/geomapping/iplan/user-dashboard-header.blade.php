<?php

use Livewire\Volt\Component;
use App\Models\GeomappingUser;

new class extends Component {
    public array $dataCounts = [];

    public function mount(): void
    {
        $geoUser = GeomappingUser::query();

        $this->dataCounts = [
            'totalRegisteredPax' => (clone $geoUser)->count(),
            'totalVerifiedPax' => (clone $geoUser)->where('is_verified', 1)->count(),
            'totalUnverifiedPax' => (clone $geoUser)->where('is_verified', 0)->count(),
            'totalBlockedPax' => (clone $geoUser)->where('is_blocked', 1)->count(),
            'totalUnblockedPax' => (clone $geoUser)->where('is_blocked', 0)->count(),
            'totalLiveInPax' => (clone $geoUser)->where('is_livein', 1)->count(),
            'totalNonLiveInPax' => (clone $geoUser)->where('is_livein', 0)->count(),
        ];
    }

    public function placeholder(){
        return view('livewire.geomapping.iplan.placeholder.user-dashboard-header');
    }
};

?>
<div>
<div class="d-flex flex-wrap gap-2">
    <span class="badge bg-primary">Registered: {{ $dataCounts['totalRegisteredPax'] }}</span>
    <span class="badge bg-success">Verified: {{ $dataCounts['totalVerifiedPax'] }}</span>
    <span class="badge bg-warning text-dark">Unverified: {{ $dataCounts['totalUnverifiedPax'] }}</span>
    <span class="badge bg-danger">Blocked: {{ $dataCounts['totalBlockedPax'] }}</span>
    <span class="badge bg-secondary">Unblocked: {{ $dataCounts['totalUnblockedPax'] }}</span>
    <span class="badge bg-info text-dark">Live In: {{ $dataCounts['totalLiveInPax'] }}</span>
    <span class="badge bg-dark">Non Live In: {{ $dataCounts['totalNonLiveInPax'] }}</span>
</div>

</div>
