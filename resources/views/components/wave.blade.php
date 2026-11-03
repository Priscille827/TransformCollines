@props(['color' => '#0f5132', 'opacity' => '0.1'])

<svg {{ $attributes->merge(['class' => 'absolute inset-x-0 bottom-0 w-full h-24']) }}
     viewBox="0 0 1440 120" fill="none" preserveAspectRatio="none">
    <path d="M0,64 C240,96 480,96 720,64 C960,32 1200,32 1440,64 L1440,120 L0,120 Z"
          fill="{{ $color }}" fill-opacity="{{ $opacity }}"/>
</svg>