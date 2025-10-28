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
            $table->enum('location_type', ['indoor', 'outdoor', 'both'])
                  ->default('indoor')
                  ->after('difficulty')
                  ->comment('Growing location: indoor, outdoor, or both');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plants', function (Blueprint $table) {
            $table->dropColumn('location_type');
        });
    }
};
