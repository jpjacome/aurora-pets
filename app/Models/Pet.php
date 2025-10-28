<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Relationships
        'client_id',
        'plant_id',
        
        // Basic information
        'name',
        'species',
        'breed',
        'gender',
        'birthday',
        'weight',
        
        // Appearance & personality
        'color',
        'characteristics',
        
        // Living environment
        'living_space',
        
        // PlantScan test data
        'plant_test',
        'plant_number',
        'metadata',
        
        // Media & profile
        'photos',
        'profile_photo',
        'profile_slug',
        
        // Life cycle
        'deceased',
        'deceased_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthday' => 'date',
        'deceased_at' => 'date',
        'color' => 'array',
        'characteristics' => 'array',
        'photos' => 'array',
        'metadata' => 'array',
        'deceased' => 'boolean',
    ];

    /**
     * Get the client that owns the pet.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the plant assigned to the pet.
     */
    public function plant()
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * Check if pet is deceased.
     *
     * @return bool
     */
    public function isDeceased()
    {
        return $this->deceased || $this->deceased_at !== null;
    }

    /**
     * Get the pet's age in human-readable format.
     *
     * @return string|null
     */
    public function getAgeAttribute()
    {
        if (!$this->birthday) {
            return null;
        }
        
        return $this->birthday->diffForHumans(['parts' => 2]);
    }
}
