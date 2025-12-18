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

