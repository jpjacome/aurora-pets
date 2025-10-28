<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plant extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'scientific_name',
        'family',
        'species',
        'plant_type',
        'difficulty',
        'location_type',
        'origin',
        'description',
        'substrate_info',
        'lighting_info',
        'light_requirement',
        'watering_info',
        'water_requirement',
        'photos',
        'default_photo',
        'plant_number',
        'slug',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'photos' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the pets assigned to this plant.
     */
    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
