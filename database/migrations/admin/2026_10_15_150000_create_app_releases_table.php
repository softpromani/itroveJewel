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
        Schema::create('app_releases', function (Blueprint $table) {
            $table->id();
            $table->string('version')->unique(); // e.g. "1.1.0"
            $table->string('title'); // e.g. "v1.1.0 Luxury UI & Performance Release"
            $table->json('release_notes')->nullable(); // array of bullet points
            $table->string('download_url')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_hash')->nullable(); // SHA256 checksum
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('min_supported_version')->default('1.0.0');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_releases');
    }
};
