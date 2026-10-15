<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppRelease;
use App\Services\ReleasePublishingService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminReleaseController extends Controller
{
    public function __construct(
        protected ReleasePublishingService $releaseService
    ) {}

    /**
     * Display a listing of application releases and publishing controls.
     */
    public function index(Request $request): View
    {
        $releases = AppRelease::latest('id')->paginate(15);

        $latestActive = AppRelease::getLatestActive();
        $currentServerVersion = $this->releaseService->getCurrentServerVersion();
        $suggestedVersion = $this->releaseService->suggestNextVersion();

        $metrics = [
            'total_releases' => AppRelease::count(),
            'active_releases' => AppRelease::where('is_active', true)->count(),
            'total_downloads' => (int) AppRelease::sum('download_count'),
            'current_server_version' => $currentServerVersion,
            'latest_active_version' => $latestActive?->version ?? 'None',
            'suggested_version' => $suggestedVersion,
        ];

        return view('admin.releases.index', compact('releases', 'metrics'));
    }

    /**
     * Package and publish a new application release for clients.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'version' => ['required', 'string', 'max:20', 'regex:/^\d+(\.\d+)+$/'],
            'title' => ['required', 'string', 'max:255'],
            'release_notes' => ['nullable', 'string', 'max:5000'],
            'min_supported_version' => ['nullable', 'string', 'max:20'],
            'is_active' => ['nullable'],
        ], [
            'version.regex' => 'The version must follow semantic numbering, e.g. 1.1.0 or 1.2.3',
        ]);

        $version = trim((string) $validated['version']);
        $title = trim((string) $validated['title']);
        $minSupported = ! empty($validated['min_supported_version']) ? trim((string) $validated['min_supported_version']) : '1.0.0';
        $isActive = $request->boolean('is_active', true);

        // Parse release notes from textarea (supports both line breaks and commas)
        $rawNotes = $validated['release_notes'] ?? '';
        $releaseNotes = [];
        if (! empty($rawNotes)) {
            $lines = preg_split('/[\r\n]+/', (string) $rawNotes);
            foreach ($lines as $line) {
                $trimmed = trim((string) $line, " \t\n\r\0\x0B-•*");
                if (! empty($trimmed)) {
                    $releaseNotes[] = $trimmed;
                }
            }
        }

        if (empty($releaseNotes)) {
            $releaseNotes = [
                'Feature updates and enhancements',
                'Performance and stability improvements',
                'Optimized local database engine',
            ];
        }

        try {
            $release = $this->releaseService->publishRelease(
                version: $version,
                title: $title,
                releaseNotes: $releaseNotes,
                minSupportedVersion: $minSupported,
                isActive: $isActive
            );

            return redirect()->route('admin.releases.index')
                ->with('success', "Release v{$release->version} successfully packaged & published! Clients connected to the internet can now upgrade with a single click.");
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to publish release: '.$e->getMessage());
        }
    }

    /**
     * Download the release update package ZIP directly from the dashboard.
     */
    public function download(AppRelease $release): BinaryFileResponse|RedirectResponse
    {
        if (! $release->file_path || ! File::exists($release->file_path)) {
            return redirect()->back()->with('error', "Update package ZIP for v{$release->version} was not found on the server storage.");
        }

        $release->increment('download_count');

        return response()->download($release->file_path, "itrovejewel-update-{$release->version}.zip", [
            'Content-Type' => 'application/zip',
        ]);
    }

    /**
     * Toggle the active state of a release.
     */
    public function toggle(AppRelease $release): RedirectResponse
    {
        $this->releaseService->toggleStatus($release);

        $statusText = $release->is_active ? 'Activated (LIVE)' : 'Paused (Deactivated)';

        return redirect()->back()
            ->with('success', "Release v{$release->version} is now {$statusText}.");
    }

    /**
     * Delete an existing release and its update package ZIP.
     */
    public function destroy(AppRelease $release): RedirectResponse
    {
        $version = $release->version;
        $this->releaseService->deleteRelease($release);

        return redirect()->route('admin.releases.index')
            ->with('success', "Release v{$version} and its update package have been permanently deleted.");
    }
}
