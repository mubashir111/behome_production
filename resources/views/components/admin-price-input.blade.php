@props([
    'name' => '',
    'id' => '',
    'value' => '',
    'required' => false,
    'placeholder' => ' ',
    'step' => '0.01',
    'class' => '',
    'icon' => null
])

<div class="price-input-wrapper">
    <input type="number" 
           name="{{ $name }}" 
           id="{{ $id ?: $name }}" 
           value="{{ $value }}" 
           step="{{ $step }}"
           @if($required) required @endif
           placeholder="{{ $placeholder }}"
           {{ $attributes->merge(['class' => 'price-input ' . $class]) }}>
    <span class="price-input-icon">
        {{ $icon ?: config('app.currency_symbol') }}
    </span>
</div>
