<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserCancelAccount extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $main_message;
    public $message;
    public $title;

    /**
     * Create a new message instance.
     *
     * @param $user
     */
    public function __construct($user)
    {
        $this->subject = trans('mail.cancel_account.subject');
        $this->title = $this->subject;
        $this->user = $user;
        $this->main_message = trans('mail.cancel_account.main_message');
        $this->message = trans('mail.cancel_account.message');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-cancel-account');
    }
}
