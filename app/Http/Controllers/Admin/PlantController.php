<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PlantController extends Controller
{
    /**
     * Show the form for editing the specified plant.
     */
    public function edit($id)
    {
        $plant = Plant::withCount('pets')->findOrFail($id);
        
        return view('admin.plants.edit', compact('plant'));
    }

    /**
     * Update the specified plant in storage.
     */
    public function update(Request $request, $id)
    {
        $plant = Plant::findOrFail($id);
        
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'scientific_name' => 'nullable|string|max:255',
                'family' => 'nullable|string|max:255',
                'species' => 'nullable|string|max:255',
                'description' => 'nullable|string|max:225',
                'substrate_info' => 'nullable|string',
                'lighting_info' => 'nullable|string',
                'light_requirement' => 'nullable|string|max:255',
                'watering_info' => 'nullable|string',
                'water_requirement' => 'nullable|string|max:255',
                'plant_type' => 'nullable|string|in:Con flor,Foliar',
                'difficulty' => 'nullable|string|in:Baja,Media,Alta',
                'origin' => 'nullable|string|max:255',
                'plant_number' => 'nullable|integer|min:1|max:27|unique:plants,plant_number,' . $id,
                'slug' => 'nullable|string|max:255|unique:plants,slug,' . $id,
                'is_active' => 'nullable|boolean',
                'plant_photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Plant validation failed', [
                'errors' => $e->errors(),
                'has_file' => $request->hasFile('plant_photos'),
                'files' => $request->file('plant_photos')
            ]);
            throw $e;
        }

        $validated['is_active'] = $request->has('is_active') ? true : false;
        
        // Auto-generate slug if not provided or empty
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        // Handle multiple plant photos upload
        if ($request->hasFile('plant_photos')) {
            $existingPhotos = $plant->photos ?? [];
            $newPhotos = [];
            
            foreach ($request->file('plant_photos') as $photo) {
                try {
                    // Store photo in public disk
                    $photoPath = $photo->store('plant-photos', 'public');
                    if ($photoPath) {
                        $newPhotos[] = $photoPath;
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to store plant photo', [
                        'error' => $e->getMessage(),
                        'file_name' => $photo->getClientOriginalName()
                    ]);
                    return back()->with('error', 'Failed to upload photo: ' . $photo->getClientOriginalName());
                }
            }
            
            // Merge with existing photos
            $validated['photos'] = array_merge($existingPhotos, $newPhotos);
        }

        // Update plant
        $plant->update($validated);

        return redirect()
            ->route('admin.plants.edit', $plant->id)
            ->with('success', "Plant '{$plant->name}' updated successfully!");
    }

    /**
     * Delete a photo from the plant's photo array.
     */
    public function deletePhoto(Request $request, $id)
    {
        $plant = Plant::findOrFail($id);
        
        $photoPath = $request->input('photo_path');
        
        if (!$photoPath) {
            return back()->with('error', 'Photo path is required.');
        }

        // Remove from photos array
        $photos = $plant->photos ?? [];
        $photos = array_filter($photos, fn($photo) => $photo !== $photoPath);
        $plant->photos = array_values($photos);
        $plant->save();

        // Delete from storage
        if (Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        return back()->with('success', 'Photo deleted successfully!');
    }
}
