<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use App\Models\Client;

class ProfileController extends Controller
{
    /**
     * Display a pet's profile by slug.
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        // Find pet by profile_slug with relationships
        $pet = Pet::with(['client', 'plant'])
            ->where('profile_slug', $slug)
            ->firstOrFail();
        
        // Calculate pet age from birthday
        $petAge = $this->calculateAge($pet->birthday);
        
        // Debug: Log birthday and age calculation
        \Log::info('Pet age calculation', [
            'pet_name' => $pet->name,
            'birthday_raw' => $pet->birthday,
            'birthday_type' => gettype($pet->birthday),
            'birthday_class' => is_object($pet->birthday) ? get_class($pet->birthday) : 'not object',
            'calculated_age' => $petAge,
        ]);
        
        // Get plant data - use final plant or test result plant
        $plantData = $this->getPlantData($pet);
        
        // Debug logging
        \Log::info('Profile plant data for pet: ' . $pet->name, [
            'pet_id' => $pet->id,
            'plant_id' => $pet->plant_id,
            'plant_test' => $pet->plant_test,
            'plantData_type' => $plantData['type'],
            'plantData_name' => $plantData['name'],
            'has_plant_object' => $plantData['plant'] ? 'yes' : 'no',
        ]);
        
        // Get plant photos (from plant data)
        $plantPhotos = $this->getPlantPhotos($plantData);
        
        // Get pet photos - profile photo first, then additional photos
        $petPhotos = [];
        
        // Always start with profile photo if it exists
        if ($pet->profile_photo) {
            $petPhotos[] = \Storage::url($pet->profile_photo);
        }
        
        // Then append additional photos
        if ($pet->photos && is_array($pet->photos) && count($pet->photos) > 0) {
            foreach ($pet->photos as $path) {
                $petPhotos[] = \Storage::url($path);
            }
        }
        
        // If no photos at all, use default
        if (count($petPhotos) === 0) {
            $petPhotos = [asset('assets/plantscan/default-pet.png')];
        }
        
        // Build Open Graph data for social sharing
        $ogData = [
            'title' => "Perfil de {$pet->name} | Aurora Pets",
            'description' => $this->buildDescription($pet, $plantData),
            'image' => $petPhotos[0] ?? asset('assets/plantscan/default-pet.png'),
            'url' => url("/profile/{$slug}"),
        ];
        
        return view('profile.show', compact('pet', 'petAge', 'plantData', 'plantPhotos', 'petPhotos', 'ogData'));
    }
    
        /**
     * Display a pet's profile by ID (alternative route).
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showById($id)
    {
        // Find pet by ID and redirect to slug-based URL
        $pet = Pet::findOrFail($id);
        
        if ($pet->profile_slug) {
            return redirect("/profile/{$pet->profile_slug}");
        }
        
        // If no slug, generate one and redirect
        $pet->profile_slug = \Str::slug("{$pet->name}-" . substr(md5($pet->id), 0, 6));
        $pet->save();
        
        return redirect("/profile/{$pet->profile_slug}");
    }
    
        /**
     * Get plant data (final plant or test result)
     *
     * @param Pet $pet
     * @return array
     */
    private function getPlantData(Pet $pet)
    {
        if ($pet->plant) {
            // Has final associated plant
            return [
                'type' => 'final',
                'plant' => $pet->plant,
                'name' => $pet->plant->name,
                'family' => $pet->plant->family,
                'species' => $pet->plant->species,
                'hasCareInfo' => true
            ];
        } elseif ($pet->plant_number) {
            // Try to find plant by plant_number (most reliable match)
            $plantFromTest = \App\Models\Plant::where('plant_number', $pet->plant_number)
                ->where('is_active', true)
                ->first();
            
            if ($plantFromTest) {
                // Found matching plant in database by number
                return [
                    'type' => 'test',
                    'plant' => $plantFromTest,
                    'name' => $plantFromTest->name,
                    'family' => $plantFromTest->family,
                    'species' => $plantFromTest->species,
                    'hasCareInfo' => true
                ];
            }
        } elseif ($pet->plant_test) {
            // Fallback: Try to find plant by test result name
            // First try exact match (case-insensitive)
            $plantFromTest = \App\Models\Plant::whereRaw('LOWER(name) = ?', [strtolower($pet->plant_test)])
                ->where('is_active', true)
                ->first();
            
            // If no exact match, try LIKE match (more flexible)
            if (!$plantFromTest) {
                $plantFromTest = \App\Models\Plant::where(function($query) use ($pet) {
                    $query->where('name', 'LIKE', '%' . $pet->plant_test . '%')
                          ->orWhereRaw('LOWER(name) LIKE ?', ['%' . strtolower($pet->plant_test) . '%']);
                })
                ->where('is_active', true)
                ->first();
            }
            
            if ($plantFromTest) {
                // Found matching plant in database by name
                return [
                    'type' => 'test',
                    'plant' => $plantFromTest,
                    'name' => $plantFromTest->name,
                    'family' => $plantFromTest->family,
                    'species' => $plantFromTest->species,
                    'hasCareInfo' => true
                ];
            }
            
            // Test result but no matching plant found in database
            return [
                'type' => 'test',
                'plant' => null,
                'name' => $pet->plant_test,
                'family' => null,
                'species' => null,
                'hasCareInfo' => false
            ];
        }
        
        return [
            'type' => 'none',
            'plant' => null,
            'name' => null,
            'family' => null,
            'species' => null,
            'hasCareInfo' => false
        ];
    }

    /**
     * Calculate pet's age in Spanish format: "X años, Y meses"
     *
     * @param \Carbon\Carbon|string|null $birthday
     * @return string|null
     */
    private function calculateAge($birthday)
    {
        if (!$birthday) {
            return null;
        }
        
        try {
            // Handle if birthday is already a Carbon instance or parse it
            $birthDate = $birthday instanceof \Carbon\Carbon ? $birthday : \Carbon\Carbon::parse($birthday);
            $now = \Carbon\Carbon::now();
            
            // Calculate years and remaining months - force integer casting
            $years = (int) $birthDate->diffInYears($now);
            $months = (int) $birthDate->copy()->addYears($years)->diffInMonths($now);
            
            // If less than 1 year old, show only months
            if ($years === 0) {
                if ($months === 0) {
                    // Less than 1 month old
                    $days = (int) $birthDate->diffInDays($now);
                    return $days === 1 ? '1 día' : "{$days} días";
                }
                return $months === 1 ? '1 mes' : "{$months} meses";
            }
            
            // Build age string
            $ageString = $years === 1 ? '1 año' : "{$years} años";
            
            if ($months > 0) {
                $ageString .= $months === 1 ? ', 1 mes' : ", {$months} meses";
            }
            
            return $ageString;
        } catch (\Exception $e) {
            \Log::error('Error calculating pet age', [
                'birthday' => $birthday,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Get plant photos from plant data.
     *
     * @param array $plantData
     * @return array
     */
    private function getPlantPhotos($plantData)
    {
        // If we have a plant object (final or test result match)
        if ($plantData['plant']) {
            $plant = $plantData['plant'];
            
            // If plant has photos array
            if ($plant->photos && count($plant->photos) > 0) {
                // Convert storage paths to URLs
                return array_map(function($path) {
                    return \Storage::url($path);
                }, $plant->photos);
            }
            
            // Use plant's default photo
            if ($plant->default_photo) {
                return [\Storage::url($plant->default_photo)];
            }
        }
        
        // Fallback to generic plant image
        return [asset('assets/plantscan/default-plant.png')];
    }
    
    /**
     * Build meta description for SEO/OG
     *
     * @param Pet $pet
     * @param array $plantData
     * @return string
     */
    private function buildDescription(Pet $pet, array $plantData)
    {
        $description = "Conoce a {$pet->name}";
        
        if ($pet->breed) {
            $description .= ", {$pet->breed}";
        }
        
        if ($plantData['type'] === 'final') {
            $description .= ". Su planta es {$plantData['name']}";
        } elseif ($plantData['type'] === 'test') {
            $description .= ". Planta sugerida: {$plantData['name']}";
        }
        
        $description .= " | Aurora Pets";
        
        return $description;
    }
}
