<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find client with 'test5'
echo "=== SEARCHING FOR TEST5 CLIENT ===\n\n";

$client = \App\Models\Client::where('client', 'LIKE', '%test5%')
    ->orWhere('email', 'LIKE', '%test5%')
    ->latest()
    ->first();

if ($client) {
    echo "✅ CLIENT FOUND:\n";
    echo "ID: " . $client->id . "\n";
    echo "Name: " . $client->client . "\n";
    echo "Email: " . $client->email . "\n";
    echo "Phone: " . ($client->phone ?? 'NULL') . "\n";
    echo "Address: " . ($client->address ?? 'NULL') . "\n";
    echo "Profile URL: " . ($client->profile_url ?? 'NULL') . "\n";
    echo "Created: " . $client->created_at . "\n\n";
    
    // Check for pets
    echo "=== CHECKING FOR PETS ===\n\n";
    $pets = \App\Models\Pet::where('client_id', $client->id)->get();
    
    if ($pets->count() > 0) {
        echo "✅ FOUND " . $pets->count() . " PET(S):\n\n";
        foreach ($pets as $pet) {
            echo "Pet ID: " . $pet->id . "\n";
            echo "Name: " . $pet->name . "\n";
            echo "Species: " . ($pet->species ?? 'NULL') . "\n";
            echo "Breed: " . ($pet->breed ?? 'NULL') . "\n";
            echo "Gender: " . ($pet->gender ?? 'NULL') . "\n";
            echo "Birthday: " . ($pet->birthday ?? 'NULL') . "\n";
            echo "Weight: " . ($pet->weight ?? 'NULL') . "\n";
            echo "Color: " . ($pet->color ? json_encode($pet->color) : 'NULL') . "\n";
            echo "Living Space: " . ($pet->living_space ?? 'NULL') . "\n";
            echo "Characteristics: " . ($pet->characteristics ? json_encode($pet->characteristics) : 'NULL') . "\n";
            echo "Plant ID: " . ($pet->plant_id ?? 'NULL') . "\n";
            echo "Plant Test: " . ($pet->plant_test ?? 'NULL') . "\n";
            echo "Plant Number: " . ($pet->plant_number ?? 'NULL') . "\n";
            echo "Metadata: " . ($pet->metadata ? json_encode($pet->metadata) : 'NULL') . "\n";
            echo "Profile Slug: " . ($pet->profile_slug ?? 'NULL') . "\n";
            echo "Photos: " . ($pet->photos ? json_encode($pet->photos) : 'NULL') . "\n";
            echo "Profile Photo: " . ($pet->profile_photo ?? 'NULL') . "\n";
            echo "Deceased: " . ($pet->deceased ? 'YES' : 'NO') . "\n";
            echo "Deceased At: " . ($pet->deceased_at ?? 'NULL') . "\n";
            echo "Created: " . $pet->created_at . "\n";
            
            if ($pet->plant) {
                echo "\n🌿 ASSIGNED PLANT:\n";
                echo "Plant Name: " . $pet->plant->name . "\n";
                echo "Family: " . ($pet->plant->family ?? 'NULL') . "\n";
                echo "Plant Number: " . ($pet->plant->plant_number ?? 'NULL') . "\n";
            }
            echo "\n" . str_repeat("-", 50) . "\n\n";
        }
    } else {
        echo "❌ No pets found for this client\n";
    }
    
} else {
    echo "❌ No client found with 'test5'\n\n";
    echo "=== SHOWING LATEST 5 CLIENTS ===\n\n";
    $latestClients = \App\Models\Client::latest()->limit(5)->get(['id', 'client', 'email']);
    foreach ($latestClients as $c) {
        echo "ID: {$c->id} | Name: {$c->client} | Email: {$c->email}\n";
    }
}
