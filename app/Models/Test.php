<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    use HasFactory;

    protected $fillable = [
        'client',
        'email',
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
        'plant_number',
        'share_token',
        'og_image',
        'og_ready',
        'metadata',
    ];

    protected $casts = [
        'pet_birthday' => 'date',
        'pet_color' => 'array',
        'pet_characteristics' => 'array',
        'metadata' => 'array',
    ];

    // Return absolute OG image URL when available
    public function getOgImageUrlAttribute()
    {
        if (!$this->og_image) return null;
        // If it's already an absolute URL, return directly
        if (preg_match('/^https?:\/\//', $this->og_image)) {
            return $this->og_image;
        }
        return url('storage/' . ltrim($this->og_image, '/'));
    }
}
