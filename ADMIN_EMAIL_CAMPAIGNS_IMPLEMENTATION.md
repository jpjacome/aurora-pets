# Admin Email Campaigns Implementation Plan

Status: Draft — Add migrations, admin UI and job pipeline, tracking, monitoring, and tests.

---

## Goals

- Add a complete admin-managed email campaign feature for sending bulk emails to `clients` and other recipients.
- Use existing mailables (e.g. `PlantScanResultWithImageMail`) where possible and allow custom templates for admin-driven campaigns.
- Track per-message status (queued, delivered, failed, bounced, opened, clicked).
- Use queues and chunking for safe bulk delivery.
- Basic tracking (open pixel and redirect click tracking) plus optional provider webhook integration.

---

## Scope

- 1. MVP: campaigns CRUD, scheduling, queued sending, email tracking (open/click), basic analytics, unsubscribe handling.
- 2. Advanced: provider webhooks, bounce handling, unsubscribe management, A/B tests, segmentation and scheduling.

---

## Database Schema

Two main tables: `email_campaigns` and `email_messages`.

### `email_campaigns`
- id (bigint) PK
- name (string) — descriptive name
- mailable_class (string nullable) — map to existing Mailable classes or a generic template handler
- subject (string nullable)
- template_body (text nullable) — raw HTML/markdown (store sanitized) if using custom templates
- attachments (json nullable) — stored file paths
- status (enum) — draft | scheduled | running | completed | cancelled
- scheduled_at (timestamp nullable)
- created_by (foreign key to `users`) — optional
- metadata (json nullable) — arbitrary campaign options
- created_at, updated_at

Index suggestions: index on `status`, `scheduled_at`.

### `email_messages` (one row per sent email)
- id (bigint) PK
- campaign_id (FK to email_campaigns) — cascade on delete
- client_id (FK to clients) nullable — link where present
- email (string)
- message_uuid (uuid) — unique token for Open/Click tracking
- provider_id (string nullable) — message-id from provider
- status (enum) — queued | processing | delivered | failed | bounced | opened
- attempts (int) — send attempts
- error (text nullable)
- sent_at (timestamp nullable)
- delivered_at (timestamp nullable)
- opened_at (timestamp nullable)
- clicked_at (timestamp nullable)
- metadata (json nullable) — e.g. recipient personalization data or redirect URLs
- created_at, updated_at

Index: `campaign_id`, `email`, `message_uuid`, `status`.

---

## Example Migrations

Create the migrations via:
```
php artisan make:migration create_email_campaigns_table
php artisan make:migration create_email_messages_table
```

Then add these schema details (simplified):

```php
Schema::create('email_campaigns', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('mailable_class')->nullable();
    $table->string('subject')->nullable();
    $table->text('template_body')->nullable();
    $table->json('attachments')->nullable();
    $table->enum('status', ['draft', 'scheduled', 'running', 'completed', 'cancelled'])->default('draft');
    $table->timestamp('scheduled_at')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->json('metadata')->nullable();
    $table->timestamps();
});

Schema::create('email_messages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
    $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
    $table->string('email');
    $table->uuid('message_uuid')->index();
    $table->string('provider_id')->nullable();
    $table->enum('status', ['queued', 'processing', 'delivered', 'failed', 'bounced', 'opened'])->default('queued');
    $table->integer('attempts')->default(0);
    $table->text('error')->nullable();
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('delivered_at')->nullable();
    $table->timestamp('opened_at')->nullable();
    $table->timestamp('clicked_at')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

---

## Models

- `app/Models/EmailCampaign.php`:
  - Fillable: `name, mailable_class, subject, template_body, attachments, status, scheduled_at, metadata`
  - Relationships: hasMany `email_messages`

- `app/Models/EmailMessage.php`:
  - Fillable: `campaign_id, client_id, email, message_uuid, provider_id, status, attempts, metadata`
  - Relationships: belongsTo `campaign`, belongsTo `client` (optional)
  - Add UUID generator on `creating` event to set `message_uuid`

---

## Routes

Under `admin` prefix:

```php
Route::prefix('admin')->middleware(['auth','can:admin'])->group(function () {
    Route::resource('email-campaigns', EmailCampaignController::class)->names('admin.email-campaigns');
    Route::post('email-campaigns/{campaign}/run', [EmailCampaignController::class, 'run'])->name('admin.email-campaigns.run');
    Route::post('email-campaigns/{campaign}/schedule', [EmailCampaignController::class, 'schedule'])->name('admin.email-campaigns.schedule');
    Route::post('email-campaigns/{campaign}/stop', [EmailCampaignController::class, 'stop'])->name('admin.email-campaigns.stop');

    // Recipients listing/search API
    Route::get('email-campaigns/{campaign}/recipients', [EmailCampaignController::class, 'recipients'])->name('admin.email-campaigns.recipients');
});

// Tracking endpoints (public)
Route::get('/email/track/open/{uuid}', [EmailTrackingController::class, 'open'])->name('email.track.open');
Route::get('/r/{uuid}', [EmailTrackingController::class, 'click'])->name('email.track.click');
```

---

## Controllers & Backend Flow

1. `EmailCampaignController` — admin controllers. Responsibilities:
   - index / create / edit / delete campaigns.
   - When `run()` called: build recipients (filter clients table), create `EmailMessage` records for each recipient, and dispatch `QueueCampaignJob`.
   - `schedule()`: set `scheduled_at` and schedule `QueueCampaignJob` with a delay (e.g., dispatchAt)
   - `recipients()`: returns paginated list with status filter.

2. `EmailTrackingController` — handles open/tracking pixel and click redirects.

---

## Job Pipeline

- `QueueCampaignJob`: reads queued messages for a campaign, splits into batches (e.g., 100 emails per batch), and dispatches `SendCampaignBatchJob` for each batch.

- `SendCampaignBatchJob`: accepts a list of `email_message` ids, iterates them and for each one dispatches `SendCampaignEmailJob` or directly processes them sequentially depending on job settings).

- `SendCampaignEmailJob` (`ShouldQueue`): sends the message using `Mail::to($message->email)->send($mailable)` or `queue()`.
  - On success: update `status = delivered` and `delivered_at` (and save `provider_id` if available).
  - On failure: increment `attempts`, set `status = failed` and `error` (or retry).

---

## Sample `SendCampaignEmailJob`

```php
class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public EmailMessage $emailMessage) {}

    public function handle(): void
    {
        $campaign = $this->emailMessage->campaign;
        $data = array_merge($campaign->metadata ?? [], $this->emailMessage->metadata ?? []);

        // Build Mailable based on campaign
        $mailable = $this->buildMailable($campaign, $data);

        try {
            Mail::to($this->emailMessage->email)->send($mailable);

            $this->emailMessage->update([
                'status' => 'delivered',
                'delivered_at' => now(),
            ]);
        } catch (\Exception $e) {
            $this->emailMessage->increment('attempts');
            $this->emailMessage->update(['status' => 'failed', 'error' => $e->getMessage()]);
            throw $e; // let the queue reattempt if configured
        }
    }

    protected function buildMailable(EmailCampaign $campaign, array $data)
    {
        if ($campaign->mailable_class) {
            // instantiate with data, if constructor requires it
            return new $campaign->mailable_class($data);
        }

        // fallback: use a generic in-project mailable that uses $campaign->template_body
        return new GenericCampaignMailable($campaign->template_body, $campaign->subject, $data);
    }
}
```

> Tip: If you expect millions of messages, integrate provider SDKs, not just SMTP queue send.

---

## Tracking (Open/Click)

### Open tracking pixel
- Embed a 1x1 transparent GIF in the email body at send time:

```html
<img src="{{ url('/email/track/open/' . $message->message_uuid) }}" alt="" style="display:none;width:1px;height:1px;" />
```

- `EmailTrackingController::open($uuid)` updates `opened_at` for the `EmailMessage` and returns the GIF content with no caching headers.

### Click tracking
- Replace links in the email template at send time to go through your tracking redirect:

For example, turn `<a href="https://example.com/page">Click</a>` into:
```
<a href="{{ url('/r/' . $message->message_uuid . '?u=' . urlencode($originalUrl)) }}">Click</a>
```

- `EmailTrackingController::click` should verify/validate the redirect URL and prevent open redirects that might be abused.

Security: Always validate redirect domain (optional whitelist) and sanitize query params.

---

## Unsubscribe & Consent

- Each campaign email must include an `unsubscribe` link scoped to the recipient. For example:

```
<a href="{{ url('/unsubscribe/' . $client->id) }}">Unsubscribe</a>
```

- Implement `clients.unsubscribed_at` timestamp, or a `subscriptions` table to manage preferences. Use `unsubscribed_at` filter when building recipient list.

Implemented in the current MVP:
- Added `unsubscribed_at` column to `clients` (via migration `2025_12_08_000003_add_unsubscribed_at_to_clients.php`).
- Unsubscribe route: `GET /unsubscribe/{client}/{uuid}` validates the message UUID before unsubscribing the client.
- Campaign emails include an unsubscribe link using the `client_id` and `message_uuid` (so unsubscription is validated by `message_uuid`).

---

## Provider Webhooks (Advanced)

- Implement `POST /webhooks/mailgun`, `POST /webhooks/sendgrid`, or `POST /webhooks/ses` to receive events like `delivered`, `opened`, `clicked`, `bounce`, and mark `email_messages` rows accordingly using the `provider_id`.
- Verify webhook signature per provider.
- Map `provider_id` to `email_messages.provider_id` for event mapping.

Implemented in the current MVP (scaffold):
- Webhook endpoint: `POST /webhooks/{provider}` logs events and is available to add provider-specific verification and processing.
- Future work: verify signatures per provider and map provider events (`delivered`, `bounced`, `opened`, `clicked`) to `email_messages` via `provider_id`.

---

## Admin UI

- Admin CMS page: `resources/views/admin/email_campaigns/*`
  - `index.blade.php` — show campaigns summary, status, quick metrics
  - `create.blade.php` — compose campaign (name, subject, template body, choosing mailable class, attachments, recipient filter)
  - `edit.blade.php` — update campaign details
  - `show.blade.php` — campaign analytics and recipient query
  - `components` — modal for preview and send scheduling

- UX:
  - Recipient filtered selector (segment by `client` filters: opt-in, last_active, tags)
  - CTA to `Preview` and `Queue`/`Schedule` or immediate `Send`
  - Job progress and status indicators (queued, running, completed)
  - Recipient segmentation control in the campaign form (All vs Subscribed only)
  - Preview button that renders a live HTML preview of the campaign before sending
  - Preview recipients (AJAX) to obtain a count of matching recipients without creating messages
  - Quick Run/Stop actions in the index and show pages

  How to use the admin UI (MVP):
  1. Admin nav: Go to `Admin -> Email Campaigns`.
  2. Create: Click `Create Campaign` — fill `name`, `subject`, and `template body` (HTML allowed) and choose recipients: `All` or `Subscribed`.
    - Click `Preview recipients` to get a count of the recipients for the selected filter before creating.
    - Click `Preview` to render an HTML preview (1x1 pixel and link transforms applied) in a modal.
  3. After creating the campaign you can `View` it to see metrics, recipients and actions.
  4. On the campaign `Show` page, use `Run Campaign` to start the send (or schedule it), `Stop` to cancel queued messages, or `Resend selected` for specific recipients.


---

## Tests

Add `Feature` and `Unit` tests:

- Feature: Admin can create campaign, schedule or run it — confirm queue jobs are dispatched and `email_messages` are created.
- Job tests: `QueueCampaignJob` dispatches `SendCampaignBatchJob` in correct sized batches.
- Send job: `SendCampaignEmailJob` processes a message and updates `status` and `delivered_at`.
- Tracking tests: open/click endpoints update timestamps and return expected content/redirect.
- Webhook tests (if implemented): provider event maps to message rows and sets statuses (bounced, delivered).

Example test command:
```
php artisan test --filter=EmailCampaign
```

---

## Monitoring & Observability

- Keep logs for send attempts with `logger()->info` statements.
- Add an admin widget for campaign health and notable failures.
- Optional: Integrate Horizon for queue insights (if Redis/Horizon used).

---

## Security & Privacy

- Only admins should access or manage campaigns.
- Sanitize any admin-provided HTML templates before saving/using.
- Respect `clients.unsubscribed_at` during selection.
- Add a retention policy for campaign-related personal data.

---

## Implementation Steps & Timeline (MVP)

1. Create migrations & models (EmailCampaign, EmailMessage) — 2–4 hours. 
2. Add basic admin CRUD and `create/run/schedule` endpoints — 1–2 days.
3. Integrate Brevo provider for campaigns (API path):
  - Add `getbrevo/brevo-php` composer dependency.
  - Add `BREVO_API_KEY` and `BREVO_WEBHOOK_SECRET` to `.env`.
  - Update `SendCampaignEmailJob` to use Brevo API when `metadata.provider == 'brevo'` or `CAMPAIGN_MAILER=brevo`.
  - Store `messageId` returned by Brevo in `email_messages.provider_id`.
  - Implement `POST /webhooks/brevo` to verify signature and update `email_messages` statuses from Brevo event payloads.
  - Add admin UI option `Provider: SMTP / Brevo` per campaign and allow quick selection during run.
4. Implement open-tracking & click-tracking routes and update `EmailMessage` rows — 4–8 hours.
5. Add UI preview & scheduling, add tests for controller & job pipelines — 1–2 days.
6. Integrate provider webhooks, unsubscribe, and expanded analytics — 1–3 days.

---

## Notes & Integration Points

- Reuse existing `app/Mail/PlantScanResultWithImageMail.php` where appropriate — either by adding a wrapper campaign that can pass different payloads or new `GenericCampaignMailable` to render `template_body`.
- Use `QUEUE_CONNECTION` and background workers — recommended `database` or `redis` for production scale.
- For large lists, prefer a provider's bulk-sending API or services like Amazon SES with SES bulk send for scalability and cost savings.
- Carefully plan sending rates to stay within provider's sending limits, including provider throttling and retry strategies.

---

## Appendix

- Example Mailable (generic): `app/Mail/GenericCampaignMailable.php` — accepts template and a data array.
- In case of HTML templates, consider using `markdown` templates to keep styles consistent.
- Add migrations to seed a `campaign_types` or `mailable_classes` table if you want a safe list of allowed mailable classes in the admin UI.


---

If you want, I can implement the MVP now (migrations, models, controllers, routes, jobs, tracking endpoints, and basic Blade view stubs).

Note: There is a helper script at `scripts/create_admin.php` which can be run to create the admin user `admin@example.com` (password: `password`). This is for local/testing convenience only — change the password immediately in production or secure with environment variables.

