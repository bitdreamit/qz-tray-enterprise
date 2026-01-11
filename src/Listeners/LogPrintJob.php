<?php

namespace BitDreamIT\QzTray\Listeners;

use Illuminate\Support\Facades\Log;
use BitDreamIT\QzTray\Events\PrintJobQueued;

class LogPrintJob
{
    public function handle(PrintJobQueued $event)
    {
        $job = $event->printJob;

        Log::info('Print job queued', [
            'job_id' => $job->id,
            'user_id' => $job->user_id,
            'printer' => $job->printer,
            'type' => $job->type,
            'status' => $job->status,
            'ip_address' => $job->ip_address,
        ]);
    }
}
