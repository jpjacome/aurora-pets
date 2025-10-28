<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PlantScanResultMail extends Mailable
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
        $pet = $this->payload['pet_name'] ?? ($this->payload['petName'] ?? 'tu mascota');
        $subject = "La planta de {$pet} - Resultado PlantScan";

        return $this->subject($subject)
                    ->markdown('emails.plantscan_result')
                    ->with(['payload' => $this->payload]);
    }
}
