<x-app-layout title="Tableau de bord">
    @php $user = auth()->user(); @endphp

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Bonjour, {{ Str::limit($user->nom, 30) }}</h1>
        <p class="text-gray-500 text-sm mt-1 flex items-center gap-1.5">
            <x-icon name="map-pin" size="4" />
            {{ $user->commune->nom ?? '' }}
            @if($user->village) · {{ $user->village }} @endif
        </p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Besoins actifs</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['besoins_actifs'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                    <x-icon name="clipboard-list" size="5" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Disponibilités</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['disponibilites'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-100 text-green-700 flex items-center justify-center">
                    <x-icon name="package" size="5" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Taux moyen</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['taux_moyen'] ?? 0 }}%</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                    <x-icon name="trending-up" size="5" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['transactions'] ?? 0 }}</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-red-100 text-red-700 flex items-center justify-center">
                    <x-icon name="handshake" size="5" />
                </div>
            </div>
        </div>
    </div>

    {{-- Actions rapides --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
        @if($user->isUnite() || $user->type_compte === 'admin')
            <a href="{{ route('besoins.create') }}"
               class="group bg-gradient-to-br from-green-600 to-green-700 text-white rounded-xl p-5 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Publier un besoin</div>
                    <div class="text-green-100 text-sm mt-0.5">Indiquez ce que vous recherchez</div>
                </div>
                <x-icon name="plus-circle" size="8" class="opacity-80 group-hover:translate-x-1 transition" />
            </a>
        @endif

        @if($user->isProducteur() || $user->type_compte === 'admin')
            <a href="{{ route('disponibilites.create') }}"
               class="group bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl p-5 shadow-sm hover:shadow-md transition flex items-center justify-between">
                <div>
                    <div class="font-semibold text-lg">Déclarer une disponibilité</div>
                    <div class="text-amber-100 text-sm mt-0.5">Annoncez vos produits à vendre</div>
                </div>
                <x-icon name="plus-circle" size="8" class="opacity-80 group-hover:translate-x-1 transition" />
            </a>
        @endif
    </div>

    {{-- Besoins récents --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900">Besoins récents sur la plateforme</h2>
            <a href="{{ route('besoins.publics') }}"
               class="text-sm text-green-700 font-medium hover:underline flex items-center gap-1">
                Voir tout
                <x-icon name="arrow-right" size="4" />
            </a>
        </div>

        <div class="divide-y divide-gray-100">
            @forelse($besoinsRecents ?? [] as $besoin)
                <a href="{{ route('besoins.show', $besoin) }}" class="block px-6 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-900">{{ $besoin->produit->nom }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($besoin->taux_couverture >= 100) bg-green-100 text-green-800
                                    @elseif($besoin->taux_couverture >= 40) bg-amber-100 text-amber-800
                                    @else bg-red-100 text-red-800 @endif">
                                    {{ $besoin->taux_couverture }}%
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 flex items-center gap-1.5">
                                <x-icon name="factory" size="4" />
                                {{ $besoin->unite->utilisateur->nom }}
                                <span class="text-gray-300">·</span>
                                <x-icon name="map-pin" size="4" />
                                {{ $besoin->unite->utilisateur->commune->nom }}
                            </div>
                        </div>
                        <div class="text-right text-sm shrink-0">
                            <div class="font-semibold text-gray-900">{{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg</div>
                            <div class="text-gray-500 text-xs">Échéance {{ $besoin->delai->format('d/m/Y') }}</div>
                        </div>
                    </div>
                    <div class="mt-3">
                        @php
                            $pct = min(100, max(0, $besoin->taux_couverture));
                            $color = $pct >= 100 ? 'bg-green-500' : ($pct >= 70 ? 'bg-lime-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-red-500'));
                        @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                            <div class="{{ $color }} h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-6 py-12 text-center text-gray-400">
                    <x-icon name="inbox" size="12" class="mx-auto mb-2 text-gray-300" />
                    <p class="text-sm">Aucun besoin publié pour le moment</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>