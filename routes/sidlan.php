<?php

use App\Services\SidlanAPIServices;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;

Route::name('sidlan.')->prefix('sidlan')->group(function () {
    Route::name('ireap.')->prefix('ireap')->group(function () {

        // Route to handle the dashboard view for iReap
        Route::get('dashboard', function (): View {
            $apiService = new SidlanAPIServices();
            $irZeroOneData = $apiService->executeRequest();

            return view('sidlan.ireap.dashboard', [
                'irZeroOneData' => $irZeroOneData,
            ]);
        })->name('dashboard');


    });
});
