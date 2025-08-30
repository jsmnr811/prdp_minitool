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
    public $accessCode = '';

    public function mount(): void
    {
        $this->accessCode = '';
    }

    public function login()
    {
        $this->validate([
            'accessCode' => 'required|string|size:8',
        ]);

        $user = GeomappingUser::where('login_code', $this->accessCode)->first();

        if ($user && $user->is_blocked === 1) {
            $this->addError('accessCode', 'User is blocked.Please contact system admin.');
            return;
        }

        if ($user) {
            Auth::guard('geomapping')->login($user);
            return redirect()->intended(route('geomapping.iplan.landing'));
        }

        // Instead of returning back(), add an error to the component state
        $this->addError('accessCode', 'Invalid login code.');
        return;
    }
};
?>


<div class="login-container">
    <div class="login-card text-center">
        <h2 class="mb-4 fw-bold">Enter Your Code</h2>
        <p class="text-muted mb-4">Please enter the access code to continue.</p>

        @if ($errors->any())
            <div class="alert alert-danger p-2" style="font-size: 12px">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="loginForm" wire:submit.prevent='login'>
            <div class="mb-3">
                <label for="codeInput" class="form-label visually-hidden">Access Code</label>
                <input type="text" class="form-control form-control-lg text-center" id="codeInput"
                    wire:model="accessCode" placeholder="Access Code">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
        </form>

        <!-- Register Link -->
        <p class="mt-3">
            Don't have an account?
            <a href="{{ route('geomapping.iplan.investment.registration') }}" class="text-decoration-none">
                Register
            </a>
        </p>

    </div>
</div>
