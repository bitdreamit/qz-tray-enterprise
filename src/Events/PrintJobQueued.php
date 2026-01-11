<?php

namespace BitDreamIT\QzTray\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use BitDreamIT\QzTray\Models\PrintJob;

class PrintJobQueued
{
    use Dispatchable, SerializesModels;

    public $printJob;

    public function __construct(PrintJob $printJob)
    {
        $this->printJob = $printJob;
    }
}
