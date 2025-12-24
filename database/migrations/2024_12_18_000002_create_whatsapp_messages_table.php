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
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('whatsapp_conversations')->onDelete('cascade');
            $table->enum('direction', ['incoming', 'outgoing']); // incoming = from customer, outgoing = from us
            $table->text('content'); // Message text content
            $table->boolean('sent_by_bot')->default(false); // True if AI generated the message
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->string('whatsapp_message_id')->nullable(); // WhatsApp's message ID for tracking
            $table->json('metadata')->nullable(); // Store additional data (media URLs, template info, etc.)
            $table->timestamps();

            // Indexes for performance
            $table->index('conversation_id');
            $table->index('direction');
            $table->index('whatsapp_message_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
