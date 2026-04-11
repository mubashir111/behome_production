<?php

namespace App\Http\Controllers\Admin\Web;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentWebController extends Controller
{
    public function index(Request $request)
    {
        // Show all transaction types (payment + refund) unless filtered.
        $query = Transaction::with(['order.user'])->latest();

        if ($request->filled('gateway')) {
            $query->where('payment_method', $request->gateway);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('payment_status', $request->status);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_no', 'like', "%{$search}%")
                  ->orWhereHas('order', function ($q2) use ($search) {
                      $q2->where('order_serial_no', 'like', "%{$search}%");
                  })
                  ->orWhereHas('order.user', function ($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->paginate(20)->withQueryString();

        $currencySymbol = config('app.currency_symbol', '$');

        $gateways = Transaction::distinct()
            ->pluck('payment_method')
            ->filter()
            ->sort()
            ->values();

        $totalPaid   = Transaction::where('type', 'payment')->where('sign', '+')->sum('amount');
        $totalRefund = Transaction::where('type', 'cash_back')->where('sign', '-')->sum('amount');

        return view('admin.payments.index', compact(
            'payments', 'currencySymbol', 'gateways', 'totalPaid', 'totalRefund'
        ));
    }
}
