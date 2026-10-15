<?php

namespace App\Console\Commands;

use App\Services\UsbLockService;
use Illuminate\Console\Command;

class UsbResetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usb:reset {--force : Confirm reset without prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset the USB Hardware Lock, returning the application to first-time setup state';

    /**
     * Execute the console command.
     */
    public function handle(UsbLockService $usbLockService): int
    {
        if (! $this->option('force')) {
            if (! $this->confirm('Are you sure you want to remove the USB Hardware Serial Lock? This will revert the app to uninstalled mode.')) {
                $this->line('Reset cancelled.');

                return 0;
            }
        }

        $reset = $usbLockService->resetLock();

        if ($reset) {
            $this->info('USB Hardware Lock has been successfully removed. Application is now in first-time install mode.');
        } else {
            $this->warn('No lock file was found to remove.');
        }

        return 0;
    }
}
