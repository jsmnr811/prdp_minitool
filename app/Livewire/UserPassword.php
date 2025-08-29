<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\User;


class UserPassword extends Component
{
    public $email;
    public $password;
    public $attempts = 0;
    protected $rules = [
        'password' => 'required|string|min:8',
    ];
    #[On('refresh-the-component')]
    // Submit the form
    public function submit()
    {
        $this->validate([
            'password' => 'required|string|min:8', // Example password validation rule
        ]);

        $user = User::where('email', $this->email)->first();

        if (Hash::check($this->password, $user->password)) {
            Auth::login($user);
            session()->flash('message', 'Login successful.');
            return redirect()->route('marketplace');
        }

        // Emit an event to Alpine.js for incorrect password
        $this->addError('password', 'The password is invalid.');
    }





    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.user-password');
    }
}
