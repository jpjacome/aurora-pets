<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING OLD CLIENT DATA ===\n\n";

// Get all clients
$clients = \App\Models\Client::all();

echo "Total clients: " . $clients->count() . "\n\n";

foreach ($clients as $client) {
    echo "CLIENT ID: {$client->id}\n";
    echo "Name: {$client->client}\n";
    echo "Email: {$client->email}\n";
    echo "Pets in relationship: {$client->pets->count()}\n";
    
    // Check if old pet data exists in clients table
    if ($client->pet_name) {
        echo "⚠️ OLD STRUCTURE DATA EXISTS:\n";
        echo "  pet_name: {$client->pet_name}\n";
        echo "  pet_species: {$client->pet_species}\n";
        echo "  pet_breed: {$client->pet_breed}\n";
        echo "  gender: {$client->gender}\n";
        echo "  pet_birthday: {$client->pet_birthday}\n";
        echo "  pet_weight: {$client->pet_weight}\n";
        echo "  pet_color: " . ($client->pet_color ? json_encode($client->pet_color) : 'NULL') . "\n";
        echo "  living_space: {$client->living_space}\n";
        echo "  pet_characteristics: " . ($client->pet_characteristics ? json_encode($client->pet_characteristics) : 'NULL') . "\n";
        echo "  plant: {$client->plant}\n";
        echo "  plant_description: " . ($client->plant_description ? substr($client->plant_description, 0, 50) . '...' : 'NULL') . "\n";
    }
    
    echo "\n" . str_repeat("-", 70) . "\n\n";
}
