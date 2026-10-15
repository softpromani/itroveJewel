<?php

namespace App\Console\Commands;

use App\Services\UsbLockService;
use Illuminate\Console\Command;

class UsbStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'usb:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display USB Hardware Lock status, detected serial number, and security verification';

    /**
     * Execute the console command.
     */
    public function handle(UsbLockService $usbLockService): int
    {
        $this->info('====================================================');
        $this->info('  ITROVEJEWEL - USB HARDWARE SERIAL LOCK STATUS');
        $this->info('====================================================');

        $hw = $usbLockService->getHardwareInfo();
        $verification = $usbLockService->verifyLock($hw);

        $this->table(
            ['Property', 'Detected Value'],
            [
                ['Drive Letter', $hw['drive_letter'].':'],
                ['Device Model', $hw['model']],
                ['Bus / Interface Type', $hw['bus_type'].($hw['is_usb'] ? ' (USB REMOVABLE)' : '')],
                ['Physical Hardware Serial', $hw['serial_number'] ?: '<NONE DETECTED>'],
                ['Disk Capacity', $hw['size_formatted']],
                ['Lock Status', $verification['status']],
                ['Locked Serial Target', $verification['locked_serial'] ?? '<NOT CONFIGURED>'],
                ['Is Authorized to Run', $verification['is_authorized'] ? 'YES (UNLOCKED)' : 'NO (ACCESS DENIED)'],
            ]
        );

        $this->line('');
        if ($verification['status'] === 'ACTIVE') {
            $this->info('Status: ACTIVE & VERIFIED. Bound to this physical hardware.');
        } elseif ($verification['status'] === 'NOT_INSTALLED') {
            $this->warn('Status: NOT INSTALLED. Run `php artisan usb:install` or open the application to complete first-time installation.');
        } else {
            $this->error('Status: '.$verification['status'].' - '.$verification['message']);
        }
        $this->line('');

        return $verification['is_authorized'] ? 0 : 1;
    }
}
