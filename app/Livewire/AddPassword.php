<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;

class AddPassword extends Component
{
    public $email;
    // public $otp;
    public $password;
    public $password_confirmation;

    // Validation rules
    protected $rules = [
        // 'otp' => 'required|numeric|digits:5',
        'password' => 'required|string|min:8|confirmed',
        'password_confirmation' => 'required|string|min:8',
    ];

    protected $messages = [
        'password.confirmed' => 'The password confirmation does not match.',
    ];

    // Submit the form
    public function submit()
    {
        $this->validate();

        $user = User::where('email', $this->email)->first();

        if ($user) {
            // if ($user->otp != $this->otp) {
            //     $this->addError('otp', 'The OTP is invalid.');
            //     return;
            // } else {
                $user->password = Hash::make($this->password);
                $user->email_verified_at = now();
                $user->save();

                Auth::login($user);
                session()->flash('message', 'Login successful.');
                return redirect()->route('marketplace');
            // }
        } else {
            session()->flash('error', 'User not found.');
        }

        // Reset the form inputs
        $this->reset(['password', 'password_confirmation']);
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.add-password');
    }
}
