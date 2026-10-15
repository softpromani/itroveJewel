<?php

namespace App\Console\Commands;

use App\Services\ReleasePublishingService;
use Exception;
use Illuminate\Console\Command;

class AppPublishReleaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:publish-release
        {version : Release version string, e.g. 1.1.0}
        {--title= : Human-readable title for this release}
        {--notes= : Comma-separated list of release notes/features}
        {--url= : Optional direct external download URL}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package and publish an application release for customer 1-click upgrades';

    public function __construct(
        protected ReleasePublishingService $releaseService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $version = trim((string) $this->argument('version'));
        $title = $this->option('title') ?: "ItroveJewel v{$version} Update";

        $rawNotes = $this->option('notes') ?: 'Performance improvements and bug fixes,Enhanced USB hardware security,Optimized database engine';
        $releaseNotes = is_array($rawNotes) ? $rawNotes : array_map('trim', explode(',', (string) $rawNotes));

        $this->info("Packaging release v{$version}...");

        try {
            $release = $this->releaseService->publishRelease(
                version: $version,
                title: $title,
                releaseNotes: $releaseNotes,
                minSupportedVersion: '1.0.0',
                isActive: true,
                customDownloadUrl: $this->option('url')
            );

            $this->line("  ✓ Generated archive: {$release->file_path}");
            $this->line("  ✓ SHA256 Checksum: {$release->file_hash}");
            $this->line("  ✓ Package Size: {$release->formatted_file_size}");

            $this->info("SUCCESS: Release v{$version} is now LIVE on the central server!");
            $this->line('Customers connected to the internet can now upgrade to this version with 1-click.');

            return 0;
        } catch (Exception $e) {
            $this->error('Failed to publish release: '.$e->getMessage());

            return 1;
        }
    }
}
