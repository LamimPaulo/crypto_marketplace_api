<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DocumentReject extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $main_message;
    public $message;
    public $reason;
    public $title;

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $reason
     */
    public function __construct($user, $reason)
    {
        $this->subject = trans('mail.documents.reject.subject');
        $this->title = $this->subject;
        $this->user = $user;
        $this->reason = $reason;
        $this->main_message = trans('mail.documents.reject.main_message');
        $this->message = trans('mail.documents.reject.message');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.actions-rejected');
    }
}
