<?php

namespace App\Http\Resources;


use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'id'                   => $this->id,
            'order_serial_no'      => $this->order_serial_no,
            'user_id'              => $this->user_id,
            "total_amount_price"   => AppLibrary::flatAmountFormat($this->total),
            "total_currency_price" => AppLibrary::currencyAmountFormat($this->total),
            'payment_status'       => $this->payment_status,
            'payment_status_name'  => $this->payment_status == \App\Enums\PaymentStatus::PAID ? 'Paid' : 'Unpaid',
            'status'               => $this->status,
            'status_name'          => trans('orderStatus.' . $this->status),
            'reason'               => $this->customerNote(),
            'status_reason'        => $this->adminStatusReason(),
            'order_items'          => optional($this->orderProducts)->count(),
            'order_datetime'       => AppLibrary::datetime($this->order_datetime),
            'user'                 => new UserResource($this->user),
        ];
    }
}
