<?php

namespace App\Services;


use App\Enums\OrderStatus;
use App\Enums\SwitchBox;
use App\Mail\OrderMail;
use App\Models\NotificationAlert;
use App\Models\Order;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Smartisan\Settings\Facades\Settings;

class OrderMailNotificationBuilder
{
    public ?int $orderId;
    public mixed $status;
    public ?object $order;
    public bool $force;

    public function __construct($orderId = null, $status = null, $force = false)
    {
        $this->orderId = $orderId;
        $this->status  = $status;
        $this->order   = $orderId ? Order::find($orderId) : null;
        $this->force   = $force;
    }

    public function send(): void
    {
        if (!blank($this->order)) {
            $user = User::find($this->order->user_id);
            if (!blank($user)) {
                if ($user->email) {
                    $this->message($user->name, $user->email, $this->status, $this->order->order_serial_no);
                }
            }
        }
    }

    public function adminOrderNotification(): void
    {
        if (!blank($this->order)) {
            $adminEmail = Settings::group('company')->get('company_email');
            if ($adminEmail) {
                $notificationAlert = NotificationAlert::where(['language' => 'admin_and_manager_new_order_message'])->first();
                if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
                    $this->mail('Admin', $adminEmail, $this->order->order_serial_no, $notificationAlert->mail_message ?? '', $this->order);
                }
            }
        }
    }

    public function adminOrderCancellationNotification(): void
    {
        if (!blank($this->order)) {
            $adminEmail = Settings::group('company')->get('company_email');
            if ($adminEmail) {
                $notificationAlert = NotificationAlert::where(['language' => 'admin_order_cancellation_message'])->first();
                if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
                    $this->mail('Admin', $adminEmail, $this->order->order_serial_no, $notificationAlert->mail_message ?? '', $this->order);
                }
            }
        }
    }

    public function adminContactMessageNotification($contactMessage): void
    {
        $adminEmail = Settings::group('company')->get('company_email');
        if ($adminEmail) {
            $notificationAlert = NotificationAlert::where(['language' => 'admin_new_contact_message'])->first();
            if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
                $messageBody = $notificationAlert->mail_message ?? '';
                $messageBody .= "\n\n--- Message Details ---\n";
                $messageBody .= "Name: " . $contactMessage->name . "\n";
                $messageBody .= "Email: " . $contactMessage->email . "\n";
                $messageBody .= "Message: " . $contactMessage->message;

                try {
                    Mail::to($adminEmail)->send(new OrderMail('Admin', 'Contact Form', $messageBody));
                } catch (Exception $e) {
                    Log::info($e->getMessage());
                }
            }
        }
    }

    private function message($name, $email, $status, $orderId): void
    {
        if ($status == OrderStatus::PENDING) {
            $this->pending($name, $email, $orderId);
        } elseif ($status == OrderStatus::CONFIRMED) {
            $this->confirmed($name, $email, $orderId);
        } elseif ($status == OrderStatus::ON_THE_WAY) {
            $this->onTheWay($name, $email, $orderId);
        } elseif ($status == OrderStatus::DELIVERED) {
            $this->delivered($name, $email, $orderId);
        } elseif ($status == OrderStatus::CANCELED) {
            $this->customerCanceled($name, $email, $orderId);
        } elseif ($status == OrderStatus::REJECTED) {
            $this->rejected($name, $email, $orderId);
        }
    }

    private function mail($name, $email, $orderId, $message, $order = null): void
    {
        try {
            Mail::to($email)->send(new OrderMail($name, $orderId, $message, $order));
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }

    private function pending($name, $email, $orderId): void
    {
        $notificationAlert = NotificationAlert::where(['language' => 'order_pending_message'])->first();
        if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
            $this->mail($name, $email, $orderId, $notificationAlert->mail_message ?? '', $this->order);
        }
    }

    private function confirmed($name, $email, $orderId): void
    {
        $notificationAlert = NotificationAlert::where(['language' => 'order_confirmation_message'])->first();
        if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
            $this->mail($name, $email, $orderId, $notificationAlert->mail_message ?? '', $this->order);
        }
    }

    private function onTheWay($name, $email, $orderId): void
    {
        $notificationAlert = NotificationAlert::where(['language' => 'order_on_the_way_message'])->first();
        if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
            $this->mail($name, $email, $orderId, $notificationAlert->mail_message ?? '', $this->order);
        }
    }

    private function delivered($name, $email, $orderId): void
    {
        $notificationAlert = NotificationAlert::where(['language' => 'order_delivered_message'])->first();
        if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
            $this->mail($name, $email, $orderId, $notificationAlert->mail_message ?? '', $this->order);
        }
    }

    private function customerCanceled($name, $email, $orderId): void
    {
        $notificationAlert = NotificationAlert::where(['language' => 'order_canceled_message'])->first();
        if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
            $this->mail($name, $email, $orderId, $notificationAlert->mail_message ?? '', $this->order);
        }
    }

    private function rejected($name, $email, $orderId): void
    {
        $notificationAlert = NotificationAlert::where(['language' => 'order_rejected_message'])->first();
        if ($this->force || ($notificationAlert && $notificationAlert->mail == SwitchBox::ON)) {
            $this->mail($name, $email, $orderId, $notificationAlert->mail_message ?? '', $this->order);
        }
    }
}