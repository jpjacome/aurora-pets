<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlantScanResultWithImageMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array */
    public $payload;

    /**
     * Create a new message instance.
     *
     * @param array $payload
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $petName = $this->payload['pet_name'] ?? 'tu mascota';
        $plantName = $this->payload['plant_name'] ?? 'tu planta';
        $subject = "¡La planta perfecta para {$petName}!";

        // Note: Image embedding is handled in the Blade template using $message->embedData()
        // The $message variable is automatically available in all email templates
        
        return $this->subject($subject)
                    ->markdown('emails.plantscan_result')
                    ->with(['payload' => $this->payload]);
    }
}
