<?php

namespace App\Http\Middleware;

use App\Services\UsbLockService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotAppBuildMiddleware
{
    public function __construct(
        protected UsbLockService $usbLockService
    ) {}

    /**
     * Handle an incoming request.
     * Enforces that the Admin Panel is strictly available on the central web server
     * and completely disabled inside packaged desktop/portable app builds.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If explicit configuration toggle disables admin panel
        if (! config('app.enable_admin_panel', true)) {
            abort(404, 'Admin Panel is not enabled on this deployment.');
        }

        // 2. If running inside the packaged portable / desktop build
        if ($this->usbLockService->isBuildEnvironment()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'ADMIN_PANEL_DISABLED_IN_DESKTOP_BUILD',
                    'message' => 'The Admin Management Panel is only accessible on the central web server.',
                ], 403);
            }

            abort(404, 'The Admin Management Panel is only accessible on the central web server.');
        }

        return $next($request);
    }
}
