<?php

namespace App\Livewire\Actions;

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        // Check if the user does not have a password
        if (empty(Auth::user()->password)) {
            User::where('id', Auth::user()->id)->update(['email_verified_at' => null]);
        }
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();
    }
}
