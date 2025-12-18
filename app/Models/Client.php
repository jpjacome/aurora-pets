<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client',
        'email',
        'phone',
        'address',
        'profile_url',
        'pet_name',
        'pet_species',
        'gender',
        'pet_birthday',
        'pet_breed',
        'pet_weight',
        'pet_color',
        'living_space',
        'pet_characteristics',
        'plant_test',
        'plant',
        'plant_description',
    ];

    protected $casts = [
        'pet_birthday' => 'date',
        'pet_color' => 'array',
        'pet_characteristics' => 'array',
        'unsubscribed_at' => 'datetime',
    ];

    /**
     * Get the pets that belong to the client.
     */
    public function pets()
    {
        return $this->hasMany(Pet::class);
    }

    /**
     * Get the email campaigns this client has participated in.
     * Uses the email_messages pivot table.
     */
    public function campaigns()
    {
        return $this->belongsToMany(
            EmailCampaign::class,
            'email_messages',
            'client_id',
            'campaign_id'
        )
        ->withPivot('status', 'delivered_at', 'opened_at', 'clicked_at')
        ->withTimestamps()
        ->orderBy('email_messages.created_at', 'desc');
    }

    /**
     * Get all email messages sent to this client.
     */
    public function emailMessages()
    {
        return $this->hasMany(EmailMessage::class, 'client_id');
    }
}
