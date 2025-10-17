<?php

use App\Services\SidlanAPIServices;
use App\Services\SidlanGoogleSheetService;
use Illuminate\View\View;
use Illuminate\Support\Facades\Route;

Route::name('sidlan.')->prefix('sidlan')->group(function () {
    Route::name('ireap.')->prefix('ireap')->group(function () {

        // Route to handle the dashboard view for iReap
        // Route::get('dashboard', function (): View {
        //     $apiService = new SidlanAPIServices();
        //     $irZeroOneData = $apiService->executeRequest();

        //     return view('sidlan.ireap.dashboard', [
        //         'irZeroOneData' => $irZeroOneData,
        //     ]);
        // })->name('dashboard');

        Route::get('dashboard', function (): View {
            $apiService = new SidlanGoogleSheetService();
            $irZeroOneData = $apiService->executeRequest();

            return view('sidlan.ireap.dashboard', [
                'irZeroOneData' => $irZeroOneData,
            ]);
        })->name('dashboard');

        Route::get('d2-portfolio', function (): View {
            $apiService = new SidlanGoogleSheetService();

            $irZeroOneData = $apiService->executeRequest();
            $latestTimestamp = $apiService->getLatestLogTimestampDirect();

            // Pass both to the view
            return view('sidlan.ireap.d2-portfolio', [
                'irZeroOneData' => $irZeroOneData,
                'latestTimestamp' => $latestTimestamp,
            ]);
        })->name('d2-portfolio');
    });
});
