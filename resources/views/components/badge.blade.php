@props(['type' => 'gray'])

@php
    $classes = match($type) {
        'success' => 'bg-green-100 text-green-800',
        'warning' => 'bg-amber-100 text-amber-800',
        'danger'  => 'bg-red-100 text-red-800',
        'info'    => 'bg-blue-100 text-blue-800',
        'primary' => 'bg-green-600 text-white',
        default   => 'bg-gray-100 text-gray-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium $classes"]) }}>
    {{ $slot }}
</span>