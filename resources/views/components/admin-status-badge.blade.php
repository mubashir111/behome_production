@props([
    'status' => 'pending',
    'customClasses' => '',
])

@php
$statusConfig = [
    'pending' => ['bg' => 'text-amber-700 bg-amber-50', 'label' => 'Pending'],
    'confirmed' => ['bg' => 'text-blue-700 bg-blue-50', 'label' => 'Confirmed'],
    'processing' => ['bg' => 'text-indigo-700 bg-indigo-50', 'label' => 'Processing'],
    'on-way' => ['bg' => 'text-indigo-700 bg-indigo-50', 'label' => 'On the Way'],
    'delivered' => ['bg' => 'text-emerald-700 bg-emerald-50', 'label' => 'Delivered'],
    'completed' => ['bg' => 'text-emerald-700 bg-emerald-50', 'label' => 'Completed'],
    'canceled' => ['bg' => 'text-rose-700 bg-rose-50', 'label' => 'Canceled'],
    'rejected' => ['bg' => 'text-gray-700 bg-gray-50', 'label' => 'Rejected'],
    'accepted' => ['bg' => 'text-emerald-700 bg-emerald-50', 'label' => 'Accepted'],
    'active' => ['bg' => 'text-emerald-700 bg-emerald-50', 'label' => 'Active'],
    'inactive' => ['bg' => 'text-gray-700 bg-gray-50', 'label' => 'Inactive'],
];

$config = $statusConfig[$status] ?? $statusConfig['pending'];
@endphp

<span class="px-2.5 py-1 text-xs font-semibold rounded-lg {{ $config['bg'] }} {{ $customClasses }}">
    {{ $slot ?? $config['label'] }}
</span>
