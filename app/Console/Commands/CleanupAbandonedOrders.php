<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\StockTax;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanupAbandonedOrders extends Command
{
    protected $signature   = 'orders:cleanup-abandoned {--minutes=30 : Minutes after which an unpaid order is considered abandoned}';
    protected $description = 'Soft-delete unpaid orders abandoned for longer than the given time window';

    public function handle(): void
    {
        $minutes = (int) $this->option('minutes');

        // Only target orders that are:
        //   - still Pending (not confirmed/shipped/delivered)
        //   - still Unpaid
        //   - created more than $minutes ago
        //   - online payment orders (source = 5 WEB or 10 APP — not POS=15)
        // Exclude COD and Credit gateways — they are always unpaid at creation
        $offlineGatewayIds = \App\Models\PaymentGateway::whereIn('slug', ['cashondelivery', 'credit'])->pluck('id');

        $abandoned = Order::where('payment_status', PaymentStatus::UNPAID)
            ->where('status', OrderStatus::PENDING)
            ->where('source', '!=', 15)                          // exclude POS orders
            ->whereNotIn('payment_method', $offlineGatewayIds)   // exclude COD / Credit
            ->where('created_at', '<', now()->subMinutes($minutes))
            ->get();

        if ($abandoned->isEmpty()) {
            $this->info('No abandoned orders found.');
            return;
        }

        $count = 0;
        foreach ($abandoned as $order) {
            try {
                DB::transaction(function () use ($order) {
                    // Restore cart items so the customer doesn't lose their cart
                    $order->load('orderProducts');
                    foreach ($order->orderProducts as $item) {
                        \App\Models\Cart::firstOrCreate([
                            'user_id'      => $order->user_id,
                            'product_id'   => $item->product_id,
                            'variation_id' => $item->variation_id > 0 ? $item->variation_id : 0,
                        ], [
                            'quantity' => abs($item->quantity),
                            'price'    => $item->price,
                        ]);
                    }

                    // Soft-delete — all child records preserved for reference
                    $order->delete();
                });
                $count++;
                Log::info("CleanupAbandonedOrders: soft-deleted order #{$order->id} (created {$order->created_at})");
            } catch (\Exception $e) {
                Log::error("CleanupAbandonedOrders: failed on order #{$order->id} — " . $e->getMessage());
            }
        }

        $this->info("Archived {$count} abandoned order(s).");
    }
}
