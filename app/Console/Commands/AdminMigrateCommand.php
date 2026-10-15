<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AdminMigrateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:migrate
        {--fresh : Drop all tables in the admin database and re-run all migrations}
        {--status : Show the status of each admin migration}
        {--rollback : Rollback the last admin migration batch}
        {--step= : The number of migrations to be reverted}
        {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run migrations for the dedicated Admin database (database/migrations/admin)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dbPath = config('database.connections.admin.database');

        if (! empty($dbPath) && $dbPath !== ':memory:' && ! File::exists($dbPath)) {
            $dir = dirname($dbPath);
            if (! File::isDirectory($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            File::put($dbPath, '');
            $this->info("Created admin database file: {$dbPath}");
        }

        $migrationPath = 'database/migrations/admin';

        if ($this->option('status')) {
            $this->info('Checking Admin Database Migrations Status...');

            return $this->call('migrate:status', [
                '--database' => 'admin',
                '--path' => $migrationPath,
            ]);
        }

        if ($this->option('rollback')) {
            $this->info('Rolling back Admin Database Migrations...');
            $params = [
                '--database' => 'admin',
                '--path' => $migrationPath,
            ];
            if ($this->option('step')) {
                $params['--step'] = (int) $this->option('step');
            }
            if ($this->option('force')) {
                $params['--force'] = true;
            }

            return $this->call('migrate:rollback', $params);
        }

        if ($this->option('fresh')) {
            $this->warn('Dropping all tables and re-migrating Admin Database...');
            $params = [
                '--database' => 'admin',
                '--path' => $migrationPath,
            ];
            if ($this->option('force')) {
                $params['--force'] = true;
            }

            return $this->call('migrate:fresh', $params);
        }

        $this->info('Running Admin Database Migrations...');
        $params = [
            '--database' => 'admin',
            '--path' => $migrationPath,
        ];
        if ($this->option('force')) {
            $params['--force'] = true;
        }

        return $this->call('migrate', $params);
    }
}
