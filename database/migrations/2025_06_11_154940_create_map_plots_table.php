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
        Schema::create('map_plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->onDelete('cascade');
            $table->string('plot_identifier', 50);
            $table->integer('coord_x');
            $table->integer('coord_y');
            $table->integer('width');
            $table->integer('height');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['map_id', 'plot_identifier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('map_plots');
    }
};
