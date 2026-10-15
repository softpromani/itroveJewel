<?php

namespace App\Console\Commands;

use App\Services\UsbLockService;
use Illuminate\Console\Command;

class UsbInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usb:install {--admin=Administrator : Name of the installing administrator} {--force : Overwrite existing lock}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and cryptographically lock the software to the current physical USB drive hardware serial number';

    /**
     * Execute the console command.
     */
    public function handle(UsbLockService $usbLockService): int
    {
        $this->info('Starting USB Hardware Serial Lock Installation...');

        if ($usbLockService->isInstalled() && ! $this->option('force')) {
            $lockData = $usbLockService->getLockData();
            $this->error('Application is ALREADY locked to serial: '.($lockData['locked_serial'] ?? 'UNKNOWN'));
            $this->line('Use --force to overwrite the existing lock.');

            return 1;
        }

        $hw = $usbLockService->getHardwareInfo();

        if (! $hw['found'] || empty($hw['serial_number'])) {
            $this->error('Could not detect physical hardware serial number: '.$hw['error']);

            return 1;
        }

        $this->line('Detected Hardware:');
        $this->line('  - Model: '.$hw['model']);
        $this->line('  - Drive: '.$hw['drive_letter'].':');
        $this->line('  - Hardware Serial: '.$hw['serial_number']);
        $this->line('  - Bus Type: '.$hw['bus_type']);

        $result = $usbLockService->installAndLock([
            'admin_name' => $this->option('admin'),
        ]);

        if ($result['success']) {
            $this->info('SUCCESS: '.$result['message']);

            return 0;
        }

        $this->error('FAILED: '.$result['message']);

        return 1;
    }
}
