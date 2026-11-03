@props(['label', 'valeur', 'icone' => null, 'couleur' => 'green'])

@php
    $couleurClasses = match($couleur) {
        'green' => 'bg-green-100 text-green-700',
        'blue'  => 'bg-blue-100 text-blue-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'red'   => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm text-gray-500 font-medium">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $valeur }}</p>
        </div>
        @if($icone)
            <div class="w-10 h-10 rounded-lg {{ $couleurClasses }} flex items-center justify-center">
                {!! $icone !!}
            </div>
        @endif
    </div>
</div>