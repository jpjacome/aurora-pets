<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Show the form for editing the specified client.
     */
    public function edit($id)
    {
        $client = Client::withCount('pets')
            ->with(['campaigns' => function($query) {
                $query->select('email_campaigns.id', 'email_campaigns.name', 'email_campaigns.subject', 'email_campaigns.status')
                      ->withPivot('status', 'delivered_at', 'opened_at', 'clicked_at');
            }])
            ->findOrFail($id);
        
        return view('admin.clients.edit', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);
        
        $validated = $request->validate([
            'client' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:clients,email,' . $id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
        ]);

        // Update client
        $client->update($validated);

        return redirect()
            ->route('admin.clients')
            ->with('success', "Client '{$client->client}' updated successfully!");
    }
}
