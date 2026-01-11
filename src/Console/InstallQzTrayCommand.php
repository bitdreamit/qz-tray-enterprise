<?php

namespace BitDreamIT\QzTray\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallQzTrayCommand extends Command
{
    protected $signature = 'qz-tray:install';
    protected $description = 'Install QZ Tray Enterprise package';

    public function handle()
    {
        $this->info('Installing QZ Tray Enterprise Package...');

        // Publish config
        $this->call('vendor:publish', [
            '--tag' => 'qz-tray-config',
            '--force' => true,
        ]);

        // Publish assets
        $this->call('vendor:publish', [
            '--tag' => 'qz-tray-assets',
            '--force' => true,
        ]);

        // Publish views
        $this->call('vendor:publish', [
            '--tag' => 'qz-tray-views',
            '--force' => true,
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--tag' => 'qz-tray-migrations',
            '--force' => true,
        ]);

        // Run migrations
        if ($this->confirm('Run migrations?', true)) {
            $this->call('migrate');
        }

        // Generate certificate
        if ($this->confirm('Generate security certificate?', true)) {
            $this->call('qz-tray:generate-certificate');
        }

        // Update .env file
        $this->updateEnvFile();

        $this->info('✅ QZ Tray Enterprise installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Install QZ Tray on client machines: https://qz.io/download/');
        $this->line('2. Configure HTTPS for your application');
        $this->line('3. Visit ' . url(config('qz-tray.ui.dashboard_route', '/qz-tray/dashboard')));
        $this->line('4. Run diagnostics to verify installation');
    }

    protected function updateEnvFile()
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            return;
        }

        $envContent = file_get_contents($envPath);

        $envVariables = [
            'QZ_TRAY_ENABLED' => 'true',
            'QZ_REQUIRE_HTTPS' => 'true',
            'QZ_ALLOW_LOCALHOST' => 'true',
            'QZ_AUTO_CONNECT' => 'true',
            'QZ_AUTO_DISCOVERY' => 'true',
            'QZ_FALLBACK_STRATEGY' => 'browser-print',
            'QZ_DEFAULT_PAPER_SIZE' => 'A4',
            'QZ_LOGGING_ENABLED' => 'true',
        ];

        foreach ($envVariables as $key => $value) {
            if (!str_contains($envContent, $key . '=')) {
                $envContent .= "\n{$key}={$value}";
            }
        }

        file_put_contents($envPath, $envContent);
    }
}
