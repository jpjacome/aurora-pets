<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;

class PlantScanResultController extends Controller
{
    public function show($token)
    {
        $test = Test::where('share_token', $token)->firstOrFail();

        // Build OG data
        $og = [
            'title' => 'La planta de ' . ($test->pet_name ?: 'tu mascota') . ' — ' . ($test->plant ?: 'Schefflera'),
            'description' => $test->plant_description ?: 'Descubre la planta que representa a tu mascota.',
            'image' => $test->og_image_url ?: url('/assets/plantscan/imgs/11.png'),
            'url' => config('app.url') . '/plantscan/result/' . $token,
        ];

        return view('plantscan.result', ['test' => $test, 'og' => $og]);
    }
}
