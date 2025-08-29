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
    <a href="#" wire:click="logout"
        class="text-gray-800 dark:text-white btn-outline-danger hover:bg-gray-50 focus:ring-4 focus:ring-gray-300 font-medium rounded-lg text-sm px-4 lg:px-5 py-2 lg:py-2.5 dark:hover:bg-gray-700 focus:outline-none dark:focus:ring-gray-800">
        Log out
    </a>
</div>
