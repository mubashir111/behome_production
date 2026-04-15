<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StockNotificationMail extends Mailable
{
    use SerializesModels;

    public string $productName;
    public string $productUrl;
    public string $customMessage;

    public function __construct(string $productName, string $productUrl, string $customMessage = '')
    {
        $this->productName   = $productName;
        $this->productUrl    = $productUrl;
        $this->customMessage = $customMessage;
    }

    public function build(): static
    {
        return $this->subject("Back in Stock: {$this->productName}")
                    ->markdown('emails.stock_notification');
    }
}
