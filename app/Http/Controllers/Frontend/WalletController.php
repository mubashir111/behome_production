<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Libraries\AppLibrary;
use App\Models\Transaction;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function transactions(Request $request)
    {
        $userId = auth()->id();

        $transactions = Transaction::whereHas('order', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->with('order:id,order_serial_no')
            ->latest()
            ->paginate(20);

        $data = $transactions->map(function ($tx) {
            return [
                'id'             => $tx->id,
                'type'           => $tx->type,
                'sign'           => $tx->sign,
                'amount'         => AppLibrary::flatAmountFormat($tx->amount),
                'currency_amount'=> AppLibrary::currencyAmountFormat($tx->amount),
                'payment_method' => $tx->payment_method,
                'transaction_no' => $tx->transaction_no,
                'order_serial_no'=> $tx->order?->order_serial_no,
                'order_id'       => $tx->order_id,
                'created_at'     => $tx->created_at?->format('d M Y, H:i'),
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $data,
            'meta'   => [
                'total'        => $transactions->total(),
                'current_page' => $transactions->currentPage(),
                'last_page'    => $transactions->lastPage(),
            ],
        ]);
    }
}
