<?php

namespace BitDreamIT\QzTray\Listeners;

use Illuminate\Support\Facades\Log;
use BitDreamIT\QzTray\Events\SmartPrintSuccess;
use BitDreamIT\QzTray\Events\SmartPrintFailed;

class LogSmartPrint
{
    /**
     * Handle smart print success
     */
    public function handleSuccess(SmartPrintSuccess $event)
    {
        Log::info('Smart print successful', [
            'log_id' => $event->log->id,
            'method' => $event->result['method'],
            'url' => $event->log->url,
            'user_id' => $event->log->user_id,
        ]);
    }

    /**
     * Handle smart print failure
     */
    public function handleFailure(SmartPrintFailed $event)
    {
        Log::error('Smart print failed', [
            'log_id' => $event->log->id,
            'error' => $event->error->getMessage(),
            'url' => $event->log->url,
            'user_id' => $event->log->user_id,
        ]);
    }

    /**
     * Register listeners
     */
    public function subscribe($events)
    {
        $events->listen(
            SmartPrintSuccess::class,
            [LogSmartPrint::class, 'handleSuccess']
        );

        $events->listen(
            SmartPrintFailed::class,
            [LogSmartPrint::class, 'handleFailure']
        );
    }
}
