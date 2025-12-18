<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receive(Request $request, string $provider)
    {
        // Basic scaffold: log the provider event
        Log::info("Webhook received from: {$provider}", $request->all());

        if (strtolower($provider) === 'brevo') {
            $this->handleBrevoWebhook($request);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleBrevoWebhook(Request $request)
    {
        // Optional signature verification
        $secret = config('services.brevo.webhook_secret') ?? env('BREVO_WEBHOOK_SECRET');
        $signatureHeader = $request->header('X-Mailin-Signature') ?: $request->header('X-Brevo-Signature');
        if ($secret && $signatureHeader) {
            $computed = base64_encode(hash_hmac('sha256', $request->getContent(), $secret, true));
            if (!hash_equals($computed, $signatureHeader)) {
                Log::warning('Brevo webhook signature mismatch');
                return; // ignore non-matching signatures
            }
        }

        $payload = $request->json()->all();

        // Brevo can send an array of event objects or an object. Normalize.
        $events = is_array($payload) ? $payload : [$payload];

        foreach ($events as $event) {
            // Brevo transactional webhooks may include 'messageId' or 'messageId' nested
            $messageId = $event['messageId'] ?? $event['message_id'] ?? ($event['message'] ?? null);
            if (!$messageId && isset($event['msgid'])) {
                $messageId = $event['msgid'];
            }

            if (!$messageId) {
                continue;
            }

            $emailMessage = \App\Models\EmailMessage::where('provider_id', $messageId)->first();
            if (!$emailMessage) {
                // Try to match by other means: email + campaign
                $email = $event['email'] ?? $event['recipient'] ?? null;
                if ($email) {
                    $emailMessage = \App\Models\EmailMessage::where('email', $email)->latest()->first();
                }
            }
            if (!$emailMessage) continue;

            $status = strtolower($event['event'] ?? $event['type'] ?? '');
            
            // Log the webhook event for debugging
            Log::info('Processing Brevo webhook event', [
                'event' => $status,
                'email' => $event['email'] ?? 'unknown',
                'message_id' => $messageId ?? 'none'
            ]);
            
            switch ($status) {
                case 'delivered':
                case 'delivered_event':
                    $emailMessage->update(['status' => 'delivered', 'delivered_at' => now()]);
                    break;
                case 'open':
                case 'opened':
                case 'open_event':
                    if (!$emailMessage->opened_at) {
                        $emailMessage->update(['status' => 'opened', 'opened_at' => now()]);
                    }
                    break;
                case 'click':
                case 'clicked':
                case 'click_event':
                    // Set both opened_at and clicked_at (click implies open)
                    $updates = ['status' => 'clicked', 'clicked_at' => now()];
                    if (!$emailMessage->opened_at) {
                        $updates['opened_at'] = now();
                    }
                    $emailMessage->update($updates);
                    break;
                case 'hard_bounce':
                case 'soft_bounce':
                case 'bounce':
                    $emailMessage->update(['status' => 'bounced']);
                    break;
                case 'spam':
                case 'complaint':
                    $emailMessage->update(['status' => 'failed']);
                    break;
                default:
                    // other event types: unsubscribed, postponed etc
                    Log::info('Unhandled Brevo webhook event', ['event' => $status]);
            }
        }
    }
}
