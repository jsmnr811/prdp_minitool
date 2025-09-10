<?php

use Illuminate\View\View;
use App\Livewire\UserList;
use App\Livewire\CodeLogin;
use App\Models\GeomappingUser;
use App\Services\SidlanAPIServices;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Route;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Http\Controllers\GeomappingUserExportController;
use App\Http\Controllers\GeomappingUsersTableController;
use App\Livewire\Geomapping\Iplan\InvestmentRegistration;
use App\Http\Controllers\GeomappingUsersDashboardController;

use App\Http\Controllers\GeomappingCommoditiesTableController;
use App\Http\Controllers\GeomappingAnalyticsDashboardController;
use App\Http\Controllers\GeomappingInterventionsTableController;

Route::get('/code-login', CodeLogin::class)->name('investment-forum');

Route::get('/investment-forum-registration', InvestmentRegistration::class)->name('investment.registration');
Route::get('/investment-forum-user-verification/{id}', [GeomappingUsersTableController::class, 'verifyUser'])->name('investment.user-verification');

// Route::get('/investment-forum-user-list', UserList::class)->name('investment.user-list');


Route::get('/investment-forum-user-list', [GeomappingUsersTableController::class, 'index'])->name('investment.user-list')->middleware('auth-geo:geomapping');
Route::get('/geomapping-users/{id}/id-card', [App\Http\Controllers\GeomappingUsersTableController::class, 'idCard'])->name('geomapping-users.id-card');

Route::view('/', 'geomapping.iplan.login')
    ->middleware('guest-geo:geomapping');


Route::name('geomapping.')->prefix('geomapping')->group(function () {
    Route::name('iplan.')->prefix('iplan')->group(function () {
        Route::get('/geomapping/generate-all-ids', [GeomappingUsersTableController::class, 'generateAllIds'])
            ->name('investment.generate-user-id')
            ->middleware('auth:geomapping');
        Route::get('/investment-forum-user-list', [GeomappingUsersTableController::class, 'index'])->name('investment.user-list')->middleware('auth-geo:geomapping');
        Route::get('/investment-forum-user-dashboard', [GeomappingUsersDashboardController::class, 'dashboard'])->name('investment.user-dashboard')->middleware('auth-geo:geomapping');
        Route::get('/investment-forum-registration', InvestmentRegistration::class)->name('investment.registration')->middleware('guest-geo:geomapping');
        Route::get('/investment-forum-commodity-list', [GeomappingCommoditiesTableController::class, 'index'])->name('investment.commodity-list')->middleware('auth-geo:geomapping');
        Route::get('/investment-forum-intervention-list', [GeomappingInterventionsTableController::class, 'index'])->name('investment.intervention-list')->middleware('auth-geo:geomapping');
        Route::get('/analytics-dashboard', [GeomappingAnalyticsDashboardController::class, 'index'])->name('investment.analytics-dashboard')->middleware('auth-geo:geomapping');

        Route::view('login', 'geomapping.iplan.login')
            ->name('login')
            ->middleware('guest-geo:geomapping');
        Route::get('/export-users', [GeomappingUserExportController::class, 'exportCsv'])->name('export.users');

        Route::middleware('auth-geo:geomapping')->group(function () {
            Route::view('dashboard', 'geomapping.iplan.dashboard')->name('dashboard');
            Route::view('dashboard-2', 'geomapping.iplan.dashboard-2')->name('dashboard-2');
            Route::view('dashboard-3', 'geomapping.iplan.dashboard-3')->name('dashboard-3');
            Route::view('landing', 'geomapping.iplan.landing')->name('landing');
        });
    });
});

Route::get('sidlaner', function () {
    $user = GeomappingUser::find(1);
    $fileName = 'user-image-' . $user->id . '.png';
    $storagePath = storage_path('app/public/' . $fileName);
    $qrCode =  QrCode::size(300)
        ->backgroundColor(255, 55, 0)
        ->generate(route('investment.user-verification', ['id' => $this->user->id]));
    $imageBase64 = 'data:image/png;base64,' . base64_encode($qrCode);
    // Render the blade view to HTML
    $html = view('components.user-id', ['user' => $user, 'qrCode' => $imageBase64])->render();
    return $html;
    // Generate PNG with fixed window size
    Browsershot::html($html)
        ->windowSize(350, 566)
        ->waitUntilNetworkIdle() // ensure all images load
        ->save($storagePath);
})->name('sidlan');
