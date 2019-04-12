<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyLoginMail extends Mailable
{
    use Queueable, SerializesModels;
    public $info;

    /**
     * Create a new message instance.
     *
     * @param $info
     */
    public function __construct($info)
    {
        $this->subject = trans('mail.notify_login.subject');
        $this->info = $info;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.notifyLogin');
    }
}
