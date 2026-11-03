<x-app-layout title="Propositions reçues">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Propositions reçues</h1>
                <p class="text-stone-500">Acceptez et payez, ou refusez chaque proposition</p>
            </div>
            <a href="{{ route('receptions.historique') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-white hover:bg-stone-50 text-stone-700 border-2 border-stone-200 rounded-2xl text-sm font-bold transition">
                <x-icon name="history" size="4" />
                Voir l'historique
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-600 text-red-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if($dispos->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-green-100 text-green-700 flex items-center justify-center">
                    <x-icon name="package-check" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune proposition en attente</p>
                <p class="text-sm text-stone-500">
                    Les producteurs qui répondent à vos besoins apparaîtront ici.
                </p>
            </div>
        @else
            {{-- Bandeau info --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 mb-6 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-blue-900">
                    <strong>Accepter et payer</strong> crée une commande et vous redirige vers le paiement Kkiapay.
                    <strong>Refuser</strong> libère le stock réservé et notifie le producteur.
                </p>
            </div>

            {{-- Grouper par besoin --}}
            @php
                $parBesoin = $dispos->groupBy('besoin_id');
            @endphp

            @foreach($parBesoin as $besoinId => $propositions)
                @php
                    $besoin = $propositions->first()->besoin;
                    $totalCouvert = $propositions->sum('quantite_kg_equivalent');
                    $pct = min(100, round(($totalCouvert / $besoin->quantite_recherchee) * 100, 1));
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border-2 border-stone-100 p-6 mb-6">
                    {{-- Header besoin --}}
                    <div class="flex items-start justify-between mb-4 pb-4 border-b border-stone-100 flex-wrap gap-3">
                        <div>
                            <h2 class="text-xl font-black text-stone-900">{{ $besoin->produit->nom }}</h2>
                            <p class="text-xs text-stone-500">
                                Besoin #{{ $besoin->id }} · {{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg recherchés
                                · échéance {{ $besoin->delai->format('d/m/Y') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-stone-500 font-black uppercase tracking-wider">Couverture</p>
                            <p class="text-2xl font-black {{ $pct >= 100 ? 'text-green-700' : 'text-amber-700' }}">{{ $pct }} %</p>
                        </div>
                    </div>

                    {{-- Liste propositions --}}
                    <div class="space-y-3">
                        @foreach($propositions as $dispo)
                            @php
                                $prixUnitaire = $dispo->prixB2BApplique();
                                $montant = $prixUnitaire * (float) $dispo->quantite;
                                $nomUnite = $dispo->uniteMesure?->nom ?? 'unité';
                            @endphp

                            <div x-data="{ showRefus: false }" class="bg-stone-50 rounded-xl p-4 space-y-3">
                                {{-- Infos proposition --}}
                                <div class="flex items-center justify-between gap-4 flex-wrap">
                                    <div class="flex items-center gap-3 flex-1 min-w-[200px]">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="sprout" size="5" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-bold text-stone-900">{{ $dispo->producteur->utilisateur->nom }}</p>
                                            <p class="text-xs text-stone-500">
                                                {{ number_format($dispo->quantite, 0, ',', ' ') }} {{ $nomUnite }}{{ $dispo->quantite > 1 ? 's' : '' }}
                                                @if($dispo->quantite_kg_equivalent)
                                                    · {{ number_format($dispo->quantite_kg_equivalent, 0, ',', ' ') }} kg
                                                @endif
                                            </p>
                                            <p class="text-xs text-stone-400 mt-0.5">
                                                Disponible le {{ $dispo->date_disponibilite?->format('d/m/Y') ?? '—' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Prix B2B</p>
                                        <p class="text-sm font-bold text-stone-700">
                                            {{ number_format($prixUnitaire, 0, ',', ' ') }} F / {{ $nomUnite }}
                                        </p>
                                    </div>

                                    <div class="text-right">
                                        <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total</p>
                                        <p class="font-black text-green-800 text-lg">{{ number_format($montant, 0, ',', ' ') }} F</p>
                                    </div>
                                </div>

                                {{-- Boutons --}}
                                <div class="flex items-center gap-2 pt-3 border-t border-stone-200 flex-wrap">
                                    <form method="POST" action="{{ route('receptions.accepter-payer', $dispo) }}" class="flex-1">
                                        @csrf
                                        <button type="submit"
                                                class="w-full px-5 py-2.5 bg-gradient-to-r from-green-800 to-green-600 hover:from-green-900 hover:to-green-700 text-white rounded-xl font-bold text-sm shadow-md flex items-center justify-center gap-2">
                                            <x-icon name="credit-card" size="4" />
                                            Accepter et payer
                                        </button>
                                    </form>

                                    <button type="button" @click="showRefus = !showRefus"
                                            class="px-5 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 border-2 border-red-200 rounded-xl font-bold text-sm flex items-center gap-2">
                                        <x-icon name="x-circle" size="4" />
                                        Refuser
                                    </button>
                                </div>

                                {{-- Formulaire refus (caché) --}}
                                <div x-show="showRefus" x-transition x-cloak
                                     class="bg-red-50 border-2 border-red-200 rounded-xl p-4">
                                    <form method="POST" action="{{ route('receptions.refuser', $dispo) }}" class="space-y-3">
                                        @csrf
                                        <div>
                                            <label class="block text-sm font-bold text-red-900 mb-2">
                                                Motif du refus <span class="text-red-600">*</span>
                                            </label>
                                            <textarea name="motif_refus" rows="2" required maxlength="500"
                                                      placeholder="Ex. Prix trop élevé, quantité insuffisante, délai non respecté..."
                                                      class="w-full border-2 border-red-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-red-500/20 focus:border-red-500 outline-none transition bg-white"></textarea>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="submit"
                                                    class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 rounded-lg transition">
                                                Confirmer le refus
                                            </button>
                                            <button type="button" @click="showRefus = false"
                                                    class="px-4 py-2 text-sm font-bold text-stone-600 hover:bg-stone-200 rounded-lg transition">
                                                Annuler
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="mt-6">{{ $dispos->links() }}</div>
        @endif
    </div>
</x-app-layout>