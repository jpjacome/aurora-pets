<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Plant;

class PlantDescriptionController extends Controller
{
    /**
     * Return a plant description for the given plant name or slug.
     * Query params: name (plant name or slug)
     */
    public function show(Request $request)
    {
        $name = $request->query('name');
        if (!$name) {
            return response()->json(['description' => null]);
        }

        // Try exact name or slug first
        $plant = Plant::where('name', $name)->orWhere('slug', $name)->first();

        // Try a slugified lookup if direct match failed
        if (!$plant) {
            $slug = Str::slug($name);
            $plant = Plant::where('slug', $slug)->first();
        }

        // As a last resort, try a case-insensitive name match
        if (!$plant) {
            $plant = Plant::whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->first();
        }

        $desc = $plant ? ($plant->description ?? null) : null;

        return response()->json(['description' => $desc]);
    }
}
