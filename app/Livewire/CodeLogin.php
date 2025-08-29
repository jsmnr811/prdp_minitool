<?php

namespace App\Livewire;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use App\Models\GeomappingUser;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CodeLogin extends Component
{
    public $form = [
        'code' => '',
    ];

    // Replace email rules with code rules
    protected $rules = [
        'form.code' => 'required|string|size:8|alpha_num',
    ];

    protected $messages = [
        'form.code.required' => 'Please enter the verification code.',
        'form.code.size' => 'The code must be exactly 8 characters long.',
        'form.code.alpha_num' => 'The code must contain only letters and numbers.',
    ];

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.auth.custom.code-login');
    }

    // public function login()
    // {
    //     $this->validate();

    //     $user = User::whereHas('userInformation', function ($query) {
    //         $query->whereJsonContains('code', $this->form['code']);
    //     })->first();

    //     if (!$user) {
    //         throw ValidationException::withMessages([
    //             'form.code' => 'Invalid code.',
    //         ]);
    //     }

    //     // Log in the user without password check since code is unique
    //     Auth::login($user);

    //     session()->regenerate();

    //     $url = route('geomapping.iplan.dashboard-2');
    //     return redirect($url);
    // }

    public function login()
    {
        $this->validate();

        $user = GeomappingUser::where('login_code', $this->form['code'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'form.code' => 'Invalid code.',
            ]);
        }

        // Use the correct guard
        Auth::guard('geomapping')->login($user);

        // session()->regenerate();

        // return redirect()->route('investment.user-list');
        session()->regenerate();

        $url = route('geomapping.iplan.landing');
        return redirect($url);
    }
}
