<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppUpdateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppUpdateClientController extends Controller
{
    public function __construct(
        protected AppUpdateService $updateService
    ) {}

    /**
     * Check if a newer version of ItroveJewel is available.
     */
    public function check(): JsonResponse
    {
        $status = $this->updateService->checkForUpdates();

        return response()->json($status);
    }

    /**
     * Trigger 1-Click Upgrade.
     */
    public function apply(Request $request): JsonResponse
    {
        $downloadUrl = $request->input('download_url');
        $sha256 = $request->input('sha256');

        $result = $this->updateService->applyUpdate($downloadUrl, $sha256);

        if (! $result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }
}
