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
    <button wire:click="logout" class="btn btn-outline-danger ms-auto">Logout</button>

</div>
