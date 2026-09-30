<x-app-layout title="Mes coopératives">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <x-icon name="users" size="6" class="text-green-700" />
                Mes coopératives virtuelles
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Les pools collectifs auxquels vous participez
            </p>
        </div>

        @if($participations->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <x-icon name="users" size="12" class="mx-auto mb-3 text-gray-300" />
                <p class="font-medium text-gray-700 mb-1">Vous ne participez à aucune coopérative</p>
                <p class="text-sm text-gray-500 mb-5">Rejoignez un pool pour répondre collectivement à un besoin.</p>
                <a href="{{ route('cooperative.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                    <x-icon name="search" size="4" />
                    Voir les besoins ouverts
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($participations as $besoinId => $p)
                    @php
                        $besoin = $p['besoin'];
                        $objectif = (float) $besoin->quantite_recherchee;
                        $total = $p['total_pool'];
                        $pct = $objectif > 0 ? min(100, round(($total / $objectif) * 100, 1)) : 0;
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4 mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-semibold text-gray-900">{{ $besoin->produit->nom }}</span>
                                    @if($pct >= 100)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-100 text-green-800 rounded-full text-xs font-medium">
                                            <x-icon name="check-circle-2" size="3" />
                                            Pool complet
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                    <x-icon name="factory" size="3" />
                                    {{ $besoin->unite->utilisateur->nom }}
                                    <span class="text-gray-300">·</span>
                                    <x-icon name="map-pin" size="3" />
                                    {{ $besoin->unite->utilisateur->commune->nom }}
                                </p>
                            </div>
                            <span class="text-xs text-gray-500 whitespace-nowrap">
                                Échéance {{ $besoin->delai->format('d/m/Y') }}
                            </span>
                        </div>

                        {{-- Ma contribution --}}
                        <div class="bg-green-50 rounded-lg p-3 mb-3 flex items-center justify-between">
                            <span class="text-sm text-gray-700 flex items-center gap-2">
                                <x-icon name="user-check" size="4" class="text-green-700" />
                                Ma contribution
                            </span>
                            <span class="font-bold text-green-700">
                                {{ number_format($p['ma_contribution'], 0, ',', ' ') }} kg
                            </span>
                        </div>

                        {{-- Progression --}}
                        <div class="mb-3">
                            <div class="flex justify-between text-xs mb-1">
                                <span class="text-gray-600">Pool collectif ({{ $p['nb_membres'] }} membres)</span>
                                <span class="font-semibold">
                                    {{ number_format($total, 0, ',', ' ') }} / {{ number_format($objectif, 0, ',', ' ') }} kg
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r from-lime-400 to-green-500 h-2 rounded-full transition-all"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <a href="{{ route('besoins.show', $besoin) }}"
                           class="inline-flex items-center gap-1.5 text-xs text-green-700 font-medium hover:underline">
                            Voir le détail du besoin
                            <x-icon name="arrow-right" size="3" />
                        </a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>