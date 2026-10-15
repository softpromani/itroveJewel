<?php

namespace App\Http\Middleware;

use App\Services\UsbLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UsbHardwareLockMiddleware
{
    public function __construct(
        protected UsbLockService $usbLockService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Exclude internal installer / status routes to avoid circular blocks
        if ($request->is('api/usb/*') || $request->is('usb/*') || $request->is('/')) {
            return $next($request);
        }

        $hw = $this->usbLockService->getHardwareInfo();
        $verification = $this->usbLockService->verifyLock($hw);

        // If not installed yet, redirect to root for first-time installation wizard
        if ($verification['status'] === 'NOT_INSTALLED') {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'INSTALLATION_REQUIRED',
                    'message' => 'First-time installation required. Please complete USB Hardware Serial Lock binding.',
                ], 403);
            }

            return redirect()->route('home');
        }

        // If hardware serial mismatch or tampering, block with Lockout screen
        if (! $verification['is_authorized']) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'HARDWARE_LOCKOUT',
                    'message' => $verification['message'],
                    'current_serial' => $verification['current_serial'],
                    'locked_serial' => $verification['locked_serial'],
                ], 403);
            }

            return response()->view('usb.lockout', [
                'hardware' => $hw,
                'verification' => $verification,
            ], 403);
        }

        return $next($request);
    }
}
