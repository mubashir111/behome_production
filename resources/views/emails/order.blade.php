@component('mail::message')
# Order Update — #{{ $orderId }}

Hello **{{ $name }}**,

{{ $message }}

---

@if($order && $order->orderProducts && $order->orderProducts->count())
## Order Items

@component('mail::table')
| Product | Qty | Price |
|:--------|:---:|------:|
@foreach($order->orderProducts as $item)
| {{ $item->product?->name ?? 'Product' }}{{ $item->variation_names ? ' ('.$item->variation_names.')' : '' }} | {{ abs($item->quantity) }} | {{ \App\Libraries\AppLibrary::currencyAmountFormat($item->total) }} |
@endforeach
@endcomponent

@php
    $rows = [];
    if ($order->subtotal)       $rows['Subtotal']  = \App\Libraries\AppLibrary::currencyAmountFormat($order->subtotal);
    if ($order->tax > 0)        $rows['Tax']       = \App\Libraries\AppLibrary::currencyAmountFormat($order->tax);
    if ($order->shipping_charge > 0) $rows['Shipping'] = \App\Libraries\AppLibrary::currencyAmountFormat($order->shipping_charge);
    if ($order->discount > 0)   $rows['Discount']  = '−' . \App\Libraries\AppLibrary::currencyAmountFormat($order->discount);
@endphp

@if(count($rows))
@component('mail::table')
| | |
|:---|---:|
@foreach($rows as $label => $value)
| {{ $label }} | {{ $value }} |
@endforeach
| **Total** | **{{ \App\Libraries\AppLibrary::currencyAmountFormat($order->total) }}** |
@endcomponent
@endif

@endif

@component('mail::button', ['url' => (config('app.frontend_url') ?: config('app.url')) . '/account/order/' . $orderId, 'color' => 'primary'])
View Your Order
@endcomponent

If you have any questions, please don't hesitate to [contact us]({{ (config('app.frontend_url') ?: config('app.url')) . '/contact' }}).

Thanks for shopping with us,
**{{ config('app.name') }}**

---
<small>This email was sent regarding order #{{ $orderId }}. If you did not place this order, please contact our support team immediately.</small>
@endcomponent
