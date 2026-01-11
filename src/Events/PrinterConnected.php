<?php

namespace BitDreamIT\QzTray\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrinterConnected
{
    use Dispatchable, SerializesModels;

    public $printerName;
    public $connectionInfo;

    public function __construct(string $printerName, array $connectionInfo = [])
    {
        $this->printerName = $printerName;
        $this->connectionInfo = $connectionInfo;
    }
}
