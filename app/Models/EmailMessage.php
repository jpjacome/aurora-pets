<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EmailMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id', 'client_id', 'email', 'message_uuid', 'provider_id', 'status', 'attempts', 'error', 'metadata',
        'delivered_at', 'opened_at', 'clicked_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (EmailMessage $message) {
            if (empty($message->message_uuid)) {
                $message->message_uuid = Str::uuid()->toString();
            }
        });
    }

    public function campaign()
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
