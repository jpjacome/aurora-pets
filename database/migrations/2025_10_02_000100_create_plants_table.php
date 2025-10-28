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
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            
            // === Basic Plant Information ===
            $table->string('name')->unique();                      // "Schefflera"
            $table->string('scientific_name')->nullable();         // "Schefflera arboricola"
            $table->string('family')->nullable();                  // "Araliaceae"
            $table->string('species')->nullable();                 // Species classification
            $table->text('description')->nullable();               // General description
            
            // === Plant Care Information (from capuchino.html requirements) ===
            $table->text('substrate_info')->nullable();            // 🪴 Soil/substrate requirements
            $table->text('lighting_info')->nullable();             // ☀️ Light requirements
            $table->text('watering_info')->nullable();             // 💦 Watering instructions
            $table->string('care_level')->nullable();              // "fácil", "moderado", "difícil"
            
            // === Media ===
            $table->json('photos')->nullable();                    // Array of plant photo URLs
            $table->string('default_photo', 500)->nullable();      // Primary image path
            
            // === PlantScan Integration ===
            $table->integer('plant_number')->unique()->nullable(); // PlantScan number (1-27)
            $table->string('slug')->unique();                      // URL-friendly name
            
            // === Status ===
            $table->boolean('is_active')->default(true);           // Available for assignment
            
            $table->timestamps();
            
            // === Indexes ===
            $table->index('is_active');
            $table->index('plant_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plants');
    }
};
