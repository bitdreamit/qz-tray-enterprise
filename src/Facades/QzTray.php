<?php

namespace BitDreamIT\QzTray\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \BitDreamIT\QzTray\Services\QzService qzService()
 * @method static \BitDreamIT\QzTray\Services\PrinterService printerService()
 * @method static \BitDreamIT\QzTray\Services\PrintService printService()
 * @method static string getCertificate()
 * @method static string signData(string $data)
 * @method static string getVersion()
 * @method static bool isEnabled()
 * @method static bool checkRequirements()
 * @method static array getSystemStatus()
 * @method static array getDiagnostics()
 * @method static array getAllPrinters()
 * @method static string|null getDefaultPrinter()
 * @method static array getPrinterCapabilities(string $printerName)
 * @method static bool saveUserPreference(string $printerName, string $module = null)
 * @method static string|null getUserPreference(string $module = null)
 * @method static bool clearQueue(string $printerName)
 * @method static array getPrinterStatus(string $printerName)
 * @method static \BitDreamIT\QzTray\Models\PrintJob printRaw(string $printer, string $text, array $options = [])
 * @method static \BitDreamIT\QzTray\Models\PrintJob printHtml(string $printer, string $html, array $options = [])
 * @method static \BitDreamIT\QzTray\Models\PrintJob printPdf(string $printer, string $pdfUrl, array $options = [])
 * @method static \BitDreamIT\QzTray\Models\PrintJob printImage(string $printer, string $imageUrl, array $options = [])
 * @method static \BitDreamIT\QzTray\Models\PrintJob printZpl(string $printer, string $zplCommands, array $options = [])
 * @method static \BitDreamIT\QzTray\Models\PrintJob printEscpos(string $printer, string $commands, array $options = [])
 * @method static \BitDreamIT\QzTray\Models\PrintJob printBarcode(string $printer, string $data, string $type = 'CODE128', array $options = [])
 * @method static \Illuminate\Database\Eloquent\Collection getJobHistory(int $limit = 50, int $userId = null)
 * @method static array getQueueStatus()
 *
 * @see \BitDreamIT\QzTray\QzTray
 */
class QzTray extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'qz-tray';
    }
}
