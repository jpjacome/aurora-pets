<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Test;
use App\Models\Client;
use App\Models\Plant;
use App\Models\Pet;
use Carbon\Carbon;

class AdminController
{
    public function dashboard(Request $request)
    {
        // Default to 7 days, allow changing via dropdown
        $days = (int) $request->input('days', 7);
        if (!in_array($days, [7, 14, 30])) {
            $days = 7;
        }
        
        // Clients statistics
        $totalClients = Client::count();
        $newClients = Client::where('created_at', '>=', Carbon::now()->subDays($days))->count();
        
        // Tests statistics
        $totalTests = Test::count();
        $newTests = Test::where('created_at', '>=', Carbon::now()->subDays($days))->count();
        
        // Pets statistics
        $totalPets = Pet::count();
        $activePets = Pet::where('deceased', false)->count(); // Prevention plans (active pets)
        $deceasedPets = Pet::where('deceased', true)->count(); // Past clients/deceased pets
        
        return view('admin.dashboard', compact(
            'totalClients',
            'newClients',
            'totalTests',
            'newTests',
            'totalPets',
            'activePets',
            'deceasedPets',
            'days'
        ));
    }

    public function tests(Request $request)
    {
        $requested = (int) $request->input('perPage', 15);
        $perPage = $requested <= 0 ? 15 : min($requested, 1000);
        // If user requests 'all' via perPage=all, allow showing all (cap to 5000)
        if ($request->input('perPage') === 'all') {
            $total = Test::count();
            $perPage = min($total ?: 1, 5000);
        }
        $q = trim((string) $request->input('q', ''));
        $query = Test::query();
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('client', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('pet_name', 'like', "%{$q}%")
                  ->orWhere('plant', 'like', "%{$q}%");
            });
        }
        $tests = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        return view('admin.tests', compact('tests'));
    }

    public function clients(Request $request)
    {
        $requested = (int) $request->input('perPage', 15);
        $perPage = $requested <= 0 ? 15 : min($requested, 1000);
        if ($request->input('perPage') === 'all') {
            $total = Client::count();
            $perPage = min($total ?: 1, 5000);
        }
        $q = trim((string) $request->input('q', ''));
        $query = Client::with(['pets.plant']); // Eager load pets and their plants
        
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('client', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  // Search in related pets table
                  ->orWhereHas('pets', function($petQuery) use ($q) {
                      $petQuery->where('name', 'like', "%{$q}%")
                               ->orWhere('species', 'like', "%{$q}%")
                               ->orWhere('breed', 'like', "%{$q}%");
                  });
            });
        }
        $clients = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();
        return view('admin.clients', compact('clients'));
    }

    public function createClient(Request $request)
    {
        $data = $request->validate([
            'client' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        Client::create($data + ['profile_url' => null]);

        return redirect('/admin/clients')->with('success', 'Client created successfully!');
    }

    public function deleteMultipleClients(Request $request)
    {
        $data = $request->validate([
            'client_ids' => 'required|array',
            'client_ids.*' => 'integer|exists:clients,id',
        ]);

        $count = Client::whereIn('id', $data['client_ids'])->delete();

        return redirect('/admin/clients')->with('success', "Deleted {$count} client(s) successfully!");
    }

    public function plants(Request $request)
    {
        $requested = (int) $request->input('perPage', 15);
        $perPage = $requested <= 0 ? 15 : min($requested, 1000);
        if ($request->input('perPage') === 'all') {
            $total = Plant::count();
            $perPage = min($total ?: 1, 5000);
        }
        $q = trim((string) $request->input('q', ''));
        $query = Plant::withCount('pets'); // Count how many pets have this plant
        
        if ($q !== '') {
            $query->where(function($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('family', 'like', "%{$q}%")
                  ->orWhere('species', 'like', "%{$q}%");
            });
        }
        $plants = $query->orderBy('name', 'asc')->paginate($perPage)->withQueryString();
        return view('admin.plants', compact('plants'));
    }

    public function createPlant(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'scientific_name' => 'nullable|string|max:255',
            'family' => 'nullable|string|max:255',
            'species' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'substrate_info' => 'nullable|string',
            'lighting_info' => 'nullable|string',
            'light_requirement' => 'nullable|string|max:255',
            'watering_info' => 'nullable|string',
            'water_requirement' => 'nullable|string|max:255',
            'plant_type' => 'nullable|string|in:Con flor,Foliar',
            'difficulty' => 'nullable|string|in:Baja,Media,Alta',
            'origin' => 'nullable|string|max:255',
            'plant_number' => 'nullable|integer|min:1|max:27|unique:plants,plant_number',
            'slug' => 'nullable|string|max:255|unique:plants,slug',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active') ? true : false;
        
        // Auto-generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        Plant::create($data);

        return redirect()->route('admin.plants')->with('success', 'Plant created successfully!');
    }

    public function deleteMultiplePlants(Request $request)
    {
        $data = $request->validate([
            'plant_ids' => 'required|array',
            'plant_ids.*' => 'integer|exists:plants,id',
        ]);

        $count = Plant::whereIn('id', $data['plant_ids'])->delete();

        return redirect()->route('admin.plants')->with('success', "Deleted {$count} plant(s) successfully!");
    }

    public function settings()
    {
        return view('admin.settings');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
        ]);

        $user = auth()->user();
        $user->update($validated);

        return redirect('/admin/settings')->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $user = auth()->user();

        // Check if current password is correct
        if (!password_verify($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $user->update([
            'password' => bcrypt($validated['password'])
        ]);

        return redirect('/admin/settings')->with('success', 'Password changed successfully!');
    }
}
