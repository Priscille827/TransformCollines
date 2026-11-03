<x-app-layout title="Tableau de bord">
    @php
        $type = $user->type_compte;
        $initial = strtoupper(substr($user->nom, 0, 1));

        // Message d'accueil selon l'heure
        $heure = now()->hour;
        $salutation = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');
    @endphp

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl sm:text-4xl font-black text-stone-900 mb-2">
            {{ $salutation }}, {{ Str::limit($user->nom, 25) }} 👋
        </h1>
        <p class="text-stone-500 flex items-center gap-2 text-sm">
            <x-icon name="map-pin" size="4" />
            {{ $user->commune->nom ?? '—' }}
            @if($user->village) · {{ $user->village }} @endif
        </p>
    </div>

    {{-- Bannière "compte en attente" --}}
    @if($user->isEnAttente() && !$user->isAdmin())
        <div class="bg-gradient-to-r from-amber-50 to-amber-100 border-2 border-amber-200 rounded-3xl p-6 mb-8 flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                <x-icon name="clock" size="6" />
            </div>
            <div>
                <p class="font-black text-amber-900 text-lg mb-1">Compte en attente de validation</p>
                <p class="text-sm text-amber-800">
                    Votre compte doit être validé par un administrateur avant de pouvoir utiliser toutes les fonctionnalités.
                    Vous serez notifié dès que possible.
                </p>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- DASHBOARD ADMIN --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if($type === 'admin')
        @include('dashboard.admin', ['stats' => $stats, 'derniersComptes' => $derniersComptes, 'dernieresPropositions' => $dernieresPropositions])

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- DASHBOARD PRODUCTEUR --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($type === 'producteur')
        @include('dashboard.producteur')

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- DASHBOARD UNITÉ --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($type === 'unite_transformation')
        @include('dashboard.unite')

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- DASHBOARD POPULATION --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @elseif($type === 'population')
        @include('dashboard.population')
    @endif
</x-app-layout>