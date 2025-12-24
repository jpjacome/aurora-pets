<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotAdminComment extends Model
{
    protected $table = 'chatbot_admin_comments';

    protected $fillable = [
        'comment',
        'conversation_context',
        'created_by',
    ];

    protected $casts = [
        'conversation_context' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
