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
        Schema::create('shop_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_plot_id')->unique()->constrained('map_plots')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('owners')->onDelete('restrict');
            $table->foreignId('building_type_id')->constrained('building_types')->onDelete('restrict');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->enum('status', ['active', 'pending_renewal', 'expired', 'vacant'])->default('active');
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_contracts');
    }
};

