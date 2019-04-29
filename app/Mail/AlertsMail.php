<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AlertsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $alert;

    /**
     * Create a new message instance.
     *
     * @param $alert
     */
    public function __construct($alert)
    {
        $this->subject = 'Alerta';
        $this->alert = $alert;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.alert');
    }
}
