@props([
    'type' => 'info', // info, success, warning, error
    'title' => '',
    'dismissible' => true,
])

@php
$colorMap = [
    'success' => [
        'bg' => 'glass border-l-4 border-emerald-500',
        'icon' => 'text-emerald-500',
        'text' => 'text-emerald-800',
        'icon_svg' => 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z',
    ],
    'error' => [
        'bg' => 'glass border-l-4 border-rose-500',
        'icon' => 'text-rose-500',
        'text' => 'text-rose-800',
        'icon_svg' => 'M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z',
    ],
    'warning' => [
        'bg' => 'glass border-l-4 border-amber-500',
        'icon' => 'text-amber-500',
        'text' => 'text-amber-800',
        'icon_svg' => 'M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z',
    ],
    'info' => [
        'bg' => 'glass border-l-4 border-indigo-500',
        'icon' => 'text-indigo-500',
        'text' => 'text-indigo-800',
        'icon_svg' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM9 9H5a4 4 0 00-4 4v2a4 4 0 004 4h4m0-14a4 4 0 110 8 4 4 0 010-8zm0 0H9m11-4a4 4 0 11-8 0 4 4 0 018 0zM15 9h-4',
    ],
];

$config = $colorMap[$type] ?? $colorMap['info'];
@endphp

<div class="{{ $config['bg'] }} p-4 mb-6 rounded-2xl flex items-center justify-between">
    <div class="flex items-center">
        <div class="flex-shrink-0 {{ $config['icon'] }} mr-3">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="{{ $config['icon_svg'] }}" clip-rule="evenodd"/>
            </svg>
        </div>
        <div>
            @if($title)
                <p class="text-sm font-semibold {{ $config['text'] }}">{{ $title }}</p>
            @endif
            <p class="text-sm font-medium {{ $config['text'] }}">{{ $slot }}</p>
        </div>
    </div>
    @if($dismissible)
    <button type="button" onclick="this.parentElement.remove()" class="{{ $config['icon'] }} hover:opacity-70 transition-opacity">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
            </svg>
    </button>
    @endif
</div>
