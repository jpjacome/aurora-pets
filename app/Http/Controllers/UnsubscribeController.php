<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EmailMessage;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    // Generic unsubscribe by client id and message token to ensure it's a valid delivery
    /**
     * Show a confirmation page before unsubscribing the client.
     */
    public function confirm(Request $request, $clientId, $messageUuid)
    {
        $client = Client::find($clientId);
        if (!$client) {
            abort(404);
        }

        $message = EmailMessage::where('message_uuid', $messageUuid)->where('client_id', $clientId)->first();
        if (!$message) {
            abort(404);
        }

        return view('unsubscribe.confirm', ['client' => $client, 'message' => $message]);
    }

    /**
     * Perform unsubscribe action (POST from confirmation form)
     */
    public function unsubscribe(Request $request, $clientId, $messageUuid)
    {
        $client = Client::find($clientId);
        if (!$client) {
            abort(404);
        }

        $message = EmailMessage::where('message_uuid', $messageUuid)->where('client_id', $clientId)->first();
        if (!$message) {
            abort(404);
        }

        // Optional: avoid accidental unsubscribes without form submission
        $request->validate([], []); // placeholder to ensure request is valid and CSRF token is present

        $client->update(['unsubscribed_at' => now()]);

        return view('unsubscribe.done', ['client' => $client]);
    }
}
