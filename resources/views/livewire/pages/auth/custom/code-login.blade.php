<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\form;
use function Livewire\Volt\layout;

layout('layouts.guest');

form(LoginForm::class);

$login = function () {
    $this->validate();

    $this->form->authenticate();

    Session::regenerate();

    $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
};

?>

<div>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit.prevent="login">
        @if($errors->has('form.code'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ $errors->first('form.code') }}</span>
        </div>
        @endif

        <div>
            <x-input-label for="code" :value="__('Enter Code')" />
            <x-text-input
                wire:model="form.code"
                id="code"
                class="block mt-1 w-full"
                type="text"
                name="code"
                required
                autofocus
                autocomplete="one-time-code"
                maxlength="8" />
            <x-input-error :messages="$errors->get('form.code')" class="mt-2" />
        </div>


        <div class="flex items-center justify-end mt-4">
            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</div>