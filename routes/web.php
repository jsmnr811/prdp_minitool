<?php

use App\Http\Controllers\PublicController;
use App\Livewire\AddPassword;
use App\Livewire\Login;
use App\Livewire\UserPassword;
use App\Livewire\MarketPlace;
use App\Livewire\ProductTable;
use App\Livewire\UserTable;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login');
Route::get('/add-password/{email}', AddPassword::class)->name('add-password');
Route::get('/user-password/{email}', UserPassword::class)->name('user-password');

// Route::controller(PublicController::class)->name('public.')->group(function () {
//     Route::get('/', 'auctions')->name('auctions');
//     Route::get('/send-email', [UserTable::class, 'sendEmail']);
// });

//Authenticated
Route::group(['middleware' => ['auth', 'verified']], function () {

    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
    Route::get('products', ProductTable::class)->name('products');
    Route::get('marketplace', MarketPlace::class)->name('marketplace');
    Route::get('users', UserTable::class)->name('users');
});

require __DIR__ . '/auth.php';
require __DIR__ . '/sidlan.php';
require __DIR__ . '/geomapping.php';
