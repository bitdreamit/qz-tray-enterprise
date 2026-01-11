<?php

namespace BitDreamIT\QzTray\Providers;

use BitDreamIT\QzTray\Console\GenerateCertificateCommand;
use BitDreamIT\QzTray\Console\InstallQzTrayCommand;
use BitDreamIT\QzTray\Services\PrinterService;
use BitDreamIT\QzTray\Services\PrintService;
use BitDreamIT\QzTray\Services\QzService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class QzTrayServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(__DIR__.'/../../config/qz-tray.php', 'qz-tray');

        // Register services
        $this->app->singleton('qz-service', function ($app) {
            return new QzService($app['config']['qz-tray']);
        });

        $this->app->singleton('printer-service', function ($app) {
            return new PrinterService;
        });

        $this->app->singleton('print-service', function ($app) {
            return new PrintService;
        });

        // Register SmartPrintService
        $this->app->singleton('smart-print-service', function ($app) {
            return new \BitDreamIT\QzTray\Services\SmartPrintService(
                $app['config']['qz-tray']
            );
        });

        // Register facade
        $this->app->bind('qz-tray', function ($app) {
            return new \BitDreamIT\QzTray\Facades\QzTray(
                $app['qz-service'],
                $app['printer-service'],
                $app['print-service']
            );
        });
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../../config/qz-tray.php' => config_path('qz-tray.php'),
        ], 'qz-tray-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../../database/migrations/' => database_path('migrations'),
        ], 'qz-tray-migrations');

        // Publish assets
        $this->publishes([
            __DIR__.'/../Resources/assets' => public_path('vendor/qz-tray'),
        ], 'qz-tray-assets');

        // Publish views
        $this->publishes([
            __DIR__.'/../Resources/views' => resource_path('views/vendor/qz-tray'),
        ], 'qz-tray-views');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'qz-tray');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallQzTrayCommand::class,
                GenerateCertificateCommand::class,
            ]);
        }

        // Register middleware
        Route::aliasMiddleware('qz-tray.auth', \BitDreamIT\QzTray\Http\Middleware\QzTrayMiddleware::class);

        // Publish smart print assets
        $this->publishes([
            __DIR__.'/../Resources/assets/js/qz-tray/smart-print.js' => public_path('vendor/qz-tray/js/smart-print.js'),
        ], 'qz-tray-smart-print');

        // Load helpers
        $this->loadHelpers();
    }

    protected function loadHelpers()
    {
        $file = __DIR__.'/../Helpers/smartPrint.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
}
