<x-app-layout title="Propositions reçues">
    <div class="max-w-4xl mx-auto">

        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2">Propositions reçues</h1>
            <p class="text-stone-500">Acceptez et payez chaque proposition pour lancer la livraison</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl">
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-600 text-red-800 px-4 py-3 rounded-r-xl">
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        @if($dispos->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-700 text-lg mb-2">Aucune proposition en attente</p>
                <p class="text-sm text-stone-500">Les producteurs qui répondent à vos besoins apparaîtront ici.</p>
            </div>
        @else
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
                            @endphp

                            <div class="bg-stone-50 rounded-xl p-4 flex items-center justify-between gap-4 flex-wrap">
                                <div class="flex items-center gap-3 flex-1 min-w-[200px]">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                                        <x-icon name="sprout" size="5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-stone-900">{{ $dispo->producteur->utilisateur->nom }}</p>
                                        <p class="text-xs text-stone-500">
                                            {{ number_format($dispo->quantite, 0, ',', ' ') }} {{ $dispo->uniteMesure->nom }}{{ $dispo->quantite > 1 ? 's' : '' }}
                                            @if($dispo->quantite_kg_equivalent)
                                                · {{ number_format($dispo->quantite_kg_equivalent, 0, ',', ' ') }} kg
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Prix B2B</p>
                                    <p class="text-sm font-bold text-stone-700">
                                        {{ number_format($prixUnitaire, 0, ',', ' ') }} F / {{ $dispo->uniteMesure->nom }}
                                    </p>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total</p>
                                    <p class="font-black text-green-800 text-lg">{{ number_format($montant, 0, ',', ' ') }} F</p>
                                </div>

                                <form method="POST" action="{{ route('receptions.accepter-payer', $dispo) }}">
                                    @csrf
                                    <button type="submit"
                                            class="px-5 py-2.5 bg-gradient-to-r from-green-800 to-green-600 hover:from-green-900 hover:to-green-700 text-white rounded-xl font-bold text-sm shadow-md flex items-center gap-2 whitespace-nowrap">
                                        <x-icon name="credit-card" size="4" />
                                        Accepter et payer
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</x-app-layout>