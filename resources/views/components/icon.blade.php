@props(['name', 'size' => 5, 'stroke' => 1.75])

@php
    $sizes = [
        3 => 'w-3 h-3',
        4 => 'w-4 h-4',
        5 => 'w-5 h-5',
        6 => 'w-6 h-6',
        7 => 'w-7 h-7',
        8 => 'w-8 h-8',
        10 => 'w-10 h-10',
        12 => 'w-12 h-12',
    ];
    $sizeClass = $sizes[$size] ?? 'w-5 h-5';
@endphp

<i data-lucide="{{ $name }}" class="{{ $sizeClass }}" stroke-width="{{ $stroke }}"></i>