<?php

namespace App\Jobs;

use App\Models\EmailMessage;
use App\Mail\GenericCampaignMailable;
use Brevo\Client\Configuration as BrevoConfiguration;
use Brevo\Client\Api\TransactionalEmailsApi as BrevoTransactionalEmailsApi;
use Brevo\Client\Model\SendSmtpEmail as BrevoSendSmtpEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendCampaignEmailJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected EmailMessage $emailMessage;

    public function __construct(EmailMessage $emailMessage)
    {
        $this->emailMessage = $emailMessage->fresh();
    }

    public function handle(): void
    {
        $campaign = $this->emailMessage->campaign;

        $mailable = new GenericCampaignMailable($campaign, $this->emailMessage);

        $provider = $this->emailMessage->metadata['provider'] ?? $campaign->metadata['provider'] ?? env('CAMPAIGN_MAILER', 'smtp');

        if ($provider === 'brevo' && class_exists(BrevoTransactionalEmailsApi::class)) {
            // Use Brevo API to send
            try {
                $apiKey = config('services.brevo.api_key') ?? env('BREVO_API_KEY');

                // Guard: fail fast with a clear error if Brevo API key is missing
                if (empty($apiKey)) {
                    $err = 'Brevo API key missing (set BREVO_API_KEY in env or services.brevo.api_key)';
                    $this->emailMessage->increment('attempts');
                    $this->emailMessage->update(['status' => 'failed', 'error' => $err]);
                    Log::error('Brevo send aborted for message id ' . $this->emailMessage->id . ': ' . $err);
                    return; // don't throw — prevents futile retries until configuration fixed
                }

                // Helpful guard: detect when an SMTP key is used instead of a transactional API key
                // SMTP keys typically start with 'xsmtpsib-' and will be rejected by the transactional endpoint.
                if (str_starts_with($apiKey, 'xsmtpsib-')) {
                    $err = 'BREVO_API_KEY appears to be an SMTP key (xsmtpsib-...). Transactional API requires a v3 API key (xkeysib-...). Please create a transactional API key in Brevo and set BREVO_API_KEY.';
                    $this->emailMessage->increment('attempts');
                    $this->emailMessage->update(['status' => 'failed', 'error' => $err]);
                    Log::error('Brevo send aborted for message id ' . $this->emailMessage->id . ': ' . $err);
                    return;
                }

                // Log API key prefix (not the full key) to help diagnose env mismatch without exposing secret
                try {
                    Log::info('Brevo API key prefix for message ' . $this->emailMessage->id . ': ' . substr($apiKey, 0, 8) . '...');
                } catch (\Exception $ex) {
                    // ignore logging errors
                }

                $config = BrevoConfiguration::getDefaultConfiguration()->setApiKey('api-key', $apiKey);
                // TransactionalEmailsApi constructor signature: __construct(ClientInterface $client = null, Configuration $config = null, HeaderSelector $selector = null)
                $apiInstance = new BrevoTransactionalEmailsApi(new \GuzzleHttp\Client(), $config);

                $recipientName = $this->emailMessage->client ? $this->emailMessage->client->client : ($this->emailMessage->metadata['client_name'] ?? 'Recipient');
                // Ensure name is never empty/null (Brevo requires it)
                if (empty($recipientName)) {
                    $recipientName = 'Recipient';
                }
                $sendObj = new BrevoSendSmtpEmail([
                    'subject' => $campaign->subject ?: $this->emailMessage->metadata['subject'] ?? 'Message from Aurora',
                    // Use renderBody() to generate HTML without invoking Laravel's Mailer (which may require a mailer named in MAIL_MAILER)
                    'htmlContent' => $mailable->renderBody(),
                    'sender' => ['name' => config('mail.from.name'), 'email' => config('mail.from.address')],
                    'to' => [['email' => $this->emailMessage->email, 'name' => $recipientName]],
                ]);

                $result = $apiInstance->sendTransacEmail($sendObj);
                // result may be a model or array / object; normalize
                $messageId = null;
                if (is_array($result)) {
                    $messageId = $result['messageId'] ?? $result['messageid'] ?? null;
                } elseif (is_object($result)) {
                    if (method_exists($result, 'getMessageId')) {
                        $messageId = $result->getMessageId();
                    } elseif (isset($result->messageId)) {
                        $messageId = $result->messageId;
                    } elseif (isset($result->messageid)) {
                        $messageId = $result->messageid;
                    }
                }
                if ($messageId) {
                    $this->emailMessage->update(['provider_id' => $messageId, 'status' => 'delivered', 'delivered_at' => now()]);
                } else {
                    $this->emailMessage->update(['status' => 'delivered', 'delivered_at' => now()]);
                }
                } catch (\Brevo\Client\ApiException $e) {
                        // Log full API response body for diagnostics (may contain useful error info)
                        $body = null;
                        try { $body = $e->getResponseBody(); } catch (\Exception $_) { $body = null; }
                        $msg = $e->getMessage() . ' ' . json_encode($body);
                        $this->emailMessage->increment('attempts');
                        $this->emailMessage->update(['status' => 'failed', 'error' => $msg]);
                        Log::error('Brevo API error for message id ' . $this->emailMessage->id . ': ' . $msg . "\n" . $e->getTraceAsString());
                        // Don't throw - allow campaign to continue with other recipients
                    } catch (\Exception $e) {
                        $this->emailMessage->increment('attempts');
                        $this->emailMessage->update(['status' => 'failed', 'error' => $e->getMessage()]);
                        Log::error('Brevo send failed for message id ' . $this->emailMessage->id . ' ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                        // Don't throw - allow campaign to continue with other recipients
                    }
        } else {
            // Fallback to Laravel mailer (SMTP / provider configured in mail.php)
            try {
                // Ensure we use a valid mailer: if the configured default mailer isn't defined in config, fall back to smtp
                $defaultMailer = config('mail.default');
                $available = config('mail.mailers', []);
                $useMailer = array_key_exists($defaultMailer, $available) ? $defaultMailer : (array_key_exists('smtp', $available) ? 'smtp' : $defaultMailer);

                Mail::mailer($useMailer)->to($this->emailMessage->email)->send($mailable);
                $this->emailMessage->update(['status' => 'delivered', 'delivered_at' => now()]);
            } catch (\Exception $e) {
                $this->emailMessage->increment('attempts');
                $this->emailMessage->update(['status' => 'failed', 'error' => $e->getMessage()]);
                Log::error('SendCampaignEmailJob failed for message id ' . $this->emailMessage->id . ' ' . $e->getMessage() . "\n" . $e->getTraceAsString());
                // Don't throw - allow campaign to continue with other recipients
            }
        }
    }
}
