@component('mail::message')
# New Order Received — #{{ $orderId }}

{{ $message }}

@component('mail::button', ['url' => config('app.url') . '/admin/orders', 'color' => 'primary'])
View in Admin Panel
@endcomponent

Thanks,
**{{ config('app.name') }} Admin**
@endcomponent
