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
            $table->string('plant_type')->nullable()->after('species'); // 'Con flor' or 'Foliar'
            $table->string('difficulty')->nullable()->after('plant_type'); // 'Fácil', 'Media', 'Alta'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn(['plant_type', 'difficulty']);
        });
    }
};
