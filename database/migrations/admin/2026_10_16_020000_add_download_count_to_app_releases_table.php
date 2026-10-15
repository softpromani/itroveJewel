<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     *
     * @var string
     */
    protected $connection = 'admin';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('app_releases', function (Blueprint $table) {
            if (! Schema::hasColumn('app_releases', 'download_count')) {
                $table->unsignedBigInteger('download_count')->default(0)->after('file_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('app_releases', function (Blueprint $table) {
            if (Schema::hasColumn('app_releases', 'download_count')) {
                $table->dropColumn('download_count');
            }
        });
    }
};
