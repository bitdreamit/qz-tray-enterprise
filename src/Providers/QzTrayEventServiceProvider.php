<?php

namespace BitDreamIT\QzTray\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use BitDreamIT\QzTray\Events\PrintJobQueued;
use BitDreamIT\QzTray\Events\PrinterConnected;
use BitDreamIT\QzTray\Listeners\LogPrintJob;

class QzTrayEventServiceProvider extends ServiceProvider
{
    protected $listen = [
        PrintJobQueued::class => [
            LogPrintJob::class,
        ],
        PrinterConnected::class => [
            // Add listeners for printer connection events
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}
