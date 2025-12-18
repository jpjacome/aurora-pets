<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Client;
use Illuminate\Support\Str;
use App\Mail\PlantScanResultMail;
use Illuminate\Support\Facades\Mail;

class TestController
{
    /**
     * Store a new test run and create client if needed.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'client' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'pet_name' => 'required|string|max:255',
            'pet_species' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:50',
            'pet_birthday' => 'nullable|date',
            'pet_breed' => 'nullable|string|max:255',
            'pet_weight' => 'nullable|string|max:50',
            'pet_color' => 'nullable|array',
            'pet_color.*' => 'string|max:100',
            'living_space' => 'nullable|string|max:255',
            'pet_characteristics' => 'nullable|array',
            'pet_characteristics.*' => 'string|max:255',
            'plant_test' => 'nullable|string|max:255',
            'plant' => 'nullable|string|max:255',
            'plant_description' => 'nullable|string',
            'plant_number' => 'nullable|integer',
            'metadata' => 'nullable|array',
        ]);

    // Create the test row (generate a short share token)
    $data['share_token'] = Str::random(10);
    $test = Test::create($data);

        // Create or find client (owner)
        $clientCreated = false;
        $client = Client::where('email', $data['email'])->first();
        if (!$client) {
            $client = Client::create([
                'client' => $data['client'] ?? $data['email'],
                'email' => $data['email'],
                'phone' => null,
                'address' => null,
                'profile_url' => null,
            ]);
            $clientCreated = true;
        }
        
        // Find the assigned plant
        $plant = \App\Models\Plant::where('name', $data['plant'])->first();
        
        // Find existing pet by name (case-insensitive) or create new
        $pet = \App\Models\Pet::where('client_id', $client->id)
            ->whereRaw('LOWER(name) = ?', [strtolower($data['pet_name'])])
            ->first();
        
        $petCreated = false;
        if ($pet) {
            // UPDATE existing pet with new test results
            $pet->update([
                'plant_id' => $plant?->id,
                'plant_test' => $data['plant_test'] ?? null,
                'plant_number' => $data['plant_number'] ?? null,
                'species' => $data['pet_species'] ?? $pet->species,
                'breed' => $data['pet_breed'] ?? $pet->breed,
                'gender' => $data['gender'] ?? $pet->gender,
                'weight' => $data['pet_weight'] ?? $pet->weight,
                'color' => $data['pet_color'] ?? $pet->color,
                'living_space' => $data['living_space'] ?? $pet->living_space,
                'characteristics' => $data['pet_characteristics'] ?? $pet->characteristics,
                'metadata' => $data['metadata'] ?? null,
                // Don't update birthday - that shouldn't change!
                // Don't update photos - preserve existing
            ]);
        } else {
            // CREATE new pet
            $pet = \App\Models\Pet::create([
                'client_id' => $client->id,
                'plant_id' => $plant?->id,
                'name' => $data['pet_name'],
                'species' => $data['pet_species'] ?? null,
                'breed' => $data['pet_breed'] ?? null,
                'gender' => $data['gender'] ?? null,
                'birthday' => $data['pet_birthday'] ?? null,
                'weight' => $data['pet_weight'] ?? null,
                'color' => $data['pet_color'] ?? null,
                'living_space' => $data['living_space'] ?? null,
                'characteristics' => $data['pet_characteristics'] ?? null,
                'plant_test' => $data['plant_test'] ?? null,
                'plant_number' => $data['plant_number'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'profile_slug' => Str::slug($data['pet_name']) . '-' . Str::random(6),
            ]);
            $petCreated = true;
        }

        // OG image generation removed - using client-side Canvas generation instead

        $shareUrl = config('app.url') . '/plantscan/result/' . $test->share_token;

        // Send email to the address provided in the test row. Use queue when available.
        try {
            $mailPayload = [
                'client' => $test->client,
                'email' => $test->email,
                'pet_name' => $test->pet_name,
                'pet_species' => $test->pet_species,
                'gender' => $test->gender,
                'pet_birthday' => $test->pet_birthday?->toDateString() ?? null,
                'pet_breed' => $test->pet_breed,
                'pet_weight' => $test->pet_weight,
                'pet_color' => $test->pet_color,
                'living_space' => $test->living_space,
                'pet_characteristics' => $test->pet_characteristics,
                'plant_test' => $test->plant_test,
                'plant' => $test->plant,
                'plant_description' => $test->plant_description,
                'plant_number' => $test->plant_number,
                'metadata' => $test->metadata,
                'og_image' => $test->og_image ? $test->og_image : null,
                'share_url' => $shareUrl,
            ];

            // Use a safe mailer in case the configured default mailer isn't present in config.mail.mailers
            $defaultMailer = config('mail.default');
            $available = config('mail.mailers', []);
            $useMailer = array_key_exists($defaultMailer, $available) ? $defaultMailer : (array_key_exists('smtp', $available) ? 'smtp' : $defaultMailer);

            Mail::mailer($useMailer)->queue(new PlantScanResultMail($mailPayload));
        } catch (\Throwable $e) {
            // Don't break the response if mailing fails; log for later inspection
            logger()->warning('Failed to queue PlantScanResultMail for test '.$test->id.': '.$e->getMessage());
        }

        return response()->json([
            'success' => true,
            'test_id' => $test->id,
            'client_created' => $clientCreated,
            'pet_created' => $petCreated,
            'pet_id' => $pet->id,
            'share_url' => $shareUrl,
        ], 201);
    }
}
