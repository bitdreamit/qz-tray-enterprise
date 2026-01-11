<?php

use Illuminate\Support\Facades\Route;
use BitDreamIT\QzTray\Http\Controllers\QzController;
use BitDreamIT\QzTray\Http\Controllers\PrinterController;
use BitDreamIT\QzTray\Http\Controllers\PrintController;

/*
|--------------------------------------------------------------------------
| QZ Tray Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the QzTrayServiceProvider.
|
*/

Route::group([
    'prefix' => config('qz-tray.ui.dashboard_route', '/qz-tray'),
    'middleware' => config('qz-tray.middleware', ['web', 'auth']),
    'as' => 'qz-tray.'
], function () {

    // Dashboard
    Route::get('/dashboard', [QzController::class, 'dashboard'])
        ->name('dashboard');

    Route::get('/diagnostics', [QzController::class, 'diagnostics'])
        ->name('diagnostics');

    // Security endpoints
    Route::get('/certificate', [QzController::class, 'getCertificate'])
        ->name('certificate');

    Route::post('/sign', [QzController::class, 'sign'])
        ->name('sign');


    // API endpoints
    Route::prefix('/api')->group(function () {
        // Status
        Route::get('/status', [QzController::class, 'status'])
            ->name('api.status');

        // Printers
        Route::get('/printers', [PrinterController::class, 'index'])
            ->name('api.printers.index');

        Route::get('/printers/default', [PrinterController::class, 'default'])
            ->name('api.printers.default');

        Route::get('/printers/{printer}/capabilities', [PrinterController::class, 'capabilities'])
            ->name('api.printers.capabilities');

        Route::post('/printers/{printer}/test', [QzController::class, 'testPrint'])
            ->name('api.printers.test');

        Route::delete('/printers/{printer}/queue', [PrinterController::class, 'clearQueue'])
            ->name('api.printers.clear-queue');

        // Print jobs
        Route::post('/print/raw', [PrintController::class, 'raw'])
            ->name('api.print.raw');

        Route::post('/print/html', [PrintController::class, 'html'])
            ->name('api.print.html');

        Route::post('/print/pdf', [PrintController::class, 'pdf'])
            ->name('api.print.pdf');

        Route::post('/print/image', [PrintController::class, 'image'])
            ->name('api.print.image');

        Route::post('/print/zpl', [PrintController::class, 'zpl'])
            ->name('api.print.zpl');

        Route::post('/print/escpos', [PrintController::class, 'escpos'])
            ->name('api.print.escpos');

        Route::post('/print/barcode', [PrintController::class, 'barcode'])
            ->name('api.print.barcode');

        // Job management
        Route::get('/jobs', [PrintController::class, 'index'])
            ->name('api.jobs.index');

        Route::get('/jobs/{id}', [PrintController::class, 'show'])
            ->name('api.jobs.show');

        Route::get('/queue/status', [PrintController::class, 'queueStatus'])
            ->name('api.queue.status');

        // Smart print endpoint
        Route::post('/smart-print', [\BitDreamIT\QzTray\Http\Controllers\PrintController::class, 'smartPrint'])
            ->name('api.smart-print');

        // Smart print statistics
        Route::get('/smart-print/stats', [\BitDreamIT\QzTray\Http\Controllers\PrintController::class, 'smartPrintStats'])
            ->name('api.smart-print.stats');

        // Smart print logs
        Route::get('/smart-print/logs', [\BitDreamIT\QzTray\Http\Controllers\PrintController::class, 'smartPrintLogs'])
            ->name('api.smart-print.logs');
    });

    // User preferences
    Route::post('/preferences/printer', [PrinterController::class, 'savePreference'])
        ->name('preferences.printer.save');

    Route::get('/preferences/printer', [PrinterController::class, 'getPreference'])
        ->name('preferences.printer.get');
});
