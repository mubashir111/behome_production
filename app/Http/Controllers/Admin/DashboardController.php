<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use App\Models\ThemeSetting;
use App\Models\Analytic;

final class DashboardController extends Controller
{
    public function index(): View
    {
        $total_revenue   = \App\Models\Order::where('status', \App\Enums\OrderStatus::DELIVERED)->sum('total');
        $active_orders   = \App\Models\Order::whereIn('status', [
            \App\Enums\OrderStatus::PENDING,
            \App\Enums\OrderStatus::CONFIRMED,
            \App\Enums\OrderStatus::ON_THE_WAY,
        ])->count();
        $total_orders     = \App\Models\Order::count();
        $total_customers  = \App\Models\User::count();
        $total_products   = \App\Models\Product::count();
        $total_reviews    = \App\Models\ProductReview::count();
        $pending_returns  = \App\Models\ReturnAndRefund::where('status', \App\Enums\ReturnOrderStatus::PENDING)->count();
        $recent_orders    = \App\Models\Order::with('user')->latest()->take(5)->get();
        $recent_reviews   = \App\Models\ProductReview::with(['user', 'product'])->latest()->take(5)->get();
        $pending_return_list = \App\Models\ReturnAndRefund::with(['user', 'returnReason'])
            ->where('status', \App\Enums\ReturnOrderStatus::PENDING)
            ->latest()->take(5)->get();

        $unread_messages      = \App\Models\OrderMessage::where('sender_type', 'customer')->where('is_read', false)->count();
        $cancellation_requests = \App\Models\OrderMessage::where('message', 'like', '[CANCELLATION REQUEST]%')->count();
        $recent_messages = \App\Models\Order::with(['user', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->whereHas('messages', fn($q) => $q->where('sender_type', 'customer')->where('is_read', false))
            ->latest()->take(5)->get();

        $currencySymbol = config('app.currency_symbol');

        return view('admin.dashboard', [
            'favicon'               => ThemeSetting::where(['key' => 'theme_favicon_logo'])->first()?->faviconLogo,
            'analytics'             => Analytic::with('analyticSections')->get(),
            'currencySymbol'        => $currencySymbol,
            'stats' => [
                'total_revenue'   => $total_revenue,
                'active_orders'   => $active_orders,
                'total_orders'    => $total_orders,
                'total_customers' => $total_customers,
                'total_products'  => $total_products,
                'total_reviews'   => $total_reviews,
                'pending_returns' => $pending_returns,
            ],
            'recent_orders'         => $recent_orders,
            'recent_reviews'        => $recent_reviews,
            'pending_return_list'   => $pending_return_list,
            'unread_messages'       => $unread_messages,
            'cancellation_requests' => $cancellation_requests,
            'recent_messages'       => $recent_messages,
        ]);
    }
}