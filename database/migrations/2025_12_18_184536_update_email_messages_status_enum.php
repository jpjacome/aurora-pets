<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_messages', function (Blueprint $table) {
            // For SQLite, we need to drop and recreate. For MySQL, we can alter.
            // This approach works for both:
            DB::statement("ALTER TABLE email_messages MODIFY COLUMN status VARCHAR(20) DEFAULT 'queued'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_messages', function (Blueprint $table) {
            DB::statement("ALTER TABLE email_messages MODIFY COLUMN status VARCHAR(20) DEFAULT 'queued'");
        });
    }
};
