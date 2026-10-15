<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    protected static bool $adminMigrated = false;

    /**
     * The database connections that should have transactions rolled back between tests.
     *
     * @var array<int, string>
     */
    protected array $connectionsToTransact = ['sqlite', 'admin'];

    /**
     * Hook called before database transactions begin in RefreshDatabase.
     */
    protected function beforeRefreshingDatabase()
    {
        $testAdminDb = database_path('test_admin.sqlite');
        config(['database.connections.admin.database' => $testAdminDb]);

        if (! static::$adminMigrated && file_exists(database_path('migrations/admin'))) {
            if (! file_exists($testAdminDb)) {
                touch($testAdminDb);
            }
            Artisan::call('migrate', [
                '--database' => 'admin',
                '--path' => 'database/migrations/admin',
                '--force' => true,
            ]);
            static::$adminMigrated = true;
        }
    }
}
