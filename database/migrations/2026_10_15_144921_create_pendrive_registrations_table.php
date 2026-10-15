<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pendrive_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->string('serial_number')->index();
            $table->string('pendrive_model')->nullable();
            $table->string('label')->nullable();
            $table->string('status')->default('active'); // active, revoked, pending
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_verified_at')->nullable();
            $table->unsignedInteger('verification_count')->default(0);
            $table->string('registered_by')->nullable();
            $table->timestamps();

            // A serial number should be unique among active registrations
            $table->unique(['serial_number', 'client_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendrive_registrations');
    }
};
