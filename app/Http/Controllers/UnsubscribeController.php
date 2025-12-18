<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EmailMessage;
use Illuminate\Http\Request;

class UnsubscribeController extends Controller
{
    // Generic unsubscribe by client id and message token to ensure it's a valid delivery
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

        $client->update(['unsubscribed_at' => now()]);

        return view('unsubscribe.done', ['client' => $client]);
    }
}
