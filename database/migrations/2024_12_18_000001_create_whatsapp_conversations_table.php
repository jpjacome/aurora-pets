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
        Schema::create('whatsapp_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique(); // WhatsApp number with country code (e.g., +593991234567)
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            $table->string('contact_name')->nullable(); // Name from WhatsApp or manually set
            $table->boolean('is_bot_mode')->default(true); // True = AI responds, False = manual only
            $table->enum('lead_score', ['new', 'cold', 'warm', 'hot'])->default('new');
            $table->timestamp('last_message_at')->nullable();
            $table->integer('unread_count')->default(0);
            $table->boolean('is_archived')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index('client_id');
            $table->index('lead_score');
            $table->index('is_archived');
            $table->index('last_message_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_conversations');
    }
};
