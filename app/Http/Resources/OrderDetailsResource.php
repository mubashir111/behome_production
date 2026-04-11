<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'                             => $this->id,
            'order_serial_no'                => $this->order_serial_no,
            'user_id'                        => $this->user_id,
            "subtotal_currency_price"        => AppLibrary::currencyAmountFormat($this->subtotal),
            "tax_currency_price"             => AppLibrary::currencyAmountFormat($this->tax),
            "discount"                       => (float) $this->discount,
            "discount_currency_price"        => AppLibrary::currencyAmountFormat($this->discount),
            "total_currency_price"           => AppLibrary::currencyAmountFormat($this->total),
            "total_amount_price"             => AppLibrary::flatAmountFormat($this->total),
            "shipping_charge_currency_price" => AppLibrary::currencyAmountFormat($this->shipping_charge),
            'order_type'                     => $this->order_type,
            'order_date'                     => AppLibrary::date($this->order_datetime),
            'order_time'                     => AppLibrary::time($this->order_datetime),
            'order_datetime'                 => AppLibrary::datetime($this->order_datetime),
            'payment_method'                 => $this->payment_method,
            'payment_method_name'            => $this->paymentMethod?->name,
            'payment_status'                 => $this->payment_status,
            'payment_status_name'            => $this->payment_status == \App\Enums\PaymentStatus::PAID ? 'Paid' : 'Unpaid',
            'status'                         => $this->status,
            'status_name'                    => trans('orderStatus.' . $this->status),
            'reason'                         => $this->customerNote(),
            'status_reason'                  => $this->adminStatusReason(),
            'cancellation_requested'         => $this->reasonPayload()['cancellation_requested'] ?? false,
            'source'                         => $this->source,
            'active'                         => (int) $this->active,
            'refund_transaction'             => ($refund = \App\Models\Transaction::where('order_id', $this->id)->where('type', 'cash_back')->first())
                ? [
                    'amount'           => AppLibrary::currencyAmountFormat($refund->amount),
                    'transaction_no'   => $refund->transaction_no,
                    'payment_method'   => $refund->payment_method,
                    'created_at'       => $refund->created_at?->format('M d, Y'),
                ]
                : null,
            'return_and_refund'              => $this->returnAndRefund ? [
                'id'                 => $this->returnAndRefund->id,
                'status'             => $this->returnAndRefund->status,
                'refund_status'      => $this->returnAndRefund->refund_status,
                'note'               => $this->returnAndRefund->note,
                'reject_reason'      => $this->returnAndRefund->reject_reason,
                'return_reason'      => $this->returnAndRefund->returnReason ? ['title' => $this->returnAndRefund->returnReason->title] : null,
                'total_return_price' => $this->returnAndRefund->returnProducts->sum('return_price'),
                'refund_issued_at'   => $this->returnAndRefund->refund_issued_at?->format('M d, Y'),
                'created_at'         => $this->returnAndRefund->created_at?->format('M d, Y'),
            ] : null,
            'user'                           => new UserResource($this->user),
            'order_address'                  => AddressResource::collection($this->address),
            'outlet_address'                 => new OutletResource($this?->outletAddress),
            'order_products'                 => OrderProductResource::collection($this->orderProducts),
        ];
    }
}
