<?php

namespace App\Mail;

use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class GenericCampaignMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $campaign;
    public $message;
    public function __construct(EmailCampaign $campaign, EmailMessage $message)
    {
        $this->campaign = $campaign;
        $this->message = $message;
    }

    public function build()
    {
        // Use the generic renderBody method to build HTML
        $subject = $this->campaign->subject ?: 'Message from Aurora';
        $body = $this->renderBody();

        return $this->subject($subject)
            ->html($body);
    }


    /**
     * Return the rendered HTML body without invoking the Mailer (safe for API sends)
     */
    public function renderBody(): string
    {
        $body = $this->campaign->template_body ?? '';

        $tokens = array_merge($this->campaign->metadata ?? [], $this->message->metadata ?? []);
        $tokens['name'] = $this->message->metadata['client_name'] ?? $this->message->client->client ?? '';

        foreach ($tokens as $key => $val) {
            if (is_array($val) || is_object($val)) {
                $replacement = json_encode($val);
            } elseif ($val === null) {
                $replacement = '';
            } else {
                $replacement = (string) $val;
            }

            $body = str_replace("{{{$key}}}", $replacement, $body);
            // Also replace neutral placeholder format [[key]] used by campaign templates
            $body = str_replace('[[' . $key . ']]', $replacement, $body);
        }

        // Convert relative image src (src="/path") to absolute URLs so external providers can fetch them
        $body = preg_replace_callback('/src="(\/[^\"]+)"/i', function ($m) {
            return 'src="' . url($m[1]) . '"';
        }, $body);

        // Convert protocol-relative (//domain/path) to absolute with current scheme
        $body = preg_replace_callback('/src="\/\/([^\"]+)"/i', function ($m) {
            return 'src="' . request()->getScheme() . '://' . $m[1] . '"';
        }, $body);

        // Convert relative hrefs (href="/path") to absolute URLs so click-tracking wrapper will work
        $body = preg_replace_callback('/href="(\/[^\"]+)"/i', function ($m) {
            return 'href="' . url($m[1]) . '"';
        }, $body);

        // Defensive: remove unrendered Blade/PHP tokens that could break external providers (e.g., {{ $user->name }})
        if (preg_match('/\{\{\s*\$|<\?php|@php/', $body)) {
            \Log::warning('Campaign body contains unrendered Blade/PHP tokens; removing them', ['campaign_id' => $this->campaign->id ?? null]);
            // remove Blade-style PHP variable tokens like {{ $user->name }}
            $body = preg_replace('/\{\{\s*\$[^}]+\}\}/', '', $body);
            // remove @php ... @endphp blocks
            $body = preg_replace('/@php\b[\s\S]*?@endphp\b/', '', $body);
            // remove any inline PHP tags
            $body = preg_replace('/<\?[\s\S]*?\?>/', '', $body);
        }

        $pixelUrl = url('/email/track/open/' . $this->message->message_uuid);
        $body .= "\n<img src=\"{$pixelUrl}\" alt=\"\" style=\"display:none;width:1px;height:1px;\"/>";

        $body = preg_replace_callback('/href="(https?:\/\\/[^\"]+)"/i', function ($matches) {
            $u = $matches[1];
            $clickUrl = url('/r/' . $this->message->message_uuid . '?u=' . urlencode($u));
            return 'href="' . $clickUrl . '"';
        }, $body);

        if ($this->message->client) {
            $unsubscribe = url('/unsubscribe/' . $this->message->client->id . '/' . $this->message->message_uuid);
            $body .= "\n<p><small><a href=\"{$unsubscribe}\">Unsubscribe</a></small></p>";
        }

        return $body;
    }
}

