<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmailTrackingController extends Controller
{
    public function open($uuid)
    {
        $msg = EmailMessage::where('message_uuid', $uuid)->first();
        if ($msg && !$msg->opened_at) {
            $msg->update(['opened_at' => now(), 'status' => 'opened']);
        }

        // transparent 1x1 GIF
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function click(Request $request, $uuid)
    {
        $msg = EmailMessage::where('message_uuid', $uuid)->first();
        if ($msg) {
            $updates = ['clicked_at' => now(), 'status' => 'clicked'];
            if (!$msg->opened_at) {
                $updates['opened_at'] = now();
            }
            $msg->update($updates);

            $redirect = $request->query('u') ?? ($msg->metadata['redirect'] ?? null);
            if ($redirect && filter_var($redirect, FILTER_VALIDATE_URL)) {
                return redirect()->away($redirect);
            }
        }

        abort(404);
    }
}
