<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ADMIN CLIENTS VIEW - UPDATED DISPLAY ===\n\n";

$clients = \App\Models\Client::with(['pets.plant'])->get();

foreach ($clients as $c) {
    echo str_repeat("=", 70) . "\n";
    echo "CLIENT: {$c->client} ({$c->email})\n";
    echo str_repeat("=", 70) . "\n";
    
    $hasPetsInRelationship = $c->pets->count() > 0;
    $hasOldPetData = !empty($c->pet_name);
    
    if ($hasPetsInRelationship) {
        echo "✅ NEW STRUCTURE (pets table):\n";
        echo "   Pets: {$c->pets->count()}\n\n";
        foreach ($c->pets as $pet) {
            echo "   🐾 {$pet->name} ({$pet->species})\n";
            echo "      Breed: {$pet->breed}\n";
            echo "      Weight: {$pet->weight}\n";
            echo "      Colors: " . (is_array($pet->color) ? implode(', ', $pet->color) : $pet->color) . "\n";
            echo "      Traits: " . (is_array($pet->characteristics) ? implode(', ', $pet->characteristics) : $pet->characteristics) . "\n";
            if ($pet->plant) {
                echo "      🌿 Plant: {$pet->plant->name}\n";
            }
            echo "\n";
        }
    } elseif ($hasOldPetData) {
        echo "⚠️ OLD STRUCTURE (clients table columns):\n";
        echo "   🐾 {$c->pet_name} ({$c->pet_species})\n";
        echo "      Breed: {$c->pet_breed}\n";
        echo "      Weight: {$c->pet_weight}\n";
        echo "      Colors: " . (is_array($c->pet_color) ? implode(', ', $c->pet_color) : $c->pet_color) . "\n";
        echo "      Traits: " . (is_array($c->pet_characteristics) ? implode(', ', $c->pet_characteristics) : $c->pet_characteristics) . "\n";
        echo "      🌿 Plant: {$c->plant}\n";
        echo "      Description: " . substr($c->plant_description, 0, 80) . "...\n";
        echo "\n";
    } else {
        echo "❌ No pets registered\n\n";
    }
}

echo str_repeat("=", 70) . "\n";
echo "\n✅ The blade template will now show:\n";
echo "   - NEW clients (test5): Pet data from pets table with relationship\n";
echo "   - OLD clients (JUAN PABLO, Eduarda): Pet data from clients columns\n";
echo "   - OLD data shows warning badge: '⚠️ Legacy data (needs migration)'\n";
echo "   - OLD data has yellow left border (vs orange for new)\n";
