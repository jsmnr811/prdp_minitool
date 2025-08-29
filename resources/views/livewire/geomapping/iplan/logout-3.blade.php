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
        class="block w-full text-center btn-outline-danger py-2 px-4 text-gray-800 dark:text-white bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
        Log out
    </a>

</div>
