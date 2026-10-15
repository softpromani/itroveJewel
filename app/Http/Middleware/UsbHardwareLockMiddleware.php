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
        // Only enforce hardware lock in packaged build files, not on developer workstations
        if (! $this->usbLockService->isBuildEnvironment()) {
            return $next($request);
        }

        // In packaged app builds, the web Admin Panel is strictly inaccessible
        if ($request->is('admin') || $request->is('admin/*')) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'error' => 'ADMIN_PANEL_DISABLED_IN_DESKTOP_BUILD',
                    'message' => 'The Admin Management Panel is only accessible on the central web server.',
                ], 403);
            }

            abort(404, 'The Admin Management Panel is only accessible on the central web server.');
        }

        // Exclude internal installer / status / license routes to avoid circular blocks
        if ($request->is('api/usb/*') || $request->is('usb/*') || $request->is('api/license/*') || $request->is('/')) {
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
