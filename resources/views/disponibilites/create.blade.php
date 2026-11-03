<x-app-layout title="Proposer un produit">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('disponibilites.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour à mes propositions
        </a>

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center shadow-xl shadow-green-500/20">
                <x-icon name="send" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Proposer mon produit</h1>
            <p class="text-stone-500">Choisissez un stock et proposez-le à une unité</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl mb-6 text-sm">
                <div class="flex items-start gap-2 mb-1">
                    <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                    <p class="font-bold">Veuillez corriger :</p>
                </div>
                <ul class="list-disc list-inside space-y-0.5 mt-2 pl-7">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Cas : pas de besoins actifs --}}
        @if($besoinsActifs->isEmpty() && !$besoinPre)
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-12 text-center">
                <x-icon name="search-x" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-900 mb-2">Aucun besoin actif</p>
                <p class="text-sm text-stone-500 mb-6">
                    Aucune unité ne recherche vos produits pour l'instant.
                </p>
                <a href="{{ route('stocks.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                    <x-icon name="warehouse" size="4" />
                    Voir mon stock
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('disponibilites.store') }}"
                  class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6 sm:p-8 space-y-6"
                  x-data="dispoForm()">

                @csrf

                {{-- Choix du stock --}}
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-3">
                        Quel produit proposez-vous ? <span class="text-red-500">*</span>
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($mesStocks as $stock)
                            <label class="cursor-pointer border-2 rounded-2xl p-4 transition relative overflow-hidden"
                                   :class="stockId == {{ $stock->id }} ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300 hover:bg-stone-50'">
                                <input type="radio" name="stock_id" value="{{ $stock->id }}"
                                       x-model="stockId"
                                       @change="selectionnerStock({{ $stock->id }})"
                                       class="sr-only" required>

                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 rounded-xl flex items-center justify-center transition"
                                             :class="stockId == {{ $stock->id }} ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                                            <x-icon name="sprout" size="5" />
                                        </div>
                                        <div>
                                            <p class="font-bold text-stone-900">{{ $stock->produit->nom }}</p>
                                            <p class="text-xs text-stone-500">{{ $stock->uniteMesure->nom }}</p>
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
                                    @if($stock->poids_unitaire_kg && !$stock->uniteMesure->est_reference)
                                        <p class="text-[10px] text-stone-400">
                                            ≈ {{ number_format($stock->quantite_vendable_kg, 0, ',', ' ') }} kg
                                        </p>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Besoin à couvrir --}}
                <div x-show="stockId" x-transition>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        À quel besoin répondez-vous ?
                        <span class="text-stone-400 text-xs font-normal">(optionnel)</span>
                    </label>
                    <select name="besoin_id" x-model="besoinId"
                            class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                        <option value="">— Déclaration libre (visible par toutes les unités) —</option>
                        @foreach($besoinsActifs as $besoin)
                            <option value="{{ $besoin->id }}" @selected(old('besoin_id', $besoinPre?->id) == $besoin->id)>
                                {{ $besoin->produit->nom }} · {{ $besoin->unite->utilisateur->nom }} ·
                                {{ number_format($besoin->restant_kg, 0, ',', ' ') }} kg restants ·
                                échéance {{ $besoin->delai->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Quantité --}}
                <div x-show="stockId" x-transition>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Quantité proposée <span class="text-red-500">*</span>
                    </label>

                    {{-- Mode de saisie --}}
                    <div class="flex gap-2 mb-3">
                        <button type="button" @click="mode = 'unite'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold transition border-2"
                                :class="mode === 'unite' ? 'border-green-600 bg-green-50 text-green-800' : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                            <span x-text="uniteNom || 'Unité'"></span>
                        </button>
                        <button type="button" @click="mode = 'kg'"
                                class="flex-1 px-4 py-2 rounded-xl text-sm font-bold transition border-2"
                                :class="mode === 'kg' ? 'border-green-600 bg-green-50 text-green-800' : 'border-stone-200 text-stone-600 hover:bg-stone-50'">
                            kg
                        </button>
                    </div>

                    <input type="hidden" name="mode_saisie" :value="mode">

                    <div class="relative">
                        <input type="number" name="quantite" step="0.01" min="0.01" required
                               x-model="quantite"
                               placeholder="0"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-4 pr-24 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-xl font-black">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-500 font-bold"
                              x-text="mode === 'kg' ? 'kg' : (uniteNom || 'unité')"></span>
                    </div>

                    {{-- Aperçu conversion --}}
                    <p class="text-xs text-stone-500 mt-2" x-show="mode === 'unite' && poidsUnitaire" x-transition>
                        ≈ <span class="font-bold" x-text="calculKgEnUnite()"></span> kg
                    </p>
                    <p class="text-xs text-stone-500 mt-2" x-show="mode === 'kg' && poidsUnitaire" x-transition>
                        ≈ <span class="font-bold" x-text="calculUniteEnKg()"></span> <span x-text="uniteNom"></span>
                    </p>

                    {{-- Limite maximale --}}
                    <p class="text-xs font-medium mt-2" x-show="stockId" x-transition>
                        <span x-show="!besoinId" class="text-stone-600">
                            Maximum : <strong x-text="stockVendableLabel"></strong> (stock disponible)
                        </span>
                        <span x-show="besoinId" class="text-amber-700">
                            Maximum autorisé : <strong x-text="limiteMaxLabel"></strong>
                            <span x-show="besoinSelectionne" class="text-stone-400">
                                · Restant du besoin : <strong class="text-amber-700" x-text="besoinSelectionne ? besoinSelectionne.restant_kg.toLocaleString('fr-FR') + ' kg' : '—'"></strong>
                            </span>
                        </span>
                    </p>

                    {{-- Alerte dépassement --}}
                    <p class="text-xs font-bold text-red-600 mt-2 flex items-center gap-1"
                       x-show="quantiteDepasseLimite" x-transition>
                        <x-icon name="alert-triangle" size="3.5" />
                        <span x-text="messageDepassement"></span>
                    </p>
                </div>

                {{-- Prix B2B --}}
<div x-show="stockId" x-transition x-cloak>
    <label class="block text-sm font-bold text-stone-800 mb-2">
        Prix B2B proposé (FCFA)
        <span class="text-xs font-normal text-amber-700">pour l'unité</span>
    </label>
    <input type="number" name="prix_b2b" step="1" min="0"
           value="{{ old('prix_b2b') }}"
           placeholder="Ex. 4000"
           class="w-full rounded-2xl border-2 border-amber-200 bg-amber-50/30 px-4 py-3.5 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition">
    <p class="text-xs text-stone-500 mt-1">
        Si laissé vide, le prix B2B de votre stock sera utilisé
    </p>
</div>

                {{-- Date --}}
                <div x-show="stockId" x-transition>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Date de disponibilité <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_disponibilite" required
                           value="{{ old('date_disponibilite', now()->format('Y-m-d')) }}"
                           min="{{ now()->format('Y-m-d') }}"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                </div>

                {{-- Info --}}
                <div x-show="stockId" x-transition
                     class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4">
                    <div class="flex items-start gap-3">
                        <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                        <p class="text-sm text-blue-900">
                            Votre stock sera <strong>réservé automatiquement</strong> dès la validation.
                            Il sera définitivement déduit quand l'unité confirmera la réception.
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                    <a href="{{ route('disponibilites.index') }}"
                       class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                        Annuler
                    </a>
                    <button type="submit"
                            :disabled="!stockId || quantiteDepasseLimite"
                            class="px-6 py-3 bg-green-800 hover:bg-green-900 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                        <x-icon name="send" size="5" />
                        Envoyer ma proposition
                    </button>
                </div>
            </form>
        @endif
    </div>

    <script>
        function dispoForm() {
            const stocks = @json($stocksJson);
            const besoins = @json($besoinsJson);

            return {
                stockId: {{ $stockIdInitial ?? 'null' }},
                besoinId: {{ old('besoin_id', $besoinPre?->id ?? 'null') }},
                uniteNom: '',
                poidsUnitaire: 0,
                estReference: false,
                mode: 'unite',
                quantite: {{ old('quantite') ? old('quantite') : 'null' }},
                stockVendableUnite: 0,
                stockVendableKg: 0,

                init() {
                    if (this.stockId) {
                        this.selectionnerStock(this.stockId);
                    }
                },

                selectionnerStock(id) {
                    const s = stocks.find(x => x.id == id);
                    if (s) {
                        this.uniteNom = s.unite;
                        this.poidsUnitaire = s.poids_unitaire;
                        this.estReference = s.est_reference;
                        this.stockVendableUnite = s.vendable;
                        this.stockVendableKg = s.est_reference
                            ? s.vendable
                            : (s.poids_unitaire ? s.vendable * s.poids_unitaire : s.vendable);
                        if (s.est_reference) this.mode = 'kg';
                    }
                },

                get besoinSelectionne() {
                    if (!this.besoinId) return null;
                    return besoins.find(b => b.id == this.besoinId);
                },

                get limiteMaxKg() {
                    if (!this.stockId) return 0;

                    // Si pas de besoin → limité par le stock
                    if (!this.besoinId || !this.besoinSelectionne) {
                        return this.stockVendableKg;
                    }

                    // Sinon → minimum entre stock et restant du besoin
                    return Math.min(this.besoinSelectionne.restant_kg, this.stockVendableKg);
                },

                get stockVendableLabel() {
                    if (this.estReference) return Math.round(this.stockVendableKg) + ' kg';
                    const u = Math.round(this.stockVendableUnite);
                    return u + ' ' + this.uniteNom + (u > 1 ? 's' : '');
                },

                get limiteMaxLabel() {
                    const limit = this.limiteMaxKg;
                    if (!isFinite(limit) || limit <= 0) return '—';

                    if (this.mode === 'kg') {
                        return Math.round(limit) + ' kg';
                    }

                    if (this.estReference) return Math.round(limit) + ' kg';
                    if (!this.poidsUnitaire) return '—';

                    const unites = limit / this.poidsUnitaire;
                    return unites.toFixed(2) + ' ' + this.uniteNom + (unites > 1 ? 's' : '');
                },

                get quantiteDepasseLimite() {
                    const q = parseFloat(this.quantite) || 0;
                    if (!q || !this.stockId) return false;

                    let qKg;

                    if (this.mode === 'kg') {
                        qKg = q;
                    } else {
                        if (this.estReference) {
                            qKg = q;
                        } else if (this.poidsUnitaire) {
                            qKg = q * this.poidsUnitaire;
                        } else {
                            return false;
                        }
                    }

                    return qKg > this.limiteMaxKg;
                },

                get messageDepassement() {
                    if (!this.besoinId) {
                        return 'Dépasse votre stock vendable (' + this.stockVendableLabel + ')';
                    }
                    return 'Dépasse le restant du besoin. Maximum : ' + this.limiteMaxLabel;
                },

                calculKgEnUnite() {
                    const q = parseFloat(this.quantite) || 0;
                    const p = parseFloat(this.poidsUnitaire) || 0;
                    if (this.estReference) return q;
                    if (!p) return '—';
                    return Math.round(q * p);
                },

                calculUniteEnKg() {
                    const q = parseFloat(this.quantite) || 0;
                    const p = parseFloat(this.poidsUnitaire) || 0;
                    if (this.estReference) return q;
                    if (!p) return '—';
                    return (q / p).toFixed(2);
                }
            }
        }
    </script>
</x-app-layout>