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
    ];

    /**
     * Get the pets that belong to the client.
     */
    public function pets()
    {
        return $this->hasMany(Pet::class);
    }
}
