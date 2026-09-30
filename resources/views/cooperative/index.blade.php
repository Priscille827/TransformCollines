<x-app-layout title="Coopérative virtuelle">
    <div class="max-w-5xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <x-icon name="users" size="6" class="text-green-700" />
                Coopérative virtuelle
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Unissez-vous à d'autres producteurs pour répondre ensemble à un gros besoin
            </p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start gap-2">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Explication --}}
        <div class="bg-gradient-to-br from-green-50 to-lime-50 border border-green-100 rounded-xl p-5 mb-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                    <x-icon name="lightbulb" size="5" />
                </div>
                <div>
                    <p class="font-semibold text-green-900 mb-1">Comment ça marche ?</p>
                    <p class="text-sm text-green-800">
                        Vous ne pouvez pas répondre seul à un besoin de 20 tonnes ? Rejoignez le pool.
                        La plateforme regroupe automatiquement les contributions de chacun et présente
                        une réponse collective à l'unité.
                    </p>
                </div>
            </div>
        </div>

        {{-- Pools en cours --}}
        @if($pools->isNotEmpty())
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-icon name="users" size="5" class="text-green-700" />
                    Coopératives en formation ({{ $pools->count() }})
                </h2>

                <div class="space-y-4">
                    @foreach($pools as $besoinId => $pool)
                        @php
                            $besoin = $pool['besoin'];
                            $total = $pool['total_quantite'];
                            $objectif = (float) $besoin->quantite_recherchee;
                            $pct = $objectif > 0 ? min(100, round(($total / $objectif) * 100, 1)) : 0;
                        @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                            <div class="flex items-start justify-between gap-4 mb-3">
                                <div>
                                    <div class="font-semibold text-gray-900">{{ $besoin->produit->nom }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        {{ $besoin->unite->utilisateur->nom }} · {{ $besoin->unite->utilisateur->commune->nom }}
                                    </div>
                                </div>
                                <span class="text-xs text-gray-500">
                                    Échéance {{ $besoin->delai->format('d/m/Y') }}
                                </span>
                            </div>

                            {{-- Membres du pool --}}
                            <div class="flex items-center gap-2 mb-3">
                                @foreach($pool['membres']->take(5) as $m)
                                    <div class="w-8 h-8 rounded-full bg-amber-400 text-green-900 flex items-center justify-center text-xs font-bold"
                                         title="{{ $m->producteur->utilisateur->nom }}">
                                        {{ strtoupper(substr($m->producteur->utilisateur->nom, 0, 1)) }}
                                    </div>
                                @endforeach
                                @if($pool['membres']->count() > 5)
                                    <div class="w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-xs font-bold">
                                        +{{ $pool['membres']->count() - 5 }}
                                    </div>
                                @endif
                                <span class="text-xs text-gray-500 ml-1">
                                    {{ $pool['membres']->count() }} producteur(s)
                                </span>
                            </div>

                            {{-- Progression --}}
                            <div class="mb-3">
                                <div class="flex items-center justify-between mb-1 text-xs">
                                    <span class="text-gray-600">Pool collectif</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ number_format($total, 0, ',', ' ') }} / {{ number_format($objectif, 0, ',', ' ') }} kg
                                        ({{ $pct }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-lime-400 to-green-500 h-2 rounded-full transition-all"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </div>

                            {{-- CTA --}}
                            @if($pool['membres']->where('producteur_id', auth()->user()->producteur->id ?? 0)->isEmpty())
                                <a href="{{ route('cooperative.rejoindre.form', $besoin) }}"
                                   class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition">
                                    <x-icon name="plus" size="3" />
                                    Rejoindre ce pool
                                </a>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs text-green-700 font-medium">
                                    <x-icon name="check-circle-2" size="3" />
                                    Vous participez déjà
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Besoins ouverts à la coopération --}}
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
                <x-icon name="clipboard-list" size="5" class="text-amber-600" />
                Besoins ouverts à la coopération
            </h2>

            @if($besoins->isEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                    <x-icon name="inbox" size="12" class="mx-auto mb-3 text-gray-300" />
                    <p class="text-sm text-gray-500">Aucun besoin ouvert pour l'instant</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($besoins as $besoin)
                        @php
                            $pct = (float) $besoin->taux_couverture;
                            $manquant = max(0, $besoin->quantite_recherchee * (1 - $pct / 100));
                        @endphp
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 flex items-center justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-medium text-gray-900">{{ $besoin->produit->nom }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                        {{ $pct >= 70 ? 'bg-lime-100 text-lime-800' : ($pct >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $pct }}%
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                    <x-icon name="factory" size="3" />
                                    {{ $besoin->unite->utilisateur->nom }}
                                    <span class="text-gray-300">·</span>
                                    <x-icon name="calendar" size="3" />
                                    {{ $besoin->delai->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Manquant : <strong>{{ number_format($manquant, 0, ',', ' ') }} kg</strong>
                                </p>
                            </div>
                            <a href="{{ route('cooperative.rejoindre.form', $besoin) }}"
                               class="inline-flex items-center gap-1.5 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition whitespace-nowrap">
                                <x-icon name="plus" size="3" />
                                Rejoindre
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

{{-- MES PARTICIPATIONS --}}
@if($mesParticipations->isNotEmpty())
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-3 flex items-center gap-2">
            <x-icon name="user-check" size="5" class="text-green-700" />
            Mes participations ({{ $mesParticipations->count() }})
        </h2>

        <div class="space-y-4">
            @foreach($mesParticipations as $besoinId => $participation)
                @php
                    $besoin = $participation['besoin'];
                    $objectif = (float) $besoin->quantite_recherchee;
                    $total = $participation['total_pool'];
                    $pct = $objectif > 0 ? min(100, round(($total / $objectif) * 100, 1)) : 0;
                @endphp
                <div class="bg-gradient-to-br from-green-50 to-lime-50 rounded-xl shadow-sm border-2 border-green-200 p-5">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-900">{{ $besoin->produit->nom }}</span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-600 text-white rounded-full text-xs font-medium">
                                    <x-icon name="check" size="3" />
                                    Vous participez
                                </span>
                            </div>
                            <div class="text-xs text-gray-600 flex items-center gap-1.5">
                                <x-icon name="factory" size="3" />
                                {{ $besoin->unite->utilisateur->nom }} · {{ $besoin->unite->utilisateur->commune->nom }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-xs text-gray-500">Échéance</div>
                            <div class="text-sm font-medium text-gray-900">{{ $besoin->delai->format('d/m/Y') }}</div>
                        </div>
                    </div>

                    {{-- Ma contribution --}}
                    <div class="bg-white rounded-lg p-3 mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-sm">
                            <x-icon name="package" size="4" class="text-green-700" />
                            <span class="text-gray-600">Ma contribution</span>
                        </div>
                        <span class="font-bold text-green-700">
                            {{ number_format($participation['ma_contribution'], 0, ',', ' ') }} kg
                        </span>
                    </div>

                    {{-- Progression du pool --}}
                    <div class="mb-3">
                        <div class="flex items-center justify-between mb-1 text-xs">
                            <span class="text-gray-600">
                                Pool collectif · {{ $participation['nb_membres'] }} producteur(s)
                            </span>
                            <span class="font-semibold text-gray-900">
                                {{ number_format($total, 0, ',', ' ') }} / {{ number_format($objectif, 0, ',', ' ') }} kg
                                ({{ $pct }}%)
                            </span>
                        </div>
                        <div class="w-full bg-white rounded-full h-2.5 overflow-hidden border border-green-200">
                            <div class="bg-gradient-to-r from-lime-400 to-green-500 h-full rounded-full transition-all"
                                 style="width: {{ $pct }}%"></div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-2">
                        <a href="{{ route('besoins.show', $besoin) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-white rounded-lg transition border border-gray-200">
                            <x-icon name="eye" size="3" />
                            Voir le besoin
                        </a>
                        @if($pct >= 100)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-100 text-green-800 rounded-lg text-xs font-medium">
                                <x-icon name="check-circle-2" size="3" />
                                Pool complet — en attente de collecte
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

    </div>
</x-app-layout>