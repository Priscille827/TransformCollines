<x-app-layout title="Valider ma commande">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('panier.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour au panier
        </a>

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center shadow-xl shadow-green-500/20">
                <x-icon name="check-circle" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Valider ma commande</h1>
            <p class="text-stone-500">Vérifiez votre panier avant de confirmer</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl text-sm">
                <div class="flex items-start gap-2 mb-1">
                    <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                    <p class="font-bold">Veuillez corriger :</p>
                </div>
                <ul class="list-disc list-inside space-y-0.5 mt-2 pl-7">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Récap par vendeur --}}
        @foreach($parVendeur as $cle => $items)
            @php
                $premier = $items->first();
                $vendeurUser = $premier->offre->producteur?->utilisateur ?? $premier->offre->unite?->utilisateur;
                $sousTotal = $items->sum(fn($i) => $i->sousTotal());
            @endphp

            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5 mb-4">
                {{-- Vendeur --}}
                <div class="flex items-center gap-3 pb-4 mb-4 border-b border-stone-100">
                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center font-black flex-shrink-0">
                        {{ strtoupper(substr($vendeurUser->nom ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-900">{{ $vendeurUser->nom ?? 'Vendeur' }}</p>
                        <p class="text-xs text-stone-500 flex items-center gap-1">
                            <x-icon name="map-pin" size="3" />
                            {{ $vendeurUser->commune->nom ?? '—' }}
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                        <x-icon name="check-circle-2" size="3" />
                        Vérifié
                    </span>
                </div>

                {{-- Articles --}}
                <div class="space-y-3 mb-4">
                    @foreach($items as $item)
                        @php
                            $offre = $item->offre;
                            $uniteNom = $offre->uniteMesure->nom ?? 'unité';
                            $pluriel = $item->quantite > 1 ? 's' : '';
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 rounded-xl bg-stone-50 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($offre->photo_principale)
                                    <img src="{{ Storage::url($offre->photo_principale) }}" class="w-full h-full object-cover">
                                @else
                                    <x-icon name="package" size="6" class="text-stone-300" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-bold text-stone-900 text-sm line-clamp-1">{{ $offre->titre }}</p>
                                <p class="text-xs text-stone-500">
                                    {{ number_format($item->quantite, 0, ',', ' ') }} {{ $uniteNom }}{{ $pluriel }}
                                    @if($offre->poids_unitaire_kg)
                                        · {{ number_format($item->quantite * $offre->poids_unitaire_kg, 0, ',', ' ') }} kg
                                    @endif
                                </p>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ number_format($offre->prixPourQuantite((float) $item->quantite), 0, ',', ' ') }} F / {{ $uniteNom }}
                                </p>
                            </div>
                            <p class="font-bold text-stone-900 whitespace-nowrap">
                                {{ number_format($item->sousTotal(), 0, ',', ' ') }} F
                            </p>
                        </div>
                    @endforeach
                </div>

                {{-- Sous-total --}}
                <div class="flex items-center justify-between pt-4 border-t border-stone-100">
                    <span class="text-sm text-stone-500">Sous-total vendeur</span>
                    <span class="font-black text-green-800 text-lg">{{ number_format($sousTotal, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        @endforeach

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('commandes.store') }}"
              class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6 space-y-5"
              x-data="{ mode: 'retrait' }">
            @csrf

            <h2 class="font-black text-stone-900 text-lg flex items-center gap-2">
                <x-icon name="truck" size="5" class="text-green-700" />
                Mode de réception
            </h2>

            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                       :class="mode === 'retrait' ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300'">
                    <input type="radio" name="mode_reception" value="retrait" x-model="mode" class="sr-only">
                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition"
                             :class="mode === 'retrait' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                            <x-icon name="package" size="6" />
                        </div>
                        <p class="font-bold text-sm" :class="mode === 'retrait' ? 'text-green-800' : 'text-stone-700'">
                            Retrait sur place
                        </p>
                        <p class="text-[10px] text-stone-400 mt-1">Chez le vendeur</p>
                    </div>
                </label>

                <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                       :class="mode === 'livraison' ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300'">
                    <input type="radio" name="mode_reception" value="livraison" x-model="mode" class="sr-only">
                    <div class="text-center">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition"
                             :class="mode === 'livraison' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                            <x-icon name="truck" size="6" />
                        </div>
                        <p class="font-bold text-sm" :class="mode === 'livraison' ? 'text-green-800' : 'text-stone-700'">
                            Livraison
                        </p>
                        <p class="text-[10px] text-stone-400 mt-1">À votre adresse</p>
                    </div>
                </label>
            </div>

            {{-- Adresse livraison --}}
            <div x-show="mode === 'livraison'" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Adresse de livraison <span class="text-red-500">*</span>
                </label>
                <textarea name="adresse_livraison" rows="3" maxlength="500"
                          placeholder="Indiquez l'adresse précise de livraison"
                          class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition resize-none">{{ old('adresse_livraison') }}</textarea>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Notes (optionnel)
                </label>
                <textarea name="notes" rows="2" maxlength="1000"
                          placeholder="Instructions particulières..."
                          class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition resize-none">{{ old('notes') }}</textarea>
            </div>

            {{-- Info paiement --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4">
                <div class="flex items-start gap-3">
                    <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-blue-900">
                        Le <strong>paiement sera convenu directement</strong> avec chaque vendeur après acceptation de la commande.
                    </p>
                </div>
            </div>

            {{-- Total --}}
            <div class="bg-gradient-to-br from-green-50 to-lime-50 rounded-2xl p-5 flex items-center justify-between border-2 border-green-100">
                <div>
                    <p class="text-sm text-green-700 font-bold uppercase tracking-wider">Total</p>
                    <p class="text-xs text-green-600 mt-0.5">Toutes taxes comprises</p>
                </div>
                <p class="text-3xl font-black text-green-800">{{ number_format($total, 0, ',', ' ') }} FCFA</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('panier.index') }}"
                   class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                    Retour au panier
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-green-800 hover:bg-green-900 text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                    <x-icon name="check-circle-2" size="5" />
                    Confirmer la commande
                </button>
            </div>
        </form>
    </div>
</x-app-layout>