<?php

namespace BitDreamIT\QzTray\Services;

use BitDreamIT\QzTray\Exceptions\QzTrayException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QzService
{
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Get certificate for QZ Tray
     */
    public function getCertificate()
    {
        $certPath = $this->config['security']['certificate_path'].'digital-certificate.txt';

        if (! Storage::exists($certPath)) {
            $this->generateCertificate();
        }

        return Storage::get($certPath);
    }

    /**
     * Generate certificate for QZ Tray
     */
    public function generateCertificate()
    {
        $certPath = $this->config['security']['certificate_path'];

        if (! Storage::exists($certPath)) {
            Storage::makeDirectory($certPath);
        }

        // Generate certificate
        $certificate = "-----BEGIN CERTIFICATE-----\n".
            base64_encode(openssl_random_pseudo_bytes(2048)).
            "\n-----END CERTIFICATE-----";

        Storage::put($certPath.'digital-certificate.txt', $certificate);

        Log::info('QZ Tray certificate generated', ['path' => $certPath]);
    }

    /**
     * Sign data for QZ Tray
     */
    public function signData(string $data)
    {
        $privateKey = $this->config['security']['private_key'];

        if (empty($privateKey)) {
            throw new QzTrayException('QZ private key not configured');
        }

        $algorithm = $this->config['security']['signature_algorithm'];

        // Sign the data
        openssl_sign($data, $signature, $privateKey, $algorithm);

        return base64_encode($signature);
    }

    /**
     * Get QZ Tray version
     */
    public function getVersion()
    {
        return '2.2.5';
    }

    /**
     * Check if QZ Tray is enabled
     */
    public function isEnabled()
    {
        return $this->config['enabled'] && $this->checkRequirements();
    }

    /**
     * Check system requirements
     */
    public function checkRequirements()
    {
        $requirements = [
            'openssl' => extension_loaded('openssl'),
            'json' => extension_loaded('json'),
            'https' => $this->config['security']['allow_localhost'] ||
                request()->secure() ||
                app()->environment('local'),
        ];

        return ! in_array(false, $requirements, true);
    }

    /**
     * Get system status
     */
    public function getSystemStatus()
    {
        return [
            'enabled' => $this->isEnabled(),
            'version' => $this->getVersion(),
            'requirements' => [
                'openssl' => extension_loaded('openssl'),
                'json' => extension_loaded('json'),
                'https' => request()->secure(),
            ],
            'config' => [
                'auto_connect' => $this->config['connection']['auto_connect'],
                'auto_discovery' => $this->config['printers']['auto_discovery'],
                'fallback_strategy' => $this->config['printers']['fallback_strategy'],
            ],
            'cache' => [
                'printers' => Cache::has('qz_printers'),
                'capabilities' => Cache::has('qz_capabilities'),
            ],
        ];
    }

    /**
     * Get diagnostics data
     */
    public function getDiagnostics()
    {
        return [
            'timestamp' => now(),
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'os' => PHP_OS,
            ],
            'qztray' => $this->getSystemStatus(),
            'memory' => [
                'usage' => memory_get_usage(true),
                'peak' => memory_get_peak_usage(true),
                'limit' => ini_get('memory_limit'),
            ],
            'session' => [
                'driver' => config('session.driver'),
                'user' => auth()->user() ? auth()->user()->id : 'guest',
            ],
        ];
    }
}
