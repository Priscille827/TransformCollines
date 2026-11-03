<x-app-layout title="Ajouter du stock">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('stocks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour à mon stock
        </a>

        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center shadow-xl shadow-green-500/20">
                <x-icon name="plus-circle" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Ajouter du stock</h1>
            <p class="text-stone-500">Enregistrez un ajout de stock</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl mb-6 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('stocks.store') }}"
              class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6 sm:p-8 space-y-6"
              x-data="stockForm()"
              novalidate>

            @csrf

            {{-- Produit --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Produit <span class="text-red-500">*</span>
                </label>
                <select name="produit_id" required
                        class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                    <option value="">— Choisir un produit —</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>
                            {{ $p->nom }} ({{ ucfirst($p->type_produit) }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Type d'unité --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-3">
                    Comment compter ? <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                           :class="typeUnite === 'mesure' ? 'border-green-600 bg-green-50 shadow-md' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="type_unite" value="mesure" x-model="typeUnite" @change="reinitialiser()" class="sr-only">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition"
                                 :class="typeUnite === 'mesure' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                                <x-icon name="scale" size="6" />
                            </div>
                            <p class="font-bold text-sm" :class="typeUnite === 'mesure' ? 'text-green-800' : 'text-stone-700'">Unité de mesure</p>
                            <p class="text-[10px] text-stone-400 mt-1">kg, g, L, ml</p>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                           :class="typeUnite === 'contenant' ? 'border-amber-600 bg-amber-50 shadow-md' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="type_unite" value="contenant" x-model="typeUnite" @change="reinitialiser()" class="sr-only">
                        <div class="text-center">
                            <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition"
                                 :class="typeUnite === 'contenant' ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-500'">
                                <x-icon name="package" size="6" />
                            </div>
                            <p class="font-bold text-sm" :class="typeUnite === 'contenant' ? 'text-amber-800' : 'text-stone-700'">Contenant</p>
                            <p class="text-[10px] text-stone-400 mt-1">sac, panier, sachet</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Mesures --}}
            <div x-show="typeUnite === 'mesure'" x-transition>
                <label class="block text-sm font-bold text-stone-800 mb-2">Unité de mesure <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($mesures as $u)
                        <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                               :class="uniteId == {{ $u->id }} ? 'border-green-600 bg-green-50' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" name="unite_mesure_id" value="{{ $u->id }}"
                                   x-model="uniteId"
                                   @change="uniteNom = '{{ $u->nom }}'; uniteSymbole = '{{ $u->symbole }}'; estReference = {{ $u->est_reference ? 'true' : 'false' }}"
                                   class="sr-only">
                            <p class="font-bold text-xs">{{ $u->nom }}</p>
                            <p class="text-[10px] text-stone-400">{{ $u->symbole }}</p>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Contenants --}}
            <div x-show="typeUnite === 'contenant'" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Contenant <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($contenants as $u)
                        <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                               :class="uniteId == {{ $u->id }} ? 'border-amber-600 bg-amber-50' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" name="unite_mesure_id" value="{{ $u->id }}"
                                   x-model="uniteId"
                                   @change="uniteNom = '{{ $u->nom }}'; uniteSymbole = '{{ $u->symbole }}'; estReference = false"
                                   class="sr-only">
                            <p class="font-bold text-xs">{{ $u->nom }}</p>
                            <p class="text-[10px] text-stone-400">{{ $u->symbole }}</p>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Poids + unité de référence (contenant) --}}
            <div x-show="typeUnite === 'contenant' && uniteId" x-transition x-cloak
                 class="bg-gradient-to-br from-amber-50 to-amber-100/50 border-2 border-amber-200 rounded-2xl p-5 space-y-4">

                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                        <x-icon name="scale" size="5" />
                    </div>
                    <div>
                        <p class="font-bold text-amber-900 text-sm">Poids du contenant</p>
                        <p class="text-xs text-amber-800 mt-0.5">Combien contient 1 <strong x-text="uniteNom"></strong> ?</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-wrap">
                    <span class="text-sm font-bold text-amber-900">1 <span x-text="uniteNom"></span> =</span>
                    <input type="number" name="poids_unitaire_kg" step="0.001" min="0.001"
                           x-model="poidsUnitaire"
                           :disabled="typeUnite !== 'contenant' || !uniteId"
                           placeholder="500"
                           class="w-32 rounded-xl border-2 border-amber-300 bg-white px-3 py-2 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition font-mono font-bold">

                    <select name="unite_reference_id" x-model="uniteRefId"
                            :disabled="typeUnite !== 'contenant' || !uniteId"
                            class="rounded-xl border-2 border-amber-300 bg-white px-3 py-2 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition font-bold text-sm">
                        @foreach($mesures as $m)
                            <option value="{{ $m->id }}">{{ $m->nom }} ({{ $m->symbole }})</option>
                        @endforeach
                    </select>
                </div>

                <p class="text-xs text-amber-700 flex items-start gap-1.5">
                    <x-icon name="info" size="3.5" class="mt-0.5 flex-shrink-0" />
                    <span>Choisissez l'unité qui correspond : <strong>g</strong> pour un petit sachet, <strong>kg</strong> pour un sac, <strong>L</strong> ou <strong>ml</strong> pour un liquide.</span>
                </p>
            </div>

            {{-- Quantité --}}
            <div x-show="uniteId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Quantité <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="number" name="quantite_disponible" step="0.01" min="0.01"
                           x-model="quantite"
                           placeholder="Ex. 10"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 pr-24 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-lg font-bold">
                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-500 font-bold"
                          x-text="typeUnite === 'contenant' ? uniteNom : (uniteSymbole || 'unité')"></span>
                </div>
            </div>

            {{-- Aperçu --}}
            <div x-show="quantite > 0 && uniteId" x-transition x-cloak
                 class="bg-gradient-to-br from-green-50 to-lime-50 border-2 border-green-200 rounded-2xl p-5">
                <p class="text-xs text-green-700 font-bold uppercase tracking-wider mb-1">Aperçu</p>
                <p class="text-2xl font-black text-green-800" x-text="apercuQuantite()"></p>
            </div>

            {{-- Prix + seuil --}}
            <div x-show="uniteId" x-transition x-cloak class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">Prix détail (FCFA)</label>
                    <input type="number" name="prix_unitaire" step="1" min="0"
                           value="{{ old('prix_unitaire') }}"
                           placeholder="Ex. 5000"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <p class="text-xs text-stone-500 mt-1">Prix pour la population</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Prix B2B (FCFA)
                    </label>
                    <input type="number" name="prix_b2b" step="1" min="0"
                           value="{{ old('prix_b2b') }}"
                           placeholder="Ex. 4000"
                           class="w-full rounded-2xl border-2 border-amber-200 bg-amber-50/30 px-4 py-3 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 outline-none transition">
                    <p class="text-xs text-stone-500 mt-1">Prix spécial pour les unités</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">Seuil d'alerte</label>
                    <input type="number" name="seuil_alerte" step="0.01" min="0"
                           value="{{ old('seuil_alerte') }}"
                           placeholder="Ex. 5"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                </div>
            </div>

            {{-- Motif --}}
            <div x-show="uniteId" x-transition x-cloak>
                <label class="block text-sm font-bold text-stone-800 mb-2">Motif</label>
                <input type="text" name="motif" value="{{ old('motif', 'Ajout de stock') }}" maxlength="255"
                       class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('stocks.index') }}"
                   class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                    Annuler
                </a>
                <button type="submit" :disabled="!uniteId"
                        class="px-6 py-3 bg-green-800 hover:bg-green-900 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                    <x-icon name="check-circle-2" size="5" />
                    Ajouter au stock
                </button>
            </div>
        </form>
    </div>

    <script>
        function stockForm() {
            return {
                typeUnite: 'mesure',
                uniteId: null,
                uniteNom: '',
                uniteSymbole: '',
                estReference: false,
                uniteRefId: {{ $uniteKg?->id ?? 1 }},
                poidsUnitaire: '{{ old('poids_unitaire_kg', '') }}',
                quantite: {{ old('quantite_disponible', 0) }},

                reinitialiser() {
                    this.uniteId = null;
                    this.uniteNom = '';
                    this.uniteSymbole = '';
                    this.estReference = false;
                },

                apercuQuantite() {
                    const q = parseFloat(this.quantite) || 0;
                    if (q <= 0) return '—';

                    const nom = this.typeUnite === 'contenant' ? this.uniteNom : this.uniteSymbole;
                    const pluriel = q > 1 ? 's' : '';

                    if (this.typeUnite === 'contenant' && this.poidsUnitaire > 0) {
                        const total = q * parseFloat(this.poidsUnitaire);
                        return `${q} ${nom}${pluriel} = ${total.toLocaleString('fr-FR')} ${this.getRefLabel()}`;
                    }

                    return `${q} ${nom}${pluriel}`;
                },

                getRefLabel() {
                    const select = document.querySelector('select[name="unite_reference_id"]');
                    if (!select) return '';
                    const opt = select.options[select.selectedIndex];
                    if (!opt) return '';
                    const match = opt.text.match(/\(([^)]+)\)/);
                    return match ? match[1] : opt.text;
                }
            }
        }
    </script>
</x-app-layout>