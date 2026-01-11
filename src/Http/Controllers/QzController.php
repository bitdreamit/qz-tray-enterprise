<?php

namespace BitDreamIT\QzTray\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use BitDreamIT\QzTray\Services\QzService;
use BitDreamIT\QzTray\Services\PrinterService;
use BitDreamIT\QzTray\Services\PrintService;

class QzController extends Controller
{
    protected $qzService;
    protected $printerService;
    protected $printService;

    public function __construct(
        QzService $qzService,
        PrinterService $printerService,
        PrintService $printService
    ) {
        $this->qzService = $qzService;
        $this->printerService = $printerService;
        $this->printService = $printService;
    }

    /**
     * Dashboard view
     */
    public function dashboard()
    {
        if (!$this->qzService->isEnabled()) {
            return view('qz-tray::disabled');
        }

        $systemStatus = $this->qzService->getSystemStatus();
        $printers = $this->printerService->getAllPrinters();
        $queueStatus = $this->printService->getQueueStatus();

        return view('qz-tray::dashboard', compact(
            'systemStatus',
            'printers',
            'queueStatus'
        ));
    }

    /**
     * Diagnostics view
     */
    public function diagnostics()
    {
        $diagnostics = $this->qzService->getDiagnostics();
        $printers = $this->printerService->getAllPrinters();

        return view('qz-tray::diagnostics', compact(
            'diagnostics',
            'printers'
        ));
    }

    /**
     * Get certificate
     */
    public function getCertificate()
    {
        try {
            $certificate = $this->qzService->getCertificate();

            return response($certificate, 200, [
                'Content-Type' => 'text/plain',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            return response('Certificate not available', 500);
        }
    }

    /**
     * Sign data
     */
    public function sign(Request $request)
    {
        try {
            $data = $request->getContent();

            if (empty($data)) {
                return response('No data to sign', 400);
            }

            $signature = $this->qzService->signData($data);

            return response($signature, 200, [
                'Content-Type' => 'text/plain',
            ]);
        } catch (\Exception $e) {
            return response('Signature failed', 500);
        }
    }

    /**
     * Get system status API
     */
    public function status()
    {
        return response()->json([
            'success' => true,
            'data' => $this->qzService->getSystemStatus(),
        ]);
    }

    /**
     * Test print endpoint
     */
    public function testPrint(Request $request)
    {
        $request->validate([
            'printer' => 'required|string',
        ]);

        try {
            $printer = $request->input('printer');

            // Create test print job
            $testData = "================================\n";
            $testData .= "   QZ Tray Test Print\n";
            $testData .= "   Date: " . now()->format('Y-m-d H:i:s') . "\n";
            $testData .= "   Printer: {$printer}\n";
            $testData .= "   User: " . (auth()->user()->name ?? 'Anonymous') . "\n";
            $testData .= "================================\n\n";
            $testData .= "This is a test print to verify\n";
            $testData .= "that the printer is working\n";
            $testData .= "correctly with QZ Tray.\n\n";
            $testData .= "✓ Connection: OK\n";
            $testData .= "✓ Security: OK\n";
            $testData .= "✓ Printing: OK\n\n";
            $testData .= "--------------------------------\n";

            $job = $this->printService->printRaw($printer, $testData);

            return response()->json([
                'success' => true,
                'message' => 'Test print job queued',
                'job_id' => $job->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
