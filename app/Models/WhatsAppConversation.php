<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppConversation extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'phone_number',
        'client_id',
        'contact_name',
        'is_bot_mode',
        'lead_score',
        'last_message_at',
        'unread_count',
        'is_archived',
    ];

    protected $casts = [
        'is_bot_mode' => 'boolean',
        'is_archived' => 'boolean',
        'last_message_at' => 'datetime',
        'unread_count' => 'integer',
    ];

    /**
     * Get the client associated with this conversation
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id');
    }

    /**
     * Get the latest message
     */
    public function latestMessage()
    {
        return $this->hasOne(WhatsAppMessage::class, 'conversation_id')->latestOfMany();
    }

    /**
     * Scope to get only active (non-archived) conversations
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope to get conversations by lead score
     */
    public function scopeByLeadScore($query, string $score)
    {
        return $query->where('lead_score', $score);
    }

    /**
     * Scope to get unread conversations
     */
    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    /**
     * Mark all messages as read
     */
    public function markAsRead(): void
    {
        $this->update(['unread_count' => 0]);
    }

    /**
     * Increment unread count
     */
    public function incrementUnread(): void
    {
        $this->increment('unread_count');
    }

    /**
     * Get display name (contact name or phone number)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->contact_name ?? $this->phone_number;
    }

    /**
     * Check if conversation is linked to a client
     */
    public function isLinkedToClient(): bool
    {
        return !is_null($this->client_id);
    }

    /**
     * Attempt to auto-link to existing client by phone number
     */
    public function autoLinkToClient(): bool
    {
        if ($this->client_id) {
            return false; // Already linked
        }

        $client = Client::where('phone', $this->phone_number)->first();
        
        if ($client) {
            $this->update([
                'client_id' => $client->id,
                'contact_name' => $client->client,
            ]);
            return true;
        }

        return false;
    }
}
