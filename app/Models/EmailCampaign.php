<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'mailable_class', 'subject', 'template_body', 'attachments', 'status', 'scheduled_at', 'created_by', 'metadata'
    ];

    protected $casts = [
        'attachments' => 'array',
        'metadata' => 'array',
        'scheduled_at' => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(EmailMessage::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the clients that participated in this campaign.
     * Uses the email_messages pivot table.
     */
    public function clients()
    {
        return $this->belongsToMany(
            Client::class,
            'email_messages',
            'campaign_id',
            'client_id'
        )
        ->withPivot('status', 'delivered_at', 'opened_at', 'clicked_at')
        ->withTimestamps()
        ->distinct();
    }
}
