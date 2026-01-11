<?php

namespace BitDreamIT\QzTray\Exceptions;

use Exception;

class QzTrayException extends Exception
{
    /**
     * Error codes
     */
    const ERROR_QZ_NOT_INSTALLED = 1001;
    const ERROR_CONNECTION_FAILED = 1002;
    const ERROR_SECURITY_FAILED = 1003;
    const ERROR_PRINTER_NOT_FOUND = 1004;
    const ERROR_PRINT_FAILED = 1005;
    const ERROR_CERTIFICATE_MISSING = 1006;
    const ERROR_SIGNATURE_FAILED = 1007;
    const ERROR_CONFIGURATION_INVALID = 1008;
    const ERROR_PERMISSION_DENIED = 1009;
    const ERROR_HTTPS_REQUIRED = 1010;

    /**
     * Error messages
     */
    protected $messages = [
        self::ERROR_QZ_NOT_INSTALLED => 'QZ Tray is not installed or not running.',
        self::ERROR_CONNECTION_FAILED => 'Failed to connect to QZ Tray.',
        self::ERROR_SECURITY_FAILED => 'Security certificate or signature verification failed.',
        self::ERROR_PRINTER_NOT_FOUND => 'Printer not found or not accessible.',
        self::ERROR_PRINT_FAILED => 'Print job failed to execute.',
        self::ERROR_CERTIFICATE_MISSING => 'Security certificate is missing or invalid.',
        self::ERROR_SIGNATURE_FAILED => 'Failed to sign the request.',
        self::ERROR_CONFIGURATION_INVALID => 'QZ Tray configuration is invalid.',
        self::ERROR_PERMISSION_DENIED => 'Permission denied to access QZ Tray features.',
        self::ERROR_HTTPS_REQUIRED => 'HTTPS is required for QZ Tray connection.',
    ];

    /**
     * Additional context data
     */
    protected $context = [];

    public function __construct(int $code = 0, array $context = [], Exception $previous = null)
    {
        $message = $this->messages[$code] ?? 'An unknown QZ Tray error occurred.';

        parent::__construct($message, $code, $previous);

        $this->context = $context;
    }

    /**
     * Get context data
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create QZ not installed exception
     */
    public static function qzNotInstalled(array $context = []): self
    {
        return new self(self::ERROR_QZ_NOT_INSTALLED, $context);
    }

    /**
     * Create connection failed exception
     */
    public static function connectionFailed(array $context = []): self
    {
        return new self(self::ERROR_CONNECTION_FAILED, $context);
    }

    /**
     * Create security failed exception
     */
    public static function securityFailed(array $context = []): self
    {
        return new self(self::ERROR_SECURITY_FAILED, $context);
    }

    /**
     * Create printer not found exception
     */
    public static function printerNotFound(string $printerName, array $context = []): self
    {
        $context['printer'] = $printerName;
        return new self(self::ERROR_PRINTER_NOT_FOUND, $context);
    }

    /**
     * Create print failed exception
     */
    public static function printFailed(array $context = []): self
    {
        return new self(self::ERROR_PRINT_FAILED, $context);
    }

    /**
     * Create certificate missing exception
     */
    public static function certificateMissing(array $context = []): self
    {
        return new self(self::ERROR_CERTIFICATE_MISSING, $context);
    }

    /**
     * Create signature failed exception
     */
    public static function signatureFailed(array $context = []): self
    {
        return new self(self::ERROR_SIGNATURE_FAILED, $context);
    }

    /**
     * Create configuration invalid exception
     */
    public static function configurationInvalid(array $context = []): self
    {
        return new self(self::ERROR_CONFIGURATION_INVALID, $context);
    }

    /**
     * Create permission denied exception
     */
    public static function permissionDenied(array $context = []): self
    {
        return new self(self::ERROR_PERMISSION_DENIED, $context);
    }

    /**
     * Create HTTPS required exception
     */
    public static function httpsRequired(array $context = []): self
    {
        return new self(self::ERROR_HTTPS_REQUIRED, $context);
    }

    /**
     * Convert to array for API response
     */
    public function toArray(): array
    {
        return [
            'error' => true,
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'context' => $this->context,
        ];
    }
}
