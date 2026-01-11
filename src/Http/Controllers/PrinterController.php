<?php

namespace BitDreamIT\QzTray\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use BitDreamIT\QzTray\Services\PrinterService;

class PrinterController extends Controller
{
    protected $printerService;

    public function __construct(PrinterService $printerService)
    {
        $this->printerService = $printerService;
    }

    /**
     * Get all printers
     */
    public function index(Request $request)
    {
        $printers = $this->printerService->getAllPrinters();

        return response()->json([
            'success' => true,
            'data' => $printers,
            'count' => count($printers),
            'timestamp' => now(),
        ]);
    }

    /**
     * Get default printer
     */
    public function default(Request $request)
    {
        $printer = $this->printerService->getDefaultPrinter();

        return response()->json([
            'success' => true,
            'data' => [
                'printer' => $printer,
                'is_default' => true,
            ],
            'timestamp' => now(),
        ]);
    }

    /**
     * Get printer capabilities
     */
    public function capabilities(Request $request, string $printer)
    {
        $capabilities = $this->printerService->getPrinterCapabilities($printer);

        return response()->json([
            'success' => true,
            'data' => [
                'printer' => $printer,
                'capabilities' => $capabilities,
            ],
            'timestamp' => now(),
        ]);
    }

    /**
     * Clear printer queue
     */
    public function clearQueue(Request $request, string $printer)
    {
        try {
            $this->printerService->clearQueue($printer);

            return response()->json([
                'success' => true,
                'message' => 'Printer queue cleared successfully',
                'data' => [
                    'printer' => $printer,
                    'cleared_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Save user printer preference
     */
    public function savePreference(Request $request)
    {
        $request->validate([
            'printer' => 'required|string',
            'module' => 'nullable|string',
        ]);

        try {
            $printer = $request->input('printer');
            $module = $request->input('module');

            $this->printerService->saveUserPreference($printer, $module);

            return response()->json([
                'success' => true,
                'message' => 'Printer preference saved',
                'data' => [
                    'printer' => $printer,
                    'module' => $module,
                    'saved_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user printer preference
     */
    public function getPreference(Request $request)
    {
        $module = $request->input('module');

        $printer = $this->printerService->getUserPreference($module);

        return response()->json([
            'success' => true,
            'data' => [
                'printer' => $printer,
                'module' => $module,
            ],
            'timestamp' => now(),
        ]);
    }

    /**
     * Get printer status
     */
    public function status(Request $request, string $printer)
    {
        $status = $this->printerService->getPrinterStatus($printer);

        return response()->json([
            'success' => true,
            'data' => $status,
            'timestamp' => now(),
        ]);
    }

    /**
     * Refresh printer cache
     */
    public function refresh(Request $request)
    {
        Cache::forget('qz_printers');
        Cache::forget('qz_capabilities');

        $printers = $this->printerService->getAllPrinters();

        return response()->json([
            'success' => true,
            'message' => 'Printer cache refreshed',
            'data' => [
                'printers' => $printers,
                'count' => count($printers),
            ],
            'timestamp' => now(),
        ]);
    }
}
