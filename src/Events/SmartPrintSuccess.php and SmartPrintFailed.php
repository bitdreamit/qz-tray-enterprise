<?php
// src/Events/SmartPrintSuccess.php
namespace BitDreamIT\QzTray\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use BitDreamIT\QzTray\Models\SmartPrintLog;

class SmartPrintSuccess
{
    use Dispatchable, SerializesModels;

    public $log;
    public $result;

    public function __construct(SmartPrintLog $log, array $result)
    {
        $this->log = $log;
        $this->result = $result;
    }
}
