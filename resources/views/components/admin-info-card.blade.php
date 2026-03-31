@props([
    'label' => '',
    'icon' => null,
    'variant' => 'default', // default, highlighted, neutral
])

@php
$variantClasses = [
    'default' => 'bg-white border border-slate-200 rounded-xl shadow-sm',
    'highlighted' => 'glass rounded-2xl',
    'neutral' => 'bg-slate-50 border border-slate-200 rounded-xl',
];
@endphp

<div class="{{ $variantClasses[$variant] ?? $variantClasses['default'] }} p-4 md:p-6">
    @if($label)
        <div class="flex items-center gap-2 mb-3">
            @if($icon)
                <div class="text-indigo-600">
                    {!! $icon !!}
                </div>
            @endif
            <label class="text-sm font-semibold text-slate-700">{{ $label }}</label>
        </div>
    @endif
    <div class="text-slate-900">
        {{ $slot }}
    </div>
</div>
