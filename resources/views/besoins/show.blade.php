<x-app-layout :title="'Besoin ' . $besoin->produit->nom">
    @php
        $user = auth()->user();
        $pct = min(100, max(0, (float) $besoin->taux_couverture));
        $color = $pct >= 100 ? 'bg-green-500' : ($pct >= 70 ? 'bg-lime-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-red-500'));

        // ✅ Utiliser quantite_kg_equivalent
        $totalCouvert = $besoin->disponibilites->whereIn('statut', ['associee', 'confirmee'])->sum('quantite_kg_equivalent');
        $manquant = max(0, $besoin->quantite_recherchee - $totalCouvert);

        $estProprietaire = $user->isUnite() && $user->unite->id === $besoin->unite_id;
    @endphp

    <div class="max-w-4xl mx-auto">

        <a href="{{ route('besoins.publics') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour aux besoins
        </a>

        {{-- Carte principale --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">

            {{-- Header --}}
            <div class="flex items-start justify-between gap-4 mb-6 flex-wrap">
                <div>
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $besoin->produit->nom }}</h1>
                        @php
                            $statutBadge = match($besoin->statut) {
                                'actif'                  => ['bg-blue-100 text-blue-800', 'Actif'],
                                'partiellement_couvert'  => ['bg-amber-100 text-amber-800', 'Partiellement couvert'],
                                'couvert'                => ['bg-green-100 text-green-800', 'Couvert'],
                                'cloture'                => ['bg-gray-100 text-gray-700', 'Clôturé'],
                                'expire'                 => ['bg-red-100 text-red-800', 'Expiré'],
                                default                  => ['bg-gray-100 text-gray-700', ucfirst($besoin->statut)],
                            };
                        @endphp
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $statutBadge[0] }}">
                            {{ $statutBadge[1] }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-500 space-y-1">
                        <p class="flex items-center gap-1.5">
                            <x-icon name="factory" size="4" />
                            Publié par <strong class="text-gray-700">{{ $besoin->unite->utilisateur->nom }}</strong>
                        </p>
                        <p class="flex items-center gap-1.5">
                            <x-icon name="map-pin" size="4" />
                            {{ $besoin->unite->utilisateur->commune->nom }}
                            @if($besoin->unite->utilisateur->village) · {{ $besoin->unite->utilisateur->village }} @endif
                        </p>
                    </div>
                </div>

                {{-- Bouton Modifier (unité propriétaire) --}}
                @if($estProprietaire)
                    <div class="flex gap-2">
                        <a href="{{ route('besoins.index') }}"
                           class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg transition flex items-center gap-1.5">
                            <x-icon name="list" size="4" />
                            Mes besoins
                        </a>
                    </div>
                @endif
            </div>

            {{-- KPI --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Quantité recherchée</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Couvert</p>
                    <p class="text-xl font-bold text-green-700">{{ number_format($totalCouvert, 0, ',', ' ') }} kg</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1">Manquant</p>
                    <p class="text-xl font-bold text-red-700">{{ number_format($manquant, 0, ',', ' ') }} kg</p>
                </div>
            </div>

            {{-- Progress --}}
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-gray-700">Taux de couverture</span>
                    <span class="text-sm font-bold {{ $pct >= 100 ? 'text-green-700' : 'text-gray-900' }}">{{ $pct }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                    <div class="{{ $color }} h-3 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                </div>
            </div>

            {{-- Infos --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1 flex items-center gap-1">
                        <x-icon name="calendar" size="3" />
                        Date limite
                    </p>
                    <p class="text-sm font-medium text-gray-900">{{ $besoin->delai->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-wide mb-1 flex items-center gap-1">
                        <x-icon name="map" size="3" />
                        Zone de collecte
                    </p>
                    <p class="text-sm font-medium text-gray-900">
                        @if($besoin->communes->isNotEmpty())
                            {{ $besoin->communes->pluck('nom')->join(', ') }}
                        @else
                            Rayon {{ (int) ($besoin->unite->zone_collecte_rayon_km ?? 0) }} km
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Déclarations --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <x-icon name="users" size="5" />
                    Producteurs mobilisés ({{ $besoin->disponibilites->count() }})
                </h2>

                @if($user->isProducteur())
                    <a href="{{ route('disponibilites.create', ['besoin_id' => $besoin->id]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-green-700 hover:bg-green-800 text-white rounded-xl font-bold text-xs shadow-sm transition">
                        <x-icon name="send" size="3.5" />
                        Proposer mon produit
                    </a>
                @endif
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($besoin->disponibilites as $dispo)
                    @php
                        $uniteNom = $dispo->uniteMesure?->nom ?? 'unité';
                        $qteKg = (float) ($dispo->quantite_kg_equivalent ?? 0);
                        $qte = (float) $dispo->quantite;
                    @endphp
                    <div class="px-6 py-4 flex items-center justify-between gap-4 flex-wrap">
                        <div class="min-w-0">
                            <span class="font-medium text-gray-900">
                                {{ $dispo->producteur->utilisateur->nom }}
                            </span>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5 mt-0.5">
                                <x-icon name="map-pin" size="3" />
                                {{ $dispo->producteur->utilisateur->commune->nom }}
                            </p>

                            {{-- Prix B2B --}}
                            @php
                                $prixB2B = $dispo->prixB2BApplique();
                            @endphp
                            @if($prixB2B > 0)
                                <p class="text-xs text-amber-700 font-bold mt-1">
                                    Prix B2B : {{ number_format($prixB2B, 0, ',', ' ') }} F / {{ $uniteNom }}
                                </p>
                            @endif
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-semibold text-gray-900">
                                {{ number_format($qte, 0, ',', ' ') }} {{ $uniteNom }}{{ $qte > 1 ? 's' : '' }}
                            </p>
                            @if($qteKg > 0)
                                <p class="text-xs text-gray-500">
                                    = {{ number_format($qteKg, 0, ',', ' ') }} kg
                                </p>
                            @endif
                            <p class="text-xs text-gray-500">pour le {{ $dispo->date_disponibilite->format('d/m/Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-12 text-center text-gray-400">
                        <x-icon name="users" size="10" class="mx-auto mb-2 text-gray-300" />
                        <p class="text-sm">Aucun producteur mobilisé pour l'instant</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>