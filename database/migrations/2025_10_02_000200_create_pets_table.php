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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            
            // === Ownership & Relationships ===
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('plant_id')->nullable()->constrained()->onDelete('set null');
            
            // === Basic Information (from PlantScan test) ===
            $table->string('name');                                // Pet name (required)
            $table->string('species')->nullable();                 // "perro", "gato", "conejo", etc.
            $table->string('breed')->nullable();                   // "Golden Retriever", "Siamés", etc.
            $table->string('gender', 50)->nullable();              // "masculino", "femenino", etc.
            $table->date('birthday')->nullable();                  // Birth date
            $table->string('weight', 50)->nullable();              // "1-5", "5-10", "10-20", etc.
            
            // === Appearance & Personality (from PlantScan test) ===
            $table->json('color')->nullable();                     // Array: ["negro", "blanco", "café"]
            $table->json('characteristics')->nullable();           // Array: ["juguetón", "tranquilo", "curioso"]
            
            // === Living Environment (from PlantScan test) ===
            $table->string('living_space')->nullable();            // "casa", "departamento", "casa con jardin"
            
            // === PlantScan Test Data (preserved from test) ===
            $table->string('plant_test')->nullable();              // Test type/identifier
            $table->integer('plant_number')->nullable();           // Assigned plant number (1-27)
            $table->json('metadata')->nullable();                  // Additional test metadata
            
            // === Media & Photos ===
            $table->json('photos')->nullable();                    // Array of photo URLs/paths
            $table->string('profile_photo', 500)->nullable();      // Main profile photo URL
            
            // === Profile & Public Access ===
            $table->string('profile_slug')->unique()->nullable();  // "capuchino", "luna-123"
            
            // === Life Cycle Tracking ===
            $table->boolean('deceased')->default(false);           // Quick deceased flag
            $table->date('deceased_at')->nullable();               // Date of death
            
            $table->timestamps();
            
            // === Indexes for Performance ===
            $table->index('client_id');
            $table->index('plant_id');
            $table->index('profile_slug');
            $table->index('deceased');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
