<?php

namespace BitDreamIT\QzTray\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use BitDreamIT\QzTray\Models\SmartPrintLog;
use BitDreamIT\QzTray\Events\SmartPrintSuccess;
use BitDreamIT\QzTray\Events\SmartPrintFailed;

class SmartPrintService
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config['smart_print'] ?? [];
    }

    /**
     * Smart print with fallback
     */
    public function print(string $url, array $options = [])
    {
        $defaultOptions = [
            'type' => 'auto',
            'filename' => 'document.pdf',
            'printer' => null,
            'copies' => 1,
            'paper_size' => 'A4',
            'orientation' => 'portrait',
            'silent' => false,
            'user_id' => auth()->id(),
            'user_ip' => request()->ip(),
        ];

        $options = array_merge($defaultOptions, $options);

        // Detect file type if auto
        if ($options['type'] === 'auto') {
            $options['type'] = $this->detectFileType($url);
        }

        // Create log entry
        $log = SmartPrintLog::create([
            'user_id' => $options['user_id'],
            'url' => $url,
            'type' => $options['type'],
            'printer' => $options['printer'],
            'copies' => $options['copies'],
            'status' => 'processing',
            'ip_address' => $options['user_ip'],
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode($options),
        ]);

        try {
            // Try print methods in order
            $result = $this->tryPrintMethods($url, $options);

            // Update log
            $log->update([
                'status' => 'success',
                'method_used' => $result['method'],
                'response' => json_encode($result),
            ]);

            // Dispatch event
            event(new SmartPrintSuccess($log, $result));

            return [
                'success' => true,
                'log_id' => $log->id,
                'method' => $result['method'],
                'message' => "Printed successfully via {$result['method']}",
            ];

        } catch (\Exception $e) {
            // Update log with failure
            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            // Dispatch event
            event(new SmartPrintFailed($log, $e));

            throw $e;
        }
    }

    /**
     * Try print methods in configured order
     */
    protected function tryPrintMethods(string $url, array $options)
    {
        $fallbackOrder = $this->config['fallback_order'] ?? ['qz', 'browser', 'download'];

        foreach ($fallbackOrder as $method) {
            try {
                switch ($method) {
                    case 'qz':
                        return $this->printWithQz($url, $options);

                    case 'browser':
                        return $this->prepareForBrowserPrint($url, $options);

                    case 'download':
                        return $this->prepareForDownload($url, $options);

                    default:
                        continue;
                }
            } catch (\Exception $e) {
                Log::warning("Smart print method {$method} failed", [
                    'url' => $url,
                    'error' => $e->getMessage(),
                ]);

                // Continue to next method
                continue;
            }
        }

        throw new \Exception('All print methods failed');
    }

    /**
     * Prepare for QZ Tray printing
     */
    protected function printWithQz(string $url, array $options)
    {
        // This would be called from JavaScript side
        // Return data for JavaScript to handle
        return [
            'method' => 'qz',
            'action' => 'javascript',
            'url' => $url,
            'options' => $options,
            'instructions' => 'Use window.smartPrint() in browser',
        ];
    }

    /**
     * Prepare for browser print
     */
    protected function prepareForBrowserPrint(string $url, array $options)
    {
        // Generate a printable version if needed
        if ($options['type'] === 'pdf') {
            // For PDFs, we just return the URL
            return [
                'method' => 'browser',
                'action' => 'open',
                'url' => $url,
                'options' => $options,
            ];
        } elseif ($options['type'] === 'html') {
            // For HTML, we might want to add print-specific CSS
            return [
                'method' => 'browser',
                'action' => 'open',
                'url' => $url . '?print=1', // Assume your route handles ?print=1
                'options' => $options,
            ];
        } else {
            return [
                'method' => 'browser',
                'action' => 'download', // Fallback to download for other types
                'url' => $url,
                'filename' => $options['filename'],
                'options' => $options,
            ];
        }
    }

    /**
     * Prepare for download
     */
    protected function prepareForDownload(string $url, array $options)
    {
        return [
            'method' => 'download',
            'action' => 'download',
            'url' => $url,
            'filename' => $options['filename'],
            'options' => $options,
        ];
    }

    /**
     * Detect file type from URL
     */
    protected function detectFileType(string $url): string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        $typeMap = [
            'pdf' => 'pdf',
            'html' => 'html',
            'htm' => 'html',
            'jpg' => 'image',
            'jpeg' => 'image',
            'png' => 'image',
            'gif' => 'image',
            'bmp' => 'image',
            'svg' => 'image',
            'txt' => 'raw',
            'text' => 'raw',
            'log' => 'raw',
            'zpl' => 'raw',
            'epl' => 'raw',
            'esc' => 'raw',
        ];

        return $typeMap[strtolower($extension)] ?? 'html';
    }

    /**
     * Get smart print statistics
     */
    public function getStatistics(array $filters = [])
    {
        $query = SmartPrintLog::query();

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $total = $query->count();
        $success = $query->where('status', 'success')->count();
        $failed = $query->where('status', 'failed')->count();

        $methods = $query->whereNotNull('method_used')
            ->selectRaw('method_used, COUNT(*) as count')
            ->groupBy('method_used')
            ->pluck('count', 'method_used')
            ->toArray();

        return [
            'total' => $total,
            'success' => $success,
            'failed' => $failed,
            'success_rate' => $total > 0 ? round(($success / $total) * 100, 2) : 0,
            'methods' => $methods,
            'recent' => $query->latest()->limit(10)->get(),
        ];
    }

    /**
     * Generate smart print button HTML
     */
    public function generateButton(string $url, array $options = []): string
    {
        $defaults = [
            'class' => 'smart-print',
            'title' => 'Print',
            'icon' => 'fas fa-print',
            'text' => '',
            'attributes' => [],
        ];

        $options = array_merge($defaults, $options);

        $attributes = array_merge([
            'class' => $options['class'],
            'title' => $options['title'],
            'data-url' => $url,
            'data-type' => $options['type'] ?? 'auto',
        ], $options['attributes']);

        if (!empty($options['filename'])) {
            $attributes['data-filename'] = $options['filename'];
        }

        if (!empty($options['printer'])) {
            $attributes['data-printer'] = $options['printer'];
        }

        if (!empty($options['copies']) && $options['copies'] > 1) {
            $attributes['data-copies'] = $options['copies'];
        }

        if (!empty($options['silent']) && $options['silent']) {
            $attributes['data-silent'] = 'true';
        }

        $html = '<a ';
        foreach ($attributes as $key => $value) {
            $html .= $key . '="' . e($value) . '" ';
        }
        $html .= '>';

        if ($options['icon']) {
            $html .= '<i class="' . e($options['icon']) . '"></i> ';
        }

        if ($options['text']) {
            $html .= e($options['text']);
        }

        $html .= '</a>';

        return $html;
    }
}
