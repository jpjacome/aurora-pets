<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\QueueCampaignJob;
use App\Jobs\SendCampaignBatchJob;

class EmailCampaignController extends Controller
{
    public function index()
    {
        $campaigns = EmailCampaign::latest()->paginate(20);
        return view('admin.email_campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        return view('admin.email_campaigns.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'mailable_class' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'template_body' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'recipient_type' => 'nullable|string',
            'manual_emails' => 'nullable|string',
            'selected_clients' => 'nullable|array'
        ]);

        $data['created_by'] = Auth::id();

        // Merge metadata provider and recipient filter; metadata[] from the form becomes array on request
        $metadata = $request->input('metadata', []);
        // normalize recipient type and store manual recipients or selected client ids in metadata
        $recipientType = $data['recipient_type'] ?? ($metadata['recipient_type'] ?? ($metadata['recipient_filter'] ?? 'all'));
        $metadata['recipient_type'] = $recipientType;
        if (!empty($data['manual_emails'])) {
            // Store manual_emails as-is (will be JSON string from new UI or old comma-separated format)
            $metadata['manual_emails'] = $data['manual_emails'];
        }
        if (!empty($data['selected_clients'])) {
            $metadata['selected_client_ids'] = array_map('intval', $data['selected_clients']);
        }

        $campaign = EmailCampaign::create(array_merge($data, ['metadata' => $metadata]));

        return redirect()->route('admin.email-campaigns.index')->with('success', 'Campaign created');
    }

    public function show(EmailCampaign $emailCampaign)
    {
        $campaign = $emailCampaign->loadCount(['messages']);
        $messages = $emailCampaign->messages()->latest()->paginate(20);
        
        // Calculate stats with fresh data - use whereIn to avoid orWhere scope issues
        $stats = [
            'total' => $campaign->messages_count,
            'delivered' => $emailCampaign->messages()->whereIn('status', ['delivered', 'opened', 'clicked'])->count(),
            'opened' => $emailCampaign->messages()->whereNotNull('opened_at')->count(),
            'clicked' => $emailCampaign->messages()->whereNotNull('clicked_at')->count(),
        ];
        
        // Debug logging
        \Log::info('Campaign stats', [
            'campaign_id' => $campaign->id,
            'stats' => $stats,
            'messages_with_opened_at' => $emailCampaign->messages()->select('id', 'email', 'status', 'opened_at', 'clicked_at')->get()->toArray()
        ]);
        
        return view('admin.email_campaigns.show', compact('campaign', 'messages', 'stats'));
    }

    public function run(Request $request, EmailCampaign $emailCampaign)
    {
        $filter = $request->input('recipient_type', $emailCampaign->metadata['recipient_type'] ?? ($emailCampaign->metadata['recipient_filter'] ?? 'all'));
        $provider = $request->input('provider', $emailCampaign->metadata['provider'] ?? env('CAMPAIGN_MAILER', 'smtp'));
        // persist chosen provider into campaign metadata
        if (($emailCampaign->metadata['provider'] ?? null) !== $provider) {
            $meta = $emailCampaign->metadata ?? [];
            $meta['provider'] = $provider;
            $emailCampaign->update(['metadata' => $meta]);
        }
        $recipients = collect();
        switch ($filter) {
            case 'manual':
                $manual = $request->input('manual_emails', $emailCampaign->metadata['manual_emails'] ?? []);
                
                // Debug logging
                \Log::info('Manual emails received', [
                    'type' => gettype($manual),
                    'value' => $manual,
                    'is_string' => is_string($manual),
                    'from_request' => $request->has('manual_emails'),
                    'from_metadata' => !$request->has('manual_emails')
                ]);
                
                // Handle JSON array format from new UI: [{"email":"...","name":"..."},...]
                if (is_string($manual)) {
                    $decoded = json_decode($manual, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        // JSON format - array of objects with email/name
                        foreach ($decoded as $recipient) {
                            $email = trim($recipient['email'] ?? '');
                            $name = trim($recipient['name'] ?? '');
                            
                            // Skip empty emails
                            if (empty($email)) continue;
                            
                            // Validate email format
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                \Log::warning('Skipping recipient with invalid email', ['email' => $email, 'name' => $name]);
                                continue;
                            }
                            
                            // Name is required
                            if (empty($name)) {
                                \Log::warning('Skipping recipient with missing name', ['email' => $email]);
                                continue;
                            }
                            
                            $emailCampaign->messages()->create([
                                'client_id' => null,
                                'email' => $email,
                                'metadata' => ['client_name' => $name, 'provider' => $provider],
                            ]);
                        }
                    } else {
                        // Fallback: old simple "email,name" format (one per line)
                        $lines = array_values(array_filter(array_map('trim', preg_split('/[\r\n]+/', $manual))));
                        foreach ($lines as $line) {
                            $parts = array_map('trim', explode(',', $line, 2));
                            $email = $parts[0] ?? '';
                            $name = $parts[1] ?? '';
                            
                            if (empty($email)) continue;
                            
                            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                \Log::warning('Skipping recipient with invalid email', ['email' => $email, 'line' => $line]);
                                continue;
                            }
                            
                            if (empty($name)) {
                                \Log::warning('Skipping recipient with missing name', ['email' => $email, 'line' => $line]);
                                continue;
                            }
                            
                            $emailCampaign->messages()->create([
                                'client_id' => null,
                                'email' => $email,
                                'metadata' => ['client_name' => $name, 'provider' => $provider],
                            ]);
                        }
                    }
                }
                break;
            case 'clients_selected':
                $ids = $request->input('selected_clients', $emailCampaign->metadata['selected_client_ids'] ?? []);
                $clients = Client::whereIn('id', $ids)->get();
                foreach ($clients as $recipient) {
                    $emailCampaign->messages()->create([
                        'client_id' => $recipient->id,
                        'email' => $recipient->email,
                        'metadata' => ['client_name' => $recipient->client, 'provider' => $provider],
                    ]);
                }
                break;
            case 'subscribed':
                $recipients = Client::whereNull('unsubscribed_at')->get();
                break;
            case 'all':
            default:
                $recipients = Client::all();
                break;
        }

        // fallback for recipients collection (all/subscribed)
        foreach ($recipients as $recipient) {
            $emailCampaign->messages()->create([
                'client_id' => $recipient->id,
                'email' => $recipient->email,
                'metadata' => ['client_name' => $recipient->client, 'provider' => $provider],
            ]);
        }

        // dispatch a job to process the campaign
        try {
            QueueCampaignJob::dispatch($emailCampaign);
            $emailCampaign->update(['status' => 'running']);
            $message = 'Campaign completed! Check the campaign details to see delivery status.';
        } catch (\Exception $e) {
            \Log::error('Campaign dispatch failed', ['campaign_id' => $emailCampaign->id, 'error' => $e->getMessage()]);
            $emailCampaign->update(['status' => 'failed']);
            $message = 'Campaign started but some emails may have failed. Check logs for details.';
        }

        return back()->with('success', $message);
    }

    public function preview(Request $request)
    {
        $data = $request->validate([
            'subject' => 'nullable|string',
            'template_body' => 'nullable|string',
        ]);

        // Build a sample message using the GenericCampaignMailable
        $campaign = new EmailCampaign(['subject' => $data['subject'], 'template_body' => $data['template_body'], 'metadata' => []]);
        $message = new EmailMessage(['message_uuid' => \Illuminate\Support\Str::uuid()->toString(), 'metadata' => ['client_name' => 'Test User'], 'email' => 'test@example.com']);

        $mailable = new \App\Mail\GenericCampaignMailable($campaign, $message);
        // Use renderBody so preview does not depend on a configured MAIL_MAILER
        $html = $mailable->renderBody();

        return response()->json(['html' => $html]);
    }

    public function schedule(Request $request, EmailCampaign $emailCampaign)
    {
        $data = $request->validate(['scheduled_at' => 'required|date']);

        $emailCampaign->update(['scheduled_at' => $data['scheduled_at'], 'status' => 'scheduled']);

        return back()->with('success', 'Campaign scheduled');
    }

    public function stop(Request $request, EmailCampaign $emailCampaign)
    {
        $emailCampaign->update(['status' => 'cancelled']);
        // optionally delete unprocessed email_messages
        $emailCampaign->messages()->whereIn('status', ['queued', 'processing'])->update(['status' => 'cancelled']);
        return back()->with('success', 'Campaign stopped');
    }

    public function destroy(EmailCampaign $emailCampaign)
    {
        $emailCampaign->messages()->delete();
        $emailCampaign->delete();

        return redirect()->route('admin.email-campaigns.index')->with('success', 'Campaign deleted');
    }

    public function recipients(EmailCampaign $emailCampaign)
    {
        $recipients = $emailCampaign->messages()->with('client')->paginate(50);
        return response()->json($recipients);
    }

    public function recipientsPreview(Request $request)
    {
        $data = $request->validate(['filter' => 'nullable|string', 'selected_clients' => 'nullable|array', 'manual_emails' => 'nullable|string']);
        $filter = $data['filter'] ?? 'all';
        switch ($filter) {
            case 'subscribed':
                $count = Client::whereNull('unsubscribed_at')->count();
                break;
            case 'clients_selected':
                $count = is_array($data['selected_clients'] ?? null) ? count($data['selected_clients']) : 0;
                break;
            case 'manual':
                $emails = $data['manual_emails'] ?? '';
                $arr = array_values(array_filter(array_map('trim', preg_split('/[\r\n,;]+/', $emails))));
                $count = count($arr);
                break;
            case 'all':
            default:
                $count = Client::count();
                break;
        }
        return response()->json(['count' => $count]);
    }

    /**
     * Return paginated clients for client selection (JSON) to support infinite scroll
     */
    public function clientsList(Request $request)
    {
        $perPage = 50;
        $page = max(1, (int) $request->query('page', 1));
        $search = $request->query('search', '');
        
        $query = Client::select('id', 'client', 'email')
            ->with('pets:id,client_id,name'); // Eager load pets
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('client', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhereHas('pets', function($petQuery) use ($search) {
                      $petQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        $clients = $query->orderBy('client')->paginate($perPage, ['*'], 'page', $page);
        
        // Format data to include pet names
        $data = $clients->items();
        $formatted = array_map(function($client) {
            return [
                'id' => $client->id,
                'name' => $client->client,
                'email' => $client->email,
                'pets' => $client->pets->pluck('name')->toArray()
            ];
        }, $data);
        
        return response()->json([
            'data' => $formatted,
            'current_page' => $clients->currentPage(),
            'last_page' => $clients->lastPage(),
            'next_page_url' => $clients->nextPageUrl(),
        ]);
    }

    public function resendSelected(Request $request, EmailCampaign $emailCampaign)
    {
        $ids = $request->validate(['message_ids' => 'required|array'])['message_ids'];
        $ids = array_map('intval', $ids);
        // Dispatch send job for selected IDs using the batch job
        SendCampaignBatchJob::dispatch($ids);
        return back()->with('success', count($ids) . ' messages re-queued for resend');
    }
}
