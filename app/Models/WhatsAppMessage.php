<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'conversation_id',
        'direction',
        'content',
        'sent_by_bot',
        'status',
        'whatsapp_message_id',
        'metadata',
    ];

    protected $casts = [
        'sent_by_bot' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsAppConversation::class, 'conversation_id');
    }

    /**
     * Scope to get incoming messages
     */
    public function scopeIncoming($query)
    {
        return $query->where('direction', 'incoming');
    }

    /**
     * Scope to get outgoing messages
     */
    public function scopeOutgoing($query)
    {
        return $query->where('direction', 'outgoing');
    }

    /**
     * Scope to get bot-generated messages
     */
    public function scopeBotGenerated($query)
    {
        return $query->where('sent_by_bot', true);
    }

    /**
     * Scope to get manual messages
     */
    public function scopeManual($query)
    {
        return $query->where('sent_by_bot', false);
    }

    /**
     * Check if message is incoming
     */
    public function isIncoming(): bool
    {
        return $this->direction === 'incoming';
    }

    /**
     * Check if message is outgoing
     */
    public function isOutgoing(): bool
    {
        return $this->direction === 'outgoing';
    }

    /**
     * Check if message was sent by AI bot
     */
    public function isBotGenerated(): bool
    {
        return $this->sent_by_bot === true;
    }

    /**
     * Update message status
     */
    public function updateStatus(string $status): void
    {
        $this->update(['status' => $status]);
    }

    /**
     * Get formatted timestamp
     */
    public function getFormattedTimeAttribute(): string
    {
        return $this->created_at->format('g:i A');
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        if ($this->created_at->isToday()) {
            return 'Today';
        }
        
        if ($this->created_at->isYesterday()) {
            return 'Yesterday';
        }
        
        return $this->created_at->format('M d, Y');
    }
}
