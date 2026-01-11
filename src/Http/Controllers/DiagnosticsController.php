<?php

namespace BitDreamIT\QzTray\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use BitDreamIT\QzTray\Services\QzService;
use BitDreamIT\QzTray\Services\PrinterService;
use BitDreamIT\QzTray\Services\PrintService;
use BitDreamIT\QzTray\Models\PrintJob;

class DiagnosticsController extends Controller
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
     * Run full diagnostics
     */
    public function runDiagnostics(Request $request)
    {
        $tests = [];

        // Test 1: System Requirements
        $tests['requirements'] = $this->testRequirements();

        // Test 2: QZ Tray Service
        $tests['qz_service'] = $this->testQzService();

        // Test 3: Printer Service
        $tests['printer_service'] = $this->testPrinterService();

        // Test 4: Print Service
        $tests['print_service'] = $this->testPrintService();

        // Test 5: Database Connectivity
        $tests['database'] = $this->testDatabase();

        // Test 6: Cache System
        $tests['cache'] = $this->testCache();

        // Test 7: Storage System
        $tests['storage'] = $this->testStorage();

        // Test 8: Security
        $tests['security'] = $this->testSecurity();

        // Calculate overall health
        $health = $this->calculateHealth($tests);

        // Store diagnostic result
        $this->storeDiagnosticResult($tests, $health);

        return response()->json([
            'success' => true,
            'data' => [
                'timestamp' => now(),
                'health' => $health,
                'tests' => $tests,
                'summary' => $this->generateSummary($tests),
            ],
        ]);
    }

    /**
     * Get diagnostic history
     */
    public function history(Request $request)
    {
        $limit = $request->input('limit', 10);

        $history = Cache::get('qz_diagnostics_history', []);

        return response()->json([
            'success' => true,
            'data' => array_slice($history, 0, $limit),
            'count' => count($history),
        ]);
    }

    /**
     * Get system metrics
     */
    public function metrics(Request $request)
    {
        $metrics = [
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'timezone' => config('app.timezone'),
            ],
            'qz_tray' => [
                'enabled' => config('qz-tray.enabled'),
                'version' => $this->qzService->getVersion(),
                'print_jobs_today' => PrintJob::whereDate('created_at', today())->count(),
                'print_jobs_total' => PrintJob::count(),
            ],
            'performance' => [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'cpu_cores' => function_exists('sys_getloadavg') ? count(sys_getloadavg()) : null,
                'load_average' => function_exists('sys_getloadavg') ? sys_getloadavg() : null,
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'printer_cache' => Cache::has('qz_printers') ? 'cached' : 'empty',
                'capabilities_cache' => Cache::has('qz_capabilities') ? 'cached' : 'empty',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'timestamp' => now(),
        ]);
    }

    /**
     * Clear diagnostic data
     */
    public function clear(Request $request)
    {
        Cache::forget('qz_diagnostics_history');

        // Clear old print jobs if requested
        if ($request->input('clear_jobs')) {
            $days = $request->input('days', 30);
            $deleted = PrintJob::where('created_at', '<', now()->subDays($days))->delete();

            Log::info('Cleared old print jobs', ['deleted' => $deleted, 'days' => $days]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Diagnostic data cleared',
            'data' => [
                'cleared_at' => now(),
                'jobs_deleted' => $deleted ?? 0,
            ],
        ]);
    }

    /**
     * Export diagnostics
     */
    public function export(Request $request)
    {
        $diagnostics = $this->runDiagnostics($request)->getData(true);

        $filename = 'qz-diagnostics-' . now()->format('Y-m-d-H-i-s') . '.json';

        return response()->json($diagnostics, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Test system requirements
     */
    protected function testRequirements()
    {
        $result = [
            'name' => 'System Requirements',
            'passed' => true,
            'details' => [],
        ];

        // Check PHP extensions
        $extensions = [
            'openssl' => extension_loaded('openssl'),
            'json' => extension_loaded('json'),
            'mbstring' => extension_loaded('mbstring'),
            'fileinfo' => extension_loaded('fileinfo'),
        ];

        foreach ($extensions as $ext => $loaded) {
            $result['details'][$ext] = $loaded ? 'installed' : 'missing';
            if (!$loaded) {
                $result['passed'] = false;
            }
        }

        // Check PHP version
        $phpVersion = PHP_VERSION;
        $minVersion = '8.0';
        $result['details']['php_version'] = $phpVersion;
        $result['details']['php_version_ok'] = version_compare($phpVersion, $minVersion, '>=');

        if (!version_compare($phpVersion, $minVersion, '>=')) {
            $result['passed'] = false;
        }

        // Check HTTPS
        $isHttps = request()->secure();
        $allowLocalhost = config('qz-tray.security.allow_localhost');
        $result['details']['https'] = $isHttps ? 'enabled' : 'disabled';
        $result['details']['https_required'] = config('qz-tray.security.require_https');

        if (!$isHttps && config('qz-tray.security.require_https') && !$allowLocalhost) {
            $result['passed'] = false;
        }

        return $result;
    }

    /**
     * Test QZ Tray service
     */
    protected function testQzService()
    {
        $result = [
            'name' => 'QZ Tray Service',
            'passed' => true,
            'details' => [],
        ];

        try {
            // Test certificate generation
            $certificate = $this->qzService->getCertificate();
            $result['details']['certificate'] = $certificate ? 'available' : 'missing';

            // Test service status
            $status = $this->qzService->getSystemStatus();
            $result['details']['service_status'] = $status['enabled'] ? 'enabled' : 'disabled';
            $result['details']['version'] = $status['version'];

            // Test requirements check
            $requirements = $this->qzService->checkRequirements();
            $result['details']['requirements_check'] = $requirements ? 'passed' : 'failed';

            if (!$requirements) {
                $result['passed'] = false;
            }

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test printer service
     */
    protected function testPrinterService()
    {
        $result = [
            'name' => 'Printer Service',
            'passed' => true,
            'details' => [],
        ];

        try {
            // Test printer discovery
            $printers = $this->printerService->getAllPrinters();
            $result['details']['printer_count'] = count($printers);
            $result['details']['discovery_working'] = count($printers) > 0 ? 'yes' : 'no';

            // Test default printer
            $defaultPrinter = $this->printerService->getDefaultPrinter();
            $result['details']['default_printer'] = $defaultPrinter ?: 'none';

            if (count($printers) === 0) {
                $result['passed'] = false;
            }

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test print service
     */
    protected function testPrintService()
    {
        $result = [
            'name' => 'Print Service',
            'passed' => true,
            'details' => [],
        ];

        try {
            // Test queue status
            $queueStatus = $this->printService->getQueueStatus();
            $result['details']['queue_status'] = $queueStatus;

            // Test job history
            $history = $this->printService->getJobHistory(5);
            $result['details']['recent_jobs'] = $history->count();

            // Test print job creation
            $testJob = $this->printService->printRaw(
                'TEST_PRINTER',
                'Diagnostic test print',
                ['copies' => 1]
            );

            $result['details']['job_creation'] = $testJob ? 'success' : 'failed';
            $result['details']['test_job_id'] = $testJob ? $testJob->id : null;

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test database
     */
    protected function testDatabase()
    {
        $result = [
            'name' => 'Database',
            'passed' => true,
            'details' => [],
        ];

        try {
            // Test connection
            DB::connection()->getPdo();
            $result['details']['connection'] = 'connected';

            // Test print jobs table
            $tableExists = DB::getSchemaBuilder()->hasTable('print_jobs');
            $result['details']['print_jobs_table'] = $tableExists ? 'exists' : 'missing';

            if (!$tableExists) {
                $result['passed'] = false;
            }

            // Test data retrieval
            $jobCount = PrintJob::count();
            $result['details']['job_count'] = $jobCount;

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test cache system
     */
    protected function testCache()
    {
        $result = [
            'name' => 'Cache System',
            'passed' => true,
            'details' => [],
        ];

        try {
            // Test cache write/read
            $testKey = 'qz_diagnostic_test_' . time();
            $testValue = 'test_value_' . time();

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);

            $result['details']['write_read_test'] = $retrieved === $testValue ? 'passed' : 'failed';
            $result['details']['driver'] = config('cache.default');

            if ($retrieved !== $testValue) {
                $result['passed'] = false;
            }

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test storage system
     */
    protected function testStorage()
    {
        $result = [
            'name' => 'Storage System',
            'passed' => true,
            'details' => [],
        ];

        try {
            $certPath = config('qz-tray.security.certificate_path');

            // Check if storage directory exists
            $directoryExists = \Storage::exists($certPath);
            $result['details']['cert_directory'] = $directoryExists ? 'exists' : 'missing';

            // Check write permissions
            $testFile = $certPath . 'test_permissions.txt';
            \Storage::put($testFile, 'test');
            $canWrite = \Storage::exists($testFile);
            \Storage::delete($testFile);

            $result['details']['write_permissions'] = $canWrite ? 'ok' : 'failed';

            if (!$canWrite) {
                $result['passed'] = false;
            }

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test security
     */
    protected function testSecurity()
    {
        $result = [
            'name' => 'Security',
            'passed' => true,
            'details' => [],
        ];

        try {
            // Test private key
            $privateKeyPath = config('qz-tray.security.private_key');
            $result['details']['private_key'] = $privateKeyPath ? 'configured' : 'missing';

            // Test signature algorithm
            $algorithm = config('qz-tray.security.signature_algorithm');
            $result['details']['signature_algorithm'] = $algorithm;

            // Test HTTPS requirement
            $requireHttps = config('qz-tray.security.require_https');
            $result['details']['require_https'] = $requireHttps ? 'yes' : 'no';

            // Test authentication
            $middleware = config('qz-tray.middleware');
            $result['details']['authentication'] = in_array('auth', $middleware) ? 'required' : 'optional';

            if (!$privateKeyPath) {
                $result['passed'] = false;
            }

        } catch (\Exception $e) {
            $result['passed'] = false;
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Calculate overall health
     */
    protected function calculateHealth(array $tests)
    {
        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test) {
            if ($test['passed']) {
                $passed++;
            }
        }

        $score = ($passed / $total) * 100;

        return [
            'score' => round($score, 1),
            'passed' => $passed,
            'total' => $total,
            'status' => $score >= 80 ? 'healthy' : ($score >= 60 ? 'warning' : 'critical'),
            'grade' => $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 70 ? 'C' : ($score >= 60 ? 'D' : 'F'))),
        ];
    }

    /**
     * Store diagnostic result
     */
    protected function storeDiagnosticResult(array $tests, array $health)
    {
        $history = Cache::get('qz_diagnostics_history', []);

        $diagnostic = [
            'timestamp' => now(),
            'health' => $health,
            'tests' => array_map(function ($test) {
                return [
                    'name' => $test['name'],
                    'passed' => $test['passed'],
                ];
            }, $tests),
        ];

        array_unshift($history, $diagnostic);

        // Keep only last 50 diagnostics
        if (count($history) > 50) {
            $history = array_slice($history, 0, 50);
        }

        Cache::put('qz_diagnostics_history', $history, 86400 * 30); // 30 days
    }

    /**
     * Generate summary
     */
    protected function generateSummary(array $tests)
    {
        $summary = [];

        foreach ($tests as $test) {
            $summary[] = [
                'test' => $test['name'],
                'status' => $test['passed'] ? 'PASS' : 'FAIL',
                'details' => array_keys($test['details']),
            ];
        }

        return $summary;
    }
}
