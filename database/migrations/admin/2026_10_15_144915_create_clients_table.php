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
        Schema::connection('admin')->create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('shop_name');
            $table->string('mobile_number');
            $table->string('email')->unique();
            $table->string('status')->default('active'); // active, suspended, inactive
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('admin')->dropIfExists('clients');
    }
};
