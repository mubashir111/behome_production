<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderGotMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int   $tries   = 3;
    public int   $timeout = 30;

    public int   $orderId;
    public mixed $message;

    public function __construct($orderId, $message)
    {
        $this->orderId = $orderId;
        $this->message = $message;
        $this->onQueue('emails');
    }

    public function build()
    {
        return $this->subject("New Order Received — #{$this->orderId}")->markdown('emails.orderGot');
    }
}
