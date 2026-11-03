<x-app-layout title="Mettre en vente">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('offres.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour à mes ventes
        </a>

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center shadow-xl shadow-green-500/20">
                <x-icon name="tag" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Mettre en vente</h1>
            <p class="text-stone-500">Publiez un produit de votre stock sur le marché</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl mb-6 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('offres.store') }}"
              class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6 sm:p-8 space-y-6"
              x-data="offreForm()">

            @csrf

            {{-- Choix du stock --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-3">
                    Que mettez-vous en vente ? <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($mesStocks as $stock)
                        <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                               :class="stockId == {{ $stock->id }} ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300 hover:bg-stone-50'">
                            <input type="radio" name="stock_id" value="{{ $stock->id }}"
                                   x-model="stockId"
                                   @change="selectionnerStock({{ $stock->id }})"
                                   class="sr-only" required>

                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center transition flex-shrink-0"
                                         :class="stockId == {{ $stock->id }} ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                                        <x-icon name="package" size="5" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-stone-900 text-sm truncate">{{ $stock->produit->nom }}</p>
                                        <p class="text-xs text-stone-500">
                                            {{ $stock->uniteMesure->nom }}
                                            @if($stock->poids_unitaire_kg && $stock->uniteReference)
                                                · 1 = {{ number_format($stock->poids_unitaire_kg, 0, ',', ' ') }} {{ $stock->uniteReference->symbole }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div x-show="stockId == {{ $stock->id }}"
                                     class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center flex-shrink-0">
                                    <x-icon name="check" size="3.5" />
                                </div>
                            </div>

                            <div class="bg-white rounded-lg p-2 border border-stone-100">
                                <p class="text-[10px] text-stone-500 font-bold uppercase tracking-wider mb-1">Vendable</p>
                                <p class="font-black text-stone-900 text-lg">
                                    {{ number_format($stock->quantite_vendable, 0, ',', ' ') }}
                                    <span class="text-xs font-bold text-stone-500">{{ $stock->uniteMesure->nom }}{{ $stock->quantite_vendable > 1 ? 's' : '' }}</span>
                                </p>
                                @if($stock->prix_unitaire)
                                    <p class="text-[10px] text-stone-400">
                                        Prix habituel : {{ number_format($stock->prix_unitaire, 0, ',', ' ') }} F
                                    </p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Titre --}}
            <div x-show="stockId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Titre de l'annonce <span class="text-red-500">*</span>
                </label>
                <input type="text" name="titre" required maxlength="150"
                       value="{{ old('titre') }}"
                       placeholder="Ex. Manioc frais de Savalou"
                       class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
            </div>

            {{-- Description --}}
            <div x-show="stockId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Description</label>
                <textarea name="description" rows="3" maxlength="2000"
                          placeholder="Qualité, variété, conditionnement..."
                          class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition resize-none">{{ old('description') }}</textarea>
            </div>

            {{-- Quantité --}}
            <div x-show="stockId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Quantité à mettre en vente <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input type="number" name="quantite_disponible" step="0.01" min="0.01" required
                           x-model="quantite"
                           :max="stockVendable"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 pr-24 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-lg font-bold">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-500 font-bold" x-text="uniteNom"></span>
                </div>
                <p class="text-xs text-stone-600 mt-2">
                    Maximum vendable : <strong x-text="stockVendable"></strong> <span x-text="uniteNom"></span>
                </p>
                <p class="text-xs font-bold text-red-600 mt-2" x-show="quantiteDepasse" x-transition>
                    ⚠️ Vous ne pouvez pas vendre plus que votre stock vendable.
                </p>
            </div>

            {{-- Prix --}}
            <div x-show="stockId" x-transition x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Prix de détail (FCFA) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="prix_detail" step="1" min="0" required
                           x-model="prixDetail"
                           placeholder="Ex. 5000"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                    <p class="text-xs text-stone-500 mt-1.5" x-show="prixSuggere > 0" x-transition>
                        💡 Prix suggéré (depuis votre stock) : <strong x-text="prixSuggere"></strong> F
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">Prix de gros (FCFA)</label>
                    <input type="number" name="prix_gros" step="1" min="0"
                           value="{{ old('prix_gros') }}"
                           placeholder="Optionnel"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                </div>
            </div>

            <div x-show="stockId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Seuil de gros (quantité minimum)</label>
                <input type="number" name="seuil_gros" step="0.01" min="0"
                       value="{{ old('seuil_gros') }}"
                       placeholder="Ex. 5 (prix de gros dès 5 unités)"
                       class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
            </div>

            {{-- Localisation --}}
            <div x-show="stockId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Localisation</label>
                <input type="text" name="localisation" maxlength="255"
                       value="{{ old('localisation') }}"
                       placeholder="Ex. Savalou, quartier Agbado"
                       class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
            </div>

            {{-- Conditions --}}
            <div x-show="stockId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Conditions de retrait / livraison</label>
                <textarea name="conditions_retrait" rows="2" maxlength="1000"
                          placeholder="Ex. Retrait sur place, livraison possible"
                          class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition resize-none">{{ old('conditions_retrait') }}</textarea>
            </div>

            {{-- Info --}}
            <div x-show="stockId" x-transition x-cloak
                 class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-blue-900">
                    Votre stock sera <strong>réservé automatiquement</strong> dès la publication.
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('offres.index') }}"
                   class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                    Annuler
                </a>
                <button type="submit" :disabled="!stockId || quantiteDepasse"
                        class="px-6 py-3 bg-green-800 hover:bg-green-900 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                    <x-icon name="tag" size="5" />
                    Publier sur le marché
                </button>
            </div>
        </form>
    </div>

    <script>
        function offreForm() {
            const stocks = @json($stocksJson);

            return {
                stockId: null,
                uniteNom: '',
                stockVendable: 0,
                quantite: null,
                prixDetail: {{ old('prix_detail', 0) }},
                prixSuggere: 0,

                selectionnerStock(id) {
                    const s = stocks.find(x => x.id == id);
                    if (s) {
                        this.uniteNom = s.unite;
                        this.stockVendable = s.vendable;
                        this.prixSuggere = s.prix_suggere;

                        if (!this.prixDetail || this.prixDetail == 0) {
                            this.prixDetail = s.prix_suggere > 0 ? s.prix_suggere : '';
                        }
                    }
                },

                get quantiteDepasse() {
                    const q = parseFloat(this.quantite) || 0;
                    return q > this.stockVendable;
                }
            }
        }
    </script>
</x-app-layout>