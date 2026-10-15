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
        Schema::create('fresh_installer_packages', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique();
            $table->string('title')->nullable();
            $table->string('package_type')->default('full_usb');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('sha256')->nullable();
            $table->unsignedInteger('download_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fresh_installer_packages');
    }
};
