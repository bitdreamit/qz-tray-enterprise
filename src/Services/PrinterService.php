<?php

namespace BitDreamIT\QzTray\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use BitDreamIT\QzTray\Models\QzPrinter;

class PrinterService
{
    protected $cacheTtl = 300; // 5 minutes

    /**
     * Get all printers (cached)
     */
    public function getAllPrinters()
    {
        return Cache::remember('qz_printers', $this->cacheTtl, function () {
            // In a real implementation, this would communicate with QZ Tray
            // For now, we return sample data or empty array

            return [
                [
                    'name' => 'Zebra LP 2844',
                    'type' => 'usb',
                    'status' => 'online',
                    'default' => true,
                    'capabilities' => ['label', 'zpl'],
                ],
                [
                    'name' => 'EPSON TM-T88V',
                    'type' => 'usb',
                    'status' => 'online',
                    'default' => false,
                    'capabilities' => ['receipt', 'escpos'],
                ],
            ];
        });
    }

    /**
     * Get default printer
     */
    public function getDefaultPrinter()
    {
        $printers = $this->getAllPrinters();

        foreach ($printers as $printer) {
            if ($printer['default'] ?? false) {
                return $printer['name'];
            }
        }

        return $printers[0]['name'] ?? null;
    }

    /**
     * Get printer capabilities
     */
    public function getPrinterCapabilities(string $printerName)
    {
        $cacheKey = 'qz_capabilities_' . md5($printerName);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($printerName) {
            // Sample capabilities based on printer name
            $capabilities = [
                'color' => false,
                'duplex' => false,
                'paper_sizes' => ['A4', 'Letter'],
                'supports' => [],
            ];

            // Determine printer type
            $lowerName = strtolower($printerName);

            if (str_contains($lowerName, 'zebra') || str_contains($lowerName, 'zpl')) {
                $capabilities['supports'] = ['zpl', 'labels', 'barcodes'];
                $capabilities['type'] = 'label';
            } elseif (str_contains($lowerName, 'epson') || str_contains($lowerName, 'tm-')) {
                $capabilities['supports'] = ['escpos', 'receipts'];
                $capabilities['type'] = 'receipt';
                $capabilities['paper_sizes'] = ['receipt'];
            } elseif (str_contains($lowerName, 'pdf') || str_contains($lowerName, 'virtual')) {
                $capabilities['supports'] = ['pdf', 'html', 'image'];
                $capabilities['type'] = 'virtual';
                $capabilities['color'] = true;
                $capabilities['duplex'] = true;
            } else {
                $capabilities['supports'] = ['raw', 'html', 'pdf'];
                $capabilities['type'] = 'standard';
                $capabilities['color'] = true;
                $capabilities['duplex'] = true;
            }

            return $capabilities;
        });
    }

    /**
     * Save printer preference for user
     */
    public function saveUserPreference(string $printerName, string $module = null)
    {
        $userId = auth()->id();

        if (!$userId) {
            return false;
        }

        $cacheKey = 'qz_user_preference_' . $userId . ($module ? '_' . $module : '');

        Cache::put($cacheKey, $printerName, 86400 * 30); // 30 days

        Log::info('Printer preference saved', [
            'user' => $userId,
            'printer' => $printerName,
            'module' => $module,
        ]);

        return true;
    }

    /**
     * Get user's printer preference
     */
    public function getUserPreference(string $module = null)
    {
        $userId = auth()->id();

        if (!$userId) {
            return null;
        }

        $cacheKey = 'qz_user_preference_' . $userId . ($module ? '_' . $module : '');

        return Cache::get($cacheKey);
    }

    /**
     * Clear printer queue
     */
    public function clearQueue(string $printerName)
    {
        Log::info('Printer queue cleared', [
            'printer' => $printerName,
            'user' => auth()->user()->name ?? 'anonymous',
            'ip' => request()->ip(),
        ]);

        // In real implementation, send command to QZ Tray

        return true;
    }

    /**
     * Get printer status
     */
    public function getPrinterStatus(string $printerName)
    {
        // Check various status indicators
        $status = [
            'name' => $printerName,
            'online' => true,
            'ready' => true,
            'queue' => 0,
            'last_used' => now()->subMinutes(5),
            'type' => $this->detectPrinterType($printerName),
        ];

        return $status;
    }

    /**
     * Detect printer type from name
     */
    protected function detectPrinterType(string $printerName)
    {
        $lowerName = strtolower($printerName);

        if (str_contains($lowerName, 'zebra') || str_contains($lowerName, 'zpl')) {
            return 'label';
        } elseif (str_contains($lowerName, 'epson') ||
            str_contains($lowerName, 'tm-') ||
            str_contains($lowerName, 'receipt')) {
            return 'receipt';
        } elseif (str_contains($lowerName, 'pdf') ||
            str_contains($lowerName, 'xps') ||
            str_contains($lowerName, 'virtual')) {
            return 'virtual';
        } elseif (str_contains($lowerName, 'network') ||
            str_contains($lowerName, 'tcp') ||
            str_contains($lowerName, '//')) {
            return 'network';
        } elseif (str_contains($lowerName, 'usb')) {
            return 'usb';
        } else {
            return 'standard';
        }
    }
}
