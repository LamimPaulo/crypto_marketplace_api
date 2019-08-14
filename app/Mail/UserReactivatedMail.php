<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserReactivatedMail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $main_message;
    public $message;
    public $title;

    /**
     * Create a new message instance.
     *
     * @param $info
     */
    public function __construct($user)
    {
        $this->subject = trans('mail.reactivate_account.subject');
        $this->title = $this->subject;
        $this->user = $user;
        $this->main_message = trans('mail.reactivate_account.main_message');
        $this->message = trans('mail.reactivate_account.message');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.user-reactivate-account');
    }
}
