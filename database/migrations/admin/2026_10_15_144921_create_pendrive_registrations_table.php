<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The database connection that should be used by the migration.
     */
    protected $connection = 'admin';

    /**
     * Run the migrations on the dedicated admin database.
     */
    public function up(): void
    {
        Schema::connection('admin')->create('pendrive_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('serial_number')->index();
            $table->string('pendrive_model')->nullable();
            $table->string('label')->nullable();
            $table->string('status')->default('active'); // active, revoked
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->unsignedInteger('verification_count')->default(0);
            $table->string('registered_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('admin')->dropIfExists('pendrive_registrations');
    }
};
