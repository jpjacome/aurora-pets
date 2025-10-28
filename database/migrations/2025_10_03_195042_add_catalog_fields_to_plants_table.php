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
        Schema::table('plants', function (Blueprint $table) {
            // Catalog-specific fields (short versions for display)
            $table->string('origin')->nullable()->after('difficulty'); // "Sudamérica", "Asia", "África"
            $table->string('light_requirement')->nullable()->after('lighting_info'); // "Indirecta", "Directa", "Semisombra"
            $table->string('water_requirement')->nullable()->after('watering_info'); // "Abundante", "Moderado", "Escaso"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn(['origin', 'light_requirement', 'water_requirement']);
        });
    }
};
