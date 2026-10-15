<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppRelease;
use App\Models\PendriveRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UpdateServerController extends Controller
{
    /**
     * Check if a newer version of the portable application is available.
     */
    public function check(Request $request): JsonResponse
    {
        $currentVersion = $request->query('version', '1.0.0');
        $serialNumber = $request->query('serial');

        // Optional license check: verify pendrive is not suspended or revoked
        if (! empty($serialNumber)) {
            $reg = PendriveRegistration::with('client')
                ->where('serial_number', $serialNumber)
                ->first();

            if ($reg && ($reg->status !== 'active' || ($reg->client && $reg->client->status !== 'active'))) {
                return response()->json([
                    'update_available' => false,
                    'error' => 'LICENSE_SUSPENDED',
                    'message' => 'Your USB Hardware License is suspended. Please contact support.',
                ], 403);
            }
        }

        $latestRelease = AppRelease::getLatestActive();

        if (! $latestRelease) {
            return response()->json([
                'update_available' => false,
                'current_version' => $currentVersion,
                'message' => 'No active updates found.',
            ]);
        }

        $isNewer = version_compare($latestRelease->version, $currentVersion, '>');

        if (! $isNewer) {
            return response()->json([
                'update_available' => false,
                'current_version' => $currentVersion,
                'latest_version' => $latestRelease->version,
                'message' => 'You are on the latest version of ItroveJewel.',
            ]);
        }

        $baseDomain = rtrim(config('app.url') ?: env('APP_URL', 'http://127.0.0.1:8000'), '/');
        $downloadUrl = $latestRelease->download_url ?: "{$baseDomain}/api/updates/download/{$latestRelease->version}";

        return response()->json([
            'update_available' => true,
            'current_version' => $currentVersion,
            'latest_version' => $latestRelease->version,
            'title' => $latestRelease->title,
            'release_notes' => $latestRelease->release_notes ?? [],
            'download_url' => $downloadUrl,
            'sha256' => $latestRelease->file_hash,
            'file_size' => $latestRelease->file_size,
            'min_supported_version' => $latestRelease->min_supported_version,
            'released_at' => $latestRelease->created_at?->format('M d, Y') ?? date('M d, Y'),
        ]);
    }

    /**
     * Download the specified version's update package zip file.
     */
    public function download(string $version): BinaryFileResponse|JsonResponse
    {
        $release = AppRelease::where('version', $version)
            ->where('is_active', true)
            ->first();

        if (! $release || ! $release->file_path || ! File::exists($release->file_path)) {
            return response()->json([
                'error' => 'FILE_NOT_FOUND',
                'message' => "Update package bundle for version {$version} was not found on this server.",
            ], 404);
        }

        $release->increment('download_count');

        return response()->download($release->file_path, "itrovejewel-update-{$version}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }
}
