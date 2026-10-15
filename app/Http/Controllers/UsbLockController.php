<?php

namespace App\Http\Controllers;

use App\Services\UsbLockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class UsbLockController extends Controller
{
    public function __construct(
        protected UsbLockService $usbLockService
    ) {}

    /**
     * Main entrypoint for web requests.
     * Evaluates lock status and displays the Installation Wizard, Lockout, or Main App.
     */
    public function index(): View|Response
    {
        $hw = $this->usbLockService->getHardwareInfo();
        $verification = $this->usbLockService->verifyLock($hw);

        if ($verification['status'] === 'NOT_INSTALLED') {
            return view('usb.install', [
                'hardware' => $hw,
                'verification' => $verification,
            ]);
        }

        if ($verification['status'] === 'LOCKOUT' || $verification['status'] === 'TAMPERED') {
            return response()->view('usb.lockout', [
                'hardware' => $hw,
                'verification' => $verification,
            ], 403);
        }

        return view('welcome', [
            'hardware' => $hw,
            'verification' => $verification,
        ]);
    }

    /**
     * Handle the first-time USB Hardware Serial Lock installation.
     */
    public function install(Request $request): JsonResponse
    {
        $request->validate([
            'admin_name' => ['nullable', 'string', 'max:100'],
            'confirm_lock' => ['required', 'boolean'],
        ]);

        if (! $request->boolean('confirm_lock')) {
            return response()->json([
                'success' => false,
                'message' => 'You must check the confirmation to bind and lock this USB pendrive.',
            ], 422);
        }

        $result = $this->usbLockService->installAndLock([
            'admin_name' => $request->input('admin_name', 'Authorized Administrator'),
        ]);

        if (! $result['success']) {
            return response()->json($result, 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'lock_data' => $result['lock_data'],
            'redirect' => route('home'),
        ]);
    }

    /**
     * Retrieve live USB hardware detection and lock verification status.
     */
    public function status(): JsonResponse
    {
        $hw = $this->usbLockService->getHardwareInfo(forceRefresh: true);
        $verification = $this->usbLockService->verifyLock($hw);

        return response()->json([
            'hardware' => $hw,
            'verification' => $verification,
            'is_authorized' => $verification['is_authorized'],
            'status' => $verification['status'],
        ]);
    }

    /**
     * Developer/Admin reset endpoint (for testing hardware locks).
     */
    public function reset(Request $request): JsonResponse
    {
        $deleted = $this->usbLockService->resetLock();

        return response()->json([
            'success' => $deleted,
            'message' => $deleted
                ? 'USB Hardware lock has been cleared. The application is now in setup mode.'
                : 'No lock file was found to remove.',
        ]);
    }
}
