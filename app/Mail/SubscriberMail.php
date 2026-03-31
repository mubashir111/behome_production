<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriberMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $timeout = 60;

    public string $title;
    public mixed  $message;

    public function __construct($title, $message)
    {
        $this->title   = $title;
        $this->message = $message;
        $this->onQueue('emails');
    }

    public function build()
    {
        return $this->subject($this->title)->markdown('emails.subscriber');
    }
}
