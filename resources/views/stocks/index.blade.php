<x-app-layout title="Mon stock">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Mon stock</h1>
                <p class="text-stone-500">Gérez ce que vous avez en réserve</p>
            </div>
            <a href="{{ route('stocks.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-lg shadow-green-800/20 transition">
                <x-icon name="plus" size="4" />
                Ajouter du stock
            </a>
        </div>

        @if($stocks->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center">
                    <x-icon name="warehouse" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Votre stock est vide</p>
                <p class="text-sm text-stone-500 mb-6 max-w-md mx-auto">
                    Ajoutez vos produits pour pouvoir les mettre en vente ou répondre aux besoins des unités.
                </p>
                <a href="{{ route('stocks.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                    <x-icon name="plus" size="4" />
                    Ajouter mon premier stock
                </a>
            </div>
        @else
            {{-- Stats --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-stone-500 font-bold uppercase tracking-wider">Références</span>
                        <x-icon name="package" size="5" class="text-stone-400" />
                    </div>
                    <p class="text-3xl font-black text-stone-900">{{ $stocks->total() }}</p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-green-700 font-bold uppercase tracking-wider">Volume total</span>
                        <x-icon name="scale" size="5" class="text-green-600" />
                    </div>
                    <p class="text-3xl font-black text-green-700">
                        {{ number_format($totalKg, 0, ',', ' ') }}
                        <span class="text-lg font-bold text-green-600">kg éq.</span>
                    </p>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-amber-700 font-bold uppercase tracking-wider">Valeur estimée</span>
                        <x-icon name="wallet" size="5" class="text-amber-600" />
                    </div>
                    <p class="text-3xl font-black text-amber-700">
                        {{ number_format($totalValeur, 0, ',', ' ') }}
                        <span class="text-lg font-bold text-amber-600">F</span>
                    </p>
                </div>
            </div>

            {{-- Liste --}}
            <div class="space-y-4">
                @foreach($stocks as $stock)
                    @php
                        $vendable = $stock->quantite_vendable;
                        $reserve = (float) $stock->quantite_reservee;
                        $alerte = $stock->isEnAlerte();
                        $uniteNom = $stock->uniteMesure?->nom ?? 'unité';
                        $uniteSymbole = $stock->uniteMesure?->symbole ?? '';
                        $plurielVendable = $vendable > 1 ? 's' : '';
                        $plurielReserve = $reserve > 1 ? 's' : '';
                        $plurielTotal = $stock->quantite_disponible > 1 ? 's' : '';
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border {{ $alerte ? 'border-amber-300' : 'border-stone-100' }} overflow-hidden hover:shadow-md transition">
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-4 mb-4 flex-wrap">
                                <div class="flex items-center gap-4 min-w-0 flex-1">
                                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                                        <x-icon name="sprout" size="7" />
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-stone-900 text-lg">{{ $stock->produit->nom }}</h3>
                                        <p class="text-xs text-stone-500">
                                            {{ ucfirst($stock->produit->type_produit) }}
                                            @if($stock->poids_unitaire_kg && $stock->uniteMesure && $stock->uniteMesure->isContenant())
                                                · 1 {{ $uniteNom }} = {{ number_format($stock->poids_unitaire_kg, 0, ',', ' ') }} {{ $stock->uniteReference?->symbole ?? 'kg' }}
                                            @endif
                                        </p>
                                        <p class="text-xs text-stone-400 mt-0.5">
                                            Mis à jour {{ $stock->updated_at->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                @if($alerte)
                                    <span class="inline-flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                        <x-icon name="alert-triangle" size="3.5" />
                                        Stock faible
                                    </span>
                                @endif
                            </div>

                            {{-- Quantités --}}
                            <div class="grid grid-cols-3 gap-3 mb-4">
                                <div class="bg-green-50 rounded-xl p-3">
                                    <p class="text-[10px] text-green-700 font-bold uppercase tracking-wider mb-1">Vendable</p>
                                    <p class="text-xl font-black text-green-800">{{ number_format($vendable, 0, ',', ' ') }}</p>
                                    <p class="text-xs text-green-600">{{ $uniteNom }}{{ $plurielVendable }}</p>
                                </div>
                                <div class="bg-amber-50 rounded-xl p-3">
                                    <p class="text-[10px] text-amber-700 font-bold uppercase tracking-wider mb-1">Réservé</p>
                                    <p class="text-xl font-black text-amber-800">{{ number_format($reserve, 0, ',', ' ') }}</p>
                                    <p class="text-xs text-amber-600">{{ $uniteNom }}{{ $plurielReserve }}</p>
                                </div>
                                <div class="bg-stone-50 rounded-xl p-3">
                                    <p class="text-[10px] text-stone-600 font-bold uppercase tracking-wider mb-1">Total</p>
                                    <p class="text-xl font-black text-stone-800">{{ number_format($stock->quantite_disponible, 0, ',', ' ') }}</p>
                                    <p class="text-xs text-stone-500">{{ $uniteNom }}{{ $plurielTotal }}</p>
                                </div>
                            </div>

                            {{-- Détails --}}
                            <div class="flex items-center justify-between flex-wrap gap-3 text-xs">
                                <div class="flex items-center gap-4 text-stone-500 flex-wrap">
                                    @if($stock->poids_unitaire_kg && !$stock->uniteMesure->est_reference)
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="info" size="3.5" />
                                            1 {{ $uniteNom }} = {{ number_format($stock->poids_unitaire_kg, 0, ',', ' ') }} {{ $stock->uniteReference?->symbole ?? 'kg' }}
                                        </span>
                                    @endif
                                    @if($stock->prix_unitaire)
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="tag" size="3.5" />
                                            {{ number_format($stock->prix_unitaire, 0, ',', ' ') }} F / {{ $uniteSymbole ?: $uniteNom }}
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="scale" size="3.5" />
                                        {{ number_format($stock->quantite_kg, 0, ',', ' ') }} {{ $stock->uniteReference?->symbole ?? 'kg' }} total
                                    </span>
                                </div>

                                <a href="{{ route('stocks.edit', $stock) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-stone-900 hover:bg-stone-800 text-white rounded-lg font-bold transition">
                                    <x-icon name="settings-2" size="3.5" />
                                    Gérer
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $stocks->links() }}</div>
        @endif
    </div>
</x-app-layout>