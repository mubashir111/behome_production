<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Order;

class OrderReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $replyMessage;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order, $replyMessage)
    {
        $this->order = $order;
        $this->replyMessage = $replyMessage;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Message Regarding Order #' . $this->order->order_serial_no)
                    ->view('emails.order_reply');
    }
}
