<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ADMIN CLIENTS VIEW - DATA PREVIEW ===\n\n";

// Get all clients with their pets and plants
$clients = \App\Models\Client::with(['pets.plant'])->latest()->limit(5)->get();

echo "Total clients in database: " . \App\Models\Client::count() . "\n";
echo "Showing latest 5 clients:\n\n";

foreach ($clients as $client) {
    echo str_repeat("=", 70) . "\n";
    echo "CLIENT: {$client->client} ({$client->email})\n";
    echo "Created: {$client->created_at->diffForHumans()}\n";
    
    if ($client->phone) echo "Phone: {$client->phone}\n";
    if ($client->address) echo "Address: {$client->address}\n";
    
    echo "\nPETS: {$client->pets->count()}\n";
    
    if ($client->pets->count() > 0) {
        foreach ($client->pets as $pet) {
            echo "\n  🐾 {$pet->name}";
            if ($pet->species) echo " ({$pet->species})";
            echo "\n";
            
            if ($pet->breed) echo "     Breed: {$pet->breed}\n";
            if ($pet->gender) echo "     Gender: {$pet->gender}\n";
            if ($pet->birthday) echo "     Birthday: {$pet->birthday->format('Y-m-d')} ({$pet->birthday->diffForHumans()})\n";
            if ($pet->weight) echo "     Weight: {$pet->weight}\n";
            if ($pet->color) echo "     Colors: " . (is_array($pet->color) ? implode(', ', $pet->color) : $pet->color) . "\n";
            if ($pet->living_space) echo "     Living: {$pet->living_space}\n";
            if ($pet->characteristics) echo "     Traits: " . (is_array($pet->characteristics) ? implode(', ', $pet->characteristics) : $pet->characteristics) . "\n";
            
            if ($pet->plant) {
                echo "     🌿 Plant: {$pet->plant->name}";
                if ($pet->plant->family) echo " ({$pet->plant->family})";
                echo "\n";
            }
            
            if ($pet->profile_slug) {
                echo "     Profile: /profile/{$pet->profile_slug}\n";
            }
            
            if ($pet->deceased) {
                echo "     ⚰️ Deceased: " . ($pet->deceased_at ? $pet->deceased_at->format('Y-m-d') : 'Yes') . "\n";
            }
        }
    } else {
        echo "  (No pets registered)\n";
    }
    
    echo "\n";
}

echo str_repeat("=", 70) . "\n\n";

// Check if old client structure still has pet data
echo "=== CHECKING OLD CLIENT STRUCTURE ===\n\n";
$clientWithOldData = \App\Models\Client::whereNotNull('pet_name')->first();
if ($clientWithOldData) {
    echo "⚠️ WARNING: Found client with old pet data in clients table:\n";
    echo "Client: {$clientWithOldData->client}\n";
    echo "Old pet_name: {$clientWithOldData->pet_name}\n";
    echo "Old pet_species: {$clientWithOldData->pet_species}\n";
    echo "\nThis data is now stored in the pets table.\n";
    echo "The old columns can be removed in Phase 0.8 (cleanup migration).\n";
} else {
    echo "✅ No old pet data found in clients table.\n";
}
