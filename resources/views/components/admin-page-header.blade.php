@props([
    'title',
    'subtitle' => '',
    'action_label' => '',
    'action_url' => '',
    'action_variant' => 'primary', // primary, secondary
])

<div class="mb-8 flex items-center justify-between gap-4 flex-wrap">
    <div>
        <h2 class="text-3xl font-bold text-slate-900">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-slate-500 mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if($action_url)
        <a href="{{ $action_url }}" @class([
            'px-6 py-3 rounded-2xl font-bold transition-all flex items-center shadow-xl' => true,
            'bg-gradient-to-r from-indigo-600 to-violet-600 text-white hover:from-indigo-500 hover:to-violet-500 shadow-indigo-200/50' => $action_variant === 'primary',
            'bg-slate-100 text-slate-700 hover:bg-slate-200' => $action_variant === 'secondary',
        ])>
            {{ $slot ?? $action_label }}
        </a>
    @elseif($slot)
        {{ $slot }}
    @endif
</div>
