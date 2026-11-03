@props(['valeur' => 0, 'taille' => 'normal'])

@php
    $valeur = min(100, max(0, $valeur));
    $couleur = match(true) {
        $valeur >= 100 => 'bg-green-500',
        $valeur >= 70  => 'bg-lime-500',
        $valeur >= 40  => 'bg-amber-500',
        default        => 'bg-red-500',
    };
    $hauteur = $taille === 'large' ? 'h-3' : 'h-2';
@endphp

<div class="w-full bg-gray-200 rounded-full {{ $hauteur }} overflow-hidden">
    <div class="{{ $couleur }} {{ $hauteur }} rounded-full transition-all duration-500"
         style="width: {{ $valeur }}%"></div>
</div>