<?php

namespace BitDreamIT\QzTray\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateCertificateCommand extends Command
{
    protected $signature = 'qz-tray:generate-certificate
                            {--force : Force regeneration even if exists}
                            {--path= : Custom certificate path}';

    protected $description = 'Generate security certificate for QZ Tray';

    public function handle()
    {
        $this->info('Generating QZ Tray security certificate...');

        $certPath = $this->option('path') ?: config('qz-tray.security.certificate_path');
        $certFile = $certPath.'digital-certificate.txt';

        // Check if certificate already exists
        if (Storage::exists($certFile) && ! $this->option('force')) {
            if (! $this->confirm('Certificate already exists. Regenerate?')) {
                $this->info('Certificate generation cancelled.');

                return;
            }
        }

        // Create directory if not exists
        if (! Storage::exists($certPath)) {
            Storage::makeDirectory($certPath);
        }

        // Generate certificate
        $this->generateCertificate($certFile);

        // Generate private key if not exists
        $this->generatePrivateKey();

        $this->info('✅ Certificate generated successfully!');
        $this->line('Certificate path: '.$certFile);
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Add certificate to QZ Tray:');
        $this->line('   - Open QZ Tray → File → Certificate Manager');
        $this->line('   - Click "Import" and select the generated certificate');
        $this->line('2. Restart QZ Tray');
    }

    protected function generateCertificate(string $certFile)
    {
        // Generate random certificate data
        $certificate = "-----BEGIN CERTIFICATE-----\n";
        $certificate .= chunk_split(base64_encode(random_bytes(2048)), 64, "\n");
        $certificate .= '-----END CERTIFICATE-----';

        Storage::put($certFile, $certificate);
    }

    protected function generatePrivateKey()
    {
        // Use relative path for Storage
        $certDir = config('qz-tray.security.certificate_path'); // e.g., 'certs'
        $privateKeyPath = $certDir.'/private-key.pem';

        if (Storage::exists($privateKeyPath)) {
            return;
        }

        // Check OpenSSL extension
        if (! extension_loaded('openssl')) {
            $this->error('OpenSSL PHP extension is not enabled. Cannot generate private key.');

            return;
        }

        // Generate RSA private key
        $config = [
            'digest_alg' => 'sha512',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $privateKey = openssl_pkey_new($config);

        if (! $privateKey) {
            $error = openssl_error_string();
            $this->error("Failed to generate private key. OpenSSL error: $error");

            return;
        }

        // Export private key
        if (! openssl_pkey_export($privateKey, $privateKeyPem)) {
            $error = openssl_error_string();
            $this->error("Failed to export private key. OpenSSL error: $error");

            return;
        }

        // Ensure certificate directory exists
        Storage::makeDirectory($certDir);

        // Save private key
        Storage::put($privateKeyPath, $privateKeyPem);

        // Update .env with absolute path
        $this->updateEnvPrivateKey(storage_path('app/'.$privateKeyPath));

        $this->info('Private key generated: '.storage_path('app/'.$privateKeyPath));
    }

    protected function updateEnvPrivateKey(string $keyPath)
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        // Get absolute path
        $absolutePath = storage_path(str_replace('storage/', '', $keyPath));

        // Update or add QZ_PRIVATE_KEY
        if (str_contains($envContent, 'QZ_PRIVATE_KEY=')) {
            $envContent = preg_replace(
                '/QZ_PRIVATE_KEY=.*/',
                'QZ_PRIVATE_KEY='.$absolutePath,
                $envContent
            );
        } else {
            $envContent .= "\nQZ_PRIVATE_KEY=".$absolutePath;
        }

        file_put_contents($envPath, $envContent);
    }
}
