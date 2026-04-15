@component('mail::message')
# Good news — {{ $productName }} is back in stock!

@if($customMessage)
{{ $customMessage }}
@else
The item you were waiting for is now available. Grab it before it sells out again!
@endif

@component('mail::button', ['url' => $productUrl])
Shop Now
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
