<?php

use App\Models\Commodity;
use Livewire\Attributes\On;
use App\Models\GeoCommodity;
use App\Models\Intervention;
use Livewire\Volt\Component;
use App\Models\GeomappingUser;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\Facades\LivewireAlert;

new class extends Component {
    public function logout()
    {
        Auth::guard('geomapping')->logout();

        // Redirect to login page (adjust route name accordingly)
        return redirect()->route('geomapping.iplan.login');
    }
};
?>

<div>
    <button wire:click="logout" class="btn btn-outline-danger d-flex align-items-center gap-2 px-3 py-2 border-2 transition-all duration-300 hover:shadow-md">
        <i class="bi bi-box-arrow-right fs-6"></i>
        <span class="fw-medium">Logout</span>
    </button>
</div>
