<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class UsbLockService
{
    /**
     * Secret salt used for HMAC digital signature of the lock token.
     */
    private const SECRET_SALT = 'ITROVE_JEWEL_USB_HW_LOCK_SECURE_SALT_v1_!#%';

    /**
     * Get the drive letter where the application is currently located.
     */
    public function getCurrentDriveLetter(): string
    {
        $base = base_path();

        if (preg_match('/^([a-zA-Z]):/', $base, $matches)) {
            return strtoupper($matches[1]);
        }

        return 'D';
    }

    /**
     * Cached hardware info during the current request lifecycle.
     *
     * @var array<string, mixed>|null
     */
    private ?array $cachedHardwareInfo = null;

    /**
     * Optional fake hardware info used during unit/feature testing.
     *
     * @var array<string, mixed>|null
     */
    public static ?array $fakeHardwareInfo = null;

    /**
     * Set fake hardware information for testing.
     *
     * @param  array<string, mixed>|null  $hardwareInfo
     */
    public static function fake(?array $hardwareInfo = null): void
    {
        self::$fakeHardwareInfo = $hardwareInfo ?? [
            'found' => true,
            'drive_letter' => 'E',
            'model' => 'SanDisk Ultra USB 3.0',
            'serial_number' => 'SANDISK_HW_12345678',
            'bus_type' => 'USB',
            'is_usb' => true,
            'unique_id' => 'USB\\VID_0781&PID_5581\\SANDISK_HW_12345678',
            'size' => 64000000000,
            'size_formatted' => '59.6 GB',
            'error' => null,
        ];
    }

    /**
     * Clear any active fake hardware info.
     */
    public static function resetFake(): void
    {
        self::$fakeHardwareInfo = null;
    }

    /**
     * Retrieve physical hardware information for the given or current drive letter.
     *
     * @return array{
     *     found: bool,
     *     drive_letter: string,
     *     model: string,
     *     serial_number: string,
     *     bus_type: string,
     *     is_usb: bool,
     *     unique_id: string,
     *     size: int,
     *     size_formatted: string,
     *     error: ?string
     * }
     */
    public function getHardwareInfo(?string $driveLetter = null, bool $forceRefresh = false): array
    {
        if (self::$fakeHardwareInfo !== null) {
            return self::$fakeHardwareInfo;
        }

        if (! $forceRefresh && $driveLetter === null && $this->cachedHardwareInfo !== null) {
            return $this->cachedHardwareInfo;
        }
        $driveLetter = $driveLetter ? strtoupper(substr($driveLetter, 0, 1)) : $this->getCurrentDriveLetter();

        $defaultInfo = [
            'found' => false,
            'drive_letter' => $driveLetter,
            'model' => 'Unknown Storage Device',
            'serial_number' => '',
            'bus_type' => 'Unknown',
            'is_usb' => false,
            'unique_id' => '',
            'size' => 0,
            'size_formatted' => '0 GB',
            'error' => null,
        ];

        // Only Windows is supported for WMI/PowerShell hardware detection
        if (PHP_OS_FAMILY !== 'Windows') {
            $defaultInfo['error'] = 'Hardware serial detection requires a Windows environment.';

            return $defaultInfo;
        }

        $psScript = <<<'POWERSHELL'
$ProgressPreference = 'SilentlyContinue'
$drive = "DRIVE_LETTER_PLACEHOLDER"

# 1. Primary method: Get-Partition piped into Get-Disk
try {
    $part = Get-Partition -DriveLetter $drive -ErrorAction SilentlyContinue
    if ($part) {
        $disk = $part | Get-Disk -ErrorAction SilentlyContinue
        if ($disk -and $disk.SerialNumber) {
            $serial = ("" + $disk.SerialNumber).Trim()
            if ($serial.Length -gt 0) {
                [PSCustomObject]@{
                    Found = $true
                    DriveLetter = $drive
                    Model = ("" + $disk.FriendlyName).Trim()
                    SerialNumber = $serial
                    BusType = ("" + $disk.BusType).Trim()
                    UniqueId = ("" + $disk.UniqueId).Trim()
                    Size = [int64]$disk.Size
                } | ConvertTo-Json -Compress
                exit
            }
        }
    }
} catch {}

# 2. Secondary fallback: WMI CIM Association
try {
    $query = "ASSOCIATORS OF {Win32_LogicalDisk.DeviceID='$($drive):'} WHERE AssocClass = Win32_LogicalDiskToPartition"
    $wmiDisk = Get-CimInstance -Query $query -ErrorAction SilentlyContinue | ForEach-Object {
        Get-CimInstance -Query "ASSOCIATORS OF {Win32_DiskPartition.DeviceID='$($_.DeviceID)'} WHERE AssocClass = Win32_DiskDriveToDiskPartition" -ErrorAction SilentlyContinue
    } | Select-Object -First 1

    if ($wmiDisk -and $wmiDisk.SerialNumber) {
        $serial = ("" + $wmiDisk.SerialNumber).Trim()
        if ($serial.Length -gt 0) {
            [PSCustomObject]@{
                Found = $true
                DriveLetter = $drive
                Model = ("" + $wmiDisk.Model).Trim()
                SerialNumber = $serial
                BusType = ("" + $wmiDisk.InterfaceType).Trim()
                UniqueId = ("" + $wmiDisk.PNPDeviceID).Trim()
                Size = [int64]$wmiDisk.Size
            } | ConvertTo-Json -Compress
            exit
        }
    }
} catch {}

# 3. Tertiary fallback: Volume Serial Number
try {
    $vol = Get-CimInstance -ClassName Win32_LogicalDisk -Filter "DeviceID='$($drive):'" -ErrorAction SilentlyContinue
    if ($vol -and $vol.VolumeSerialNumber) {
        [PSCustomObject]@{
            Found = $true
            DriveLetter = $drive
            Model = ("" + $vol.VolumeName).Trim() + " Volume"
            SerialNumber = ("VOL-" + $vol.VolumeSerialNumber).Trim()
            BusType = "Removable"
            UniqueId = ("" + $vol.DeviceID).Trim()
            Size = [int64]$vol.Size
        } | ConvertTo-Json -Compress
        exit
    }
} catch {}

[PSCustomObject]@{
    Found = $false
    DriveLetter = $drive
} | ConvertTo-Json -Compress
POWERSHELL;

        $psScript = str_replace('DRIVE_LETTER_PLACEHOLDER', $driveLetter, $psScript);
        $utf16 = mb_convert_encoding($psScript, 'UTF-16LE', 'UTF-8');
        $b64 = base64_encode($utf16);
        $cmd = "powershell.exe -NoProfile -NonInteractive -ExecutionPolicy Bypass -EncodedCommand $b64";

        try {
            $output = shell_exec($cmd);

            if (! $output) {
                $defaultInfo['error'] = 'No output returned from hardware detection command.';

                return $defaultInfo;
            }

            // Extract the JSON portion if there's any stray console text
            if (preg_match('/\{.*\}/s', trim($output), $matches)) {
                $decoded = json_decode($matches[0], true);

                if (is_array($decoded) && ! empty($decoded['Found']) && ! empty($decoded['SerialNumber'])) {
                    $busType = $decoded['BusType'] ?? 'Unknown';
                    $isUsb = stripos($busType, 'USB') !== false || stripos($busType, 'Removable') !== false;
                    $size = (int) ($decoded['Size'] ?? 0);

                    $info = [
                        'found' => true,
                        'drive_letter' => $decoded['DriveLetter'] ?? $driveLetter,
                        'model' => $decoded['Model'] ?? 'Storage Device',
                        'serial_number' => $decoded['SerialNumber'],
                        'bus_type' => $busType,
                        'is_usb' => $isUsb,
                        'unique_id' => $decoded['UniqueId'] ?? '',
                        'size' => $size,
                        'size_formatted' => $this->formatBytes($size),
                        'error' => null,
                    ];

                    if ($driveLetter === $this->getCurrentDriveLetter()) {
                        $this->cachedHardwareInfo = $info;
                    }

                    return $info;
                }
            }

            $defaultInfo['error'] = 'Could not parse physical serial number for drive '.$driveLetter;
        } catch (Exception $e) {
            $defaultInfo['error'] = $e->getMessage();
            Log::error('UsbLockService hardware detection failed: '.$e->getMessage());
        }

        return $defaultInfo;
    }

    /**
     * Get candidate file paths where the lock token is stored.
     *
     * @return array<int, string>
     */
    public function getLockFilePaths(): array
    {
        $paths = [];

        // 1. Packaged portable root: USB_READY_ItroveJewel/data/.usb_hardware_lock.json
        $portableDataDir = dirname(dirname(dirname(base_path()))).DIRECTORY_SEPARATOR.'data';
        if (File::isDirectory($portableDataDir) || File::exists($portableDataDir.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json')) {
            $paths[] = $portableDataDir.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json';
        }

        // 2. Base project data directory: base_path('data/.usb_hardware_lock.json')
        $baseDataDir = base_path('data');
        if (File::isDirectory($baseDataDir) || File::exists($baseDataDir.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json')) {
            $paths[] = $baseDataDir.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json';
        }

        // 3. Storage framework fallback: storage_path('framework/.usb_hardware_lock.json')
        $paths[] = storage_path('framework'.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json');

        // 4. Default project data path if none existed yet
        if (! in_array(base_path('data'.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json'), $paths, true)) {
            $paths[] = base_path('data'.DIRECTORY_SEPARATOR.'.usb_hardware_lock.json');
        }

        return array_unique($paths);
    }

    /**
     * Check if the application has already been installed & locked to a USB.
     */
    public function isInstalled(): bool
    {
        $lockData = $this->getLockData();

        return $lockData !== null && ! empty($lockData['locked_serial']);
    }

    /**
     * Read and return the saved lock data, if any.
     *
     * @return array<string, mixed>|null
     */
    public function getLockData(): ?array
    {
        foreach ($this->getLockFilePaths() as $filePath) {
            if (File::exists($filePath)) {
                $content = File::get($filePath);
                $data = json_decode($content, true);

                if (is_array($data) && ! empty($data['locked_serial'])) {
                    return $data;
                }
            }
        }

        return null;
    }

    /**
     * Generate an HMAC cryptographic digital signature for the lock parameters.
     */
    public function generateSignature(string $serialNumber, string $uniqueId, string $installedAt): string
    {
        $appKey = config('app.key', 'base64:CDeoIyd2H/tq6mXpmlu/jMd1V7bbFBzN7RRD2oUhs3s=');
        $payload = implode('::', [
            trim($serialNumber),
            trim($uniqueId),
            trim($installedAt),
            self::SECRET_SALT,
        ]);

        return hash_hmac('sha256', $payload, $appKey);
    }

    /**
     * Install and permanently bind this software copy to the current USB drive hardware serial.
     *
     * @param  array<string, mixed>  $options
     * @return array{
     *     success: bool,
     *     message: string,
     *     lock_data?: array<string, mixed>
     * }
     */
    public function installAndLock(array $options = []): array
    {
        $hw = $this->getHardwareInfo();

        if (! $hw['found'] || empty($hw['serial_number'])) {
            return [
                'success' => false,
                'message' => 'Unable to detect a valid hardware serial number for drive '.$hw['drive_letter'].': '.$hw['error'],
            ];
        }

        $now = now()->toISOString();
        $serial = trim($hw['serial_number']);
        $uniqueId = trim($hw['unique_id']);
        $signature = $this->generateSignature($serial, $uniqueId, $now);

        $lockData = [
            'status' => 'LOCKED',
            'locked_serial' => $serial,
            'disk_model' => $hw['model'],
            'bus_type' => $hw['bus_type'],
            'drive_letter' => $hw['drive_letter'],
            'unique_id' => $uniqueId,
            'disk_size' => $hw['size'],
            'installed_at' => $now,
            'installed_by' => $options['admin_name'] ?? 'Authorized Administrator',
            'app_name' => config('app.name', 'ItroveJewel'),
            'signature' => $signature,
        ];

        $jsonPayload = json_encode($lockData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // Save lock file across all target paths
        $saved = false;
        foreach ($this->getLockFilePaths() as $filePath) {
            $dir = dirname($filePath);
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true, true);
            }

            File::put($filePath, $jsonPayload);
            $saved = true;
        }

        if (! $saved) {
            return [
                'success' => false,
                'message' => 'Failed to write lock configuration file to storage.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Software successfully locked to USB Hardware Serial: '.$serial,
            'lock_data' => $lockData,
        ];
    }

    /**
     * Perform the comprehensive Hardware Serial Lock verification.
     *
     * @return array{
     *     status: 'NOT_INSTALLED'|'ACTIVE'|'LOCKOUT'|'TAMPERED',
     *     is_authorized: bool,
     *     current_drive: string,
     *     current_serial: string,
     *     current_model: string,
     *     locked_serial: ?string,
     *     locked_model: ?string,
     *     installed_at: ?string,
     *     message: string
     * }
     */
    public function verifyLock(?array $currentHw = null): array
    {
        $currentHw = $currentHw ?? $this->getHardwareInfo();
        $lockData = $this->getLockData();

        // 1. First-time setup required
        if (! $lockData || empty($lockData['locked_serial'])) {
            return [
                'status' => 'NOT_INSTALLED',
                'is_authorized' => false,
                'current_drive' => $currentHw['drive_letter'],
                'current_serial' => $currentHw['serial_number'],
                'current_model' => $currentHw['model'],
                'locked_serial' => null,
                'locked_model' => null,
                'installed_at' => null,
                'message' => 'Software not yet installed. First-time hardware binding required.',
            ];
        }

        $lockedSerial = trim($lockData['locked_serial'] ?? '');
        $uniqueId = trim($lockData['unique_id'] ?? '');
        $installedAt = trim($lockData['installed_at'] ?? '');
        $savedSignature = trim($lockData['signature'] ?? '');

        // 2. Cryptographic signature check (detect file tampering)
        $expectedSignature = $this->generateSignature($lockedSerial, $uniqueId, $installedAt);
        if (! hash_equals($expectedSignature, $savedSignature)) {
            return [
                'status' => 'TAMPERED',
                'is_authorized' => false,
                'current_drive' => $currentHw['drive_letter'],
                'current_serial' => $currentHw['serial_number'],
                'current_model' => $currentHw['model'],
                'locked_serial' => $lockedSerial,
                'locked_model' => $lockData['disk_model'] ?? 'Unknown',
                'installed_at' => $installedAt,
                'message' => 'CRITICAL SECURITY ALERT: Hardware lock signature is invalid or tampered with!',
            ];
        }

        // 3. Hardware Serial comparison
        $currentSerial = trim($currentHw['serial_number']);

        if (empty($currentSerial) || strcasecmp($currentSerial, $lockedSerial) !== 0) {
            return [
                'status' => 'LOCKOUT',
                'is_authorized' => false,
                'current_drive' => $currentHw['drive_letter'],
                'current_serial' => $currentSerial ?: 'NOT_DETECTED',
                'current_model' => $currentHw['model'],
                'locked_serial' => $lockedSerial,
                'locked_model' => $lockData['disk_model'] ?? 'Authorized USB Drive',
                'installed_at' => $installedAt,
                'message' => 'UNAUTHORIZED HARDWARE: This software is locked to USB Serial ['.$lockedSerial.']. Current drive serial: ['.($currentSerial ?: 'NONE').']. Copying is prohibited.',
            ];
        }

        // 4. Authorized and Active
        return [
            'status' => 'ACTIVE',
            'is_authorized' => true,
            'current_drive' => $currentHw['drive_letter'],
            'current_serial' => $currentSerial,
            'current_model' => $currentHw['model'],
            'locked_serial' => $lockedSerial,
            'locked_model' => $lockData['disk_model'] ?? 'Authorized USB Drive',
            'installed_at' => $installedAt,
            'message' => 'Hardware verification verified. Authorized USB drive confirmed.',
        ];
    }

    /**
     * Clear / reset the lock token (used for administrative resets or testing).
     */
    public function resetLock(): bool
    {
        $deleted = false;
        foreach ($this->getLockFilePaths() as $filePath) {
            if (File::exists($filePath)) {
                File::delete($filePath);
                $deleted = true;
            }
        }

        return $deleted;
    }

    /**
     * Helper to format bytes into readable string.
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 GB';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) floor(log($bytes, 1024));

        return round($bytes / pow(1024, $i), 2).' '.$units[$i];
    }
}
