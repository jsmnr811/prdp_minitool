<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Livewire\Attributes\Layout;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendEmail;

class Login extends Component
{
    public $form = [
        'email' => '',
        'remember' => false,
    ];

    // Validation rules
    protected $rules = [
        'form.email' => 'required|email|exists:users,email',
    ];
    protected $messages = [
        'form.email.exists' => 'This email is not registered in our system.',
    ];
    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.pages.auth.custom.login');
    }

    public function checkEmail()
    {
        $this->validate([
            'form.email' => 'required|email',
        ]);

        $user = User::where('email', $this->form['email'])->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'form.email' => 'This email is not registered in our system.',
            ]);
        }

        if (!empty($user->password)) {
            return redirect()->route('user-password', ['email' => $this->form['email']]);
        } else {
            // $otp = rand(10000, 99999);

            // $user->otp = $otp;
            // $user->otp_sent_at = Carbon::now();
            // $user->otp_expires_at = Carbon::now()->addMinutes(30);
            // $user->save();

            // Mail::to($user->email)->send(new SendEmail($otp));

            // Redirect to the route where user can add a password
            return redirect()->route('add-password', ['email' => $this->form['email']]);
        }
    }
}
