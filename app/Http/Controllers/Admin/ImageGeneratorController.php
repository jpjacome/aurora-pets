<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ImageGeneratorController extends Controller
{
    public function index()
    {
        // Provide plant and pet lists so admin can pick and auto-fill fields
    $plants = \App\Models\Plant::orderBy('name')->get();
    // Show recently modified pets first in the admin selector
    $pets = \App\Models\Pet::with('client')->orderByDesc('updated_at')->get();

        // Normalize items for the view (include an accessible image URL and description)
        $plantItems = $plants->map(function ($p) {
            // decide image candidate: default_photo or first photos entry
            $img = $p->default_photo ?: (is_array($p->photos) && count($p->photos) ? $p->photos[0] : null);
            $imageUrl = null;
            if ($img) {
                if (filter_var($img, FILTER_VALIDATE_URL)) {
                    $imageUrl = $img;
                } else {
                    // Prefer storage disk public if exists
                    try {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img)) {
                            $imageUrl = \Illuminate\Support\Facades\Storage::url($img);
                        } else {
                            $imageUrl = asset($img);
                        }
                    } catch (\Exception $e) {
                        $imageUrl = asset($img);
                    }
                }
            }

            return [
                'id' => $p->id,
                'name' => $p->name,
                'description' => $p->description,
                'image' => $imageUrl,
                'is_active' => (bool) $p->is_active,
            ];
        })->values();

        $petItems = $pets->map(function ($pet) {
            $img = $pet->profile_photo ?: (is_array($pet->photos) && count($pet->photos) ? $pet->photos[0] : null);
            $imageUrl = null;
            if ($img) {
                if (filter_var($img, FILTER_VALIDATE_URL)) {
                    $imageUrl = $img;
                } else {
                    try {
                        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($img)) {
                            $imageUrl = \Illuminate\Support\Facades\Storage::url($img);
                        } else {
                            $imageUrl = asset($img);
                        }
                    } catch (\Exception $e) {
                        $imageUrl = asset($img);
                    }
                }
            }

            return [
                'id' => $pet->id,
                'name' => $pet->name,
                'client' => $pet->client ? $pet->client->client : null,
                'image' => $imageUrl,
            ];
        })->values();

        return view('admin.image-generator', ['plants' => $plantItems, 'pets' => $petItems]);
    }

    // Accepts { dataURL, pet_name, plant_name, metadata }
    public function upload(Request $r)
    {
        $r->validate([ 'dataURL' => 'required|string' ]);

        $data = $r->input('dataURL');
        if (!preg_match('/^data:image\/(png|jpeg);base64,/', $data, $m)) {
            return response()->json(['error' => 'Invalid image data'], 422);
        }

        $mime = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $raw = substr($data, strpos($data, ',') + 1);
        $decoded = base64_decode($raw);
        if ($decoded === false) return response()->json(['error' => 'Could not decode'], 422);

        // Limit size (example: 6MB)
        if (strlen($decoded) > 6 * 1024 * 1024) {
            return response()->json(['error' => 'Image too large'], 413);
        }

        $filename = 'og-' . Str::random(10) . '.' . $mime;
        Storage::disk('public')->put('og-images/' . $filename, $decoded);

        return response()->json([ 'url' => Storage::url('og-images/' . $filename) ]);
    }

    // Server-side render (enqueue job or run inline)
    public function server(Request $r)
    {
        $r->validate([ 'plant_image_url' => 'required|string' ]);
        // For MVP, just return a placeholder.
        // Ideally dispatch new \App\Jobs\GenerateOgImage($payload)
        return response()->json(['status' => 'queued']);
    }
}
