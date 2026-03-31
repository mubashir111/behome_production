<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $timeout = 30;

    public string $name;
    public int    $orderId;
    public mixed  $message;
    public ?Order $order;

    public function __construct($name, $orderId, $message, ?Order $order = null)
    {
        $this->name    = $name;
        $this->orderId = $orderId;
        $this->message = $message;
        $this->order   = $order;
        $this->onQueue('emails');
    }

    public function build()
    {
        return $this->subject("Order Update — #{$this->orderId}")->markdown('emails.order');
    }
}
