<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    /**
     * Show the form for editing the specified pet.
     */
    public function edit($id)
    {
        $pet = Pet::with(['client', 'plant'])->findOrFail($id);
        $plants = Plant::where('is_active', true)
            ->orderBy('name')
            ->get();
        
        return view('admin.pets.edit', compact('pet', 'plants'));
    }

    /**
     * Update the specified pet in storage.
     */
    public function update(Request $request, $id)
    {
        $pet = Pet::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'nullable|string|max:255',
            'breed' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'birthday' => 'nullable|date',
            'weight' => 'nullable|string|max:50',
            'living_space' => 'nullable|string|max:255',
            'plant_id' => 'nullable|exists:plants,id',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'pet_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'deceased' => 'nullable|boolean',
            'deceased_at' => 'nullable|date',
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old profile photo if exists
            if ($pet->profile_photo) {
                Storage::disk('public')->delete($pet->profile_photo);
            }
            
            $profilePhotoPath = $request->file('profile_photo')->store('pet-photos', 'public');
            $validated['profile_photo'] = $profilePhotoPath;
        }

        // Handle multiple pet photos upload
        if ($request->hasFile('pet_photos')) {
            $existingPhotos = $pet->photos ?? [];
            $newPhotos = [];
            
            foreach ($request->file('pet_photos') as $photo) {
                $photoPath = $photo->store('pet-photos', 'public');
                $newPhotos[] = $photoPath;
            }
            
            // Merge with existing photos
            $validated['photos'] = array_merge($existingPhotos, $newPhotos);
        }

        // Update pet
        $pet->update($validated);

        return redirect()
            ->route('admin.clients')
            ->with('success', "Pet '{$pet->name}' updated successfully!");
    }

    /**
     * Delete a photo from the pet's photo array.
     */
    public function deletePhoto(Request $request, $id)
    {
        $pet = Pet::findOrFail($id);
        
        $photoPath = $request->input('photo_path');
        
        if (!$photoPath) {
            return back()->with('error', 'Photo path is required.');
        }

        // Remove from photos array
        $photos = $pet->photos ?? [];
        $photos = array_filter($photos, fn($photo) => $photo !== $photoPath);
        $pet->photos = array_values($photos);
        $pet->save();

        // Delete from storage
        if (Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        return back()->with('success', 'Photo deleted successfully!');
    }
}
