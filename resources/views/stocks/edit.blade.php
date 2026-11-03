<x-app-layout title="Gérer le stock">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('stocks.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour à mon stock
        </a>

        {{-- Header --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center">
                    <x-icon name="sprout" size="7" />
                </div>
                <div>
                    <h1 class="text-2xl font-black text-stone-900">{{ $stock->produit->nom }}</h1>
                    <p class="text-sm text-stone-500">
                        {{ $stock->uniteMesure->nom }}
                        @if($stock->poids_unitaire_kg && !$stock->uniteMesure->est_reference)
                            · 1 {{ $stock->uniteMesure->nom }} = {{ number_format($stock->poids_unitaire_kg, 0, ',', ' ') }} kg
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="bg-green-50 rounded-xl p-3 text-center">
                    <p class="text-[10px] text-green-700 font-bold uppercase tracking-wider mb-1">Vendable</p>
                    <p class="text-xl font-black text-green-800">{{ number_format($stock->quantite_vendable, 0, ',', ' ') }}</p>
                </div>
                <div class="bg-amber-50 rounded-xl p-3 text-center">
                    <p class="text-[10px] text-amber-700 font-bold uppercase tracking-wider mb-1">Réservé</p>
                    <p class="text-xl font-black text-amber-800">{{ number_format($stock->quantite_reservee, 0, ',', ' ') }}</p>
                </div>
                <div class="bg-stone-50 rounded-xl p-3 text-center">
                    <p class="text-[10px] text-stone-600 font-bold uppercase tracking-wider mb-1">Total</p>
                    <p class="text-xl font-black text-stone-800">{{ number_format($stock->quantite_disponible, 0, ',', ' ') }}</p>
                </div>
            </div>
        </div>

        {{-- Nouveau mouvement --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6"
             x-data="{ type: 'entree' }">
            <h2 class="font-bold text-stone-900 mb-4 flex items-center gap-2">
                <x-icon name="repeat" size="5" class="text-green-700" />
                Nouveau mouvement
            </h2>

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-3 rounded-r-xl mb-4 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('stocks.update', $stock) }}" class="space-y-4">
                @csrf @method('PATCH')

                <div class="grid grid-cols-3 gap-2">
                    <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                           :class="type === 'entree' ? 'border-green-600 bg-green-50' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="type" value="entree" x-model="type" class="sr-only">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                 :class="type === 'entree' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-400'">
                                <x-icon name="plus-circle" size="5" />
                            </div>
                            <span class="text-xs font-bold" :class="type === 'entree' ? 'text-green-800' : 'text-stone-600'">Entrée</span>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                           :class="type === 'sortie' ? 'border-red-600 bg-red-50' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="type" value="sortie" x-model="type" class="sr-only">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                 :class="type === 'sortie' ? 'bg-red-600 text-white' : 'bg-stone-100 text-stone-400'">
                                <x-icon name="minus-circle" size="5" />
                            </div>
                            <span class="text-xs font-bold" :class="type === 'sortie' ? 'text-red-800' : 'text-stone-600'">Sortie</span>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                           :class="type === 'ajustement' ? 'border-amber-600 bg-amber-50' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="type" value="ajustement" x-model="type" class="sr-only">
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                 :class="type === 'ajustement' ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-400'">
                                <x-icon name="settings-2" size="5" />
                            </div>
                            <span class="text-xs font-bold" :class="type === 'ajustement' ? 'text-amber-800' : 'text-stone-600'">Ajustement</span>
                        </div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Quantité <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="quantite" step="0.01" min="0.01" required
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 pr-24 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-lg font-bold">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-500 font-bold">
                            {{ $stock->uniteMesure->symbole ?? $stock->uniteMesure->nom }}
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 mt-2" x-show="type === 'entree'">
                        Ajoute la quantité au stock.
                    </p>
                    <p class="text-xs text-stone-500 mt-2" x-show="type === 'sortie'">
                        Retire du stock. Maximum vendable : {{ number_format($stock->quantite_vendable, 0, ',', ' ') }} {{ $stock->uniteMesure->nom }}.
                    </p>
                    <p class="text-xs text-stone-500 mt-2" x-show="type === 'ajustement'">
                        Définit la nouvelle quantité totale.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">Motif</label>
                    <input type="text" name="motif" maxlength="255"
                           placeholder="Ex. Récolte, vente, perte..."
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                </div>

                <button type="submit"
                        class="w-full bg-green-800 hover:bg-green-900 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-green-800/20 flex items-center justify-center gap-2">
                    <x-icon name="check-circle-2" size="5" />
                    Enregistrer le mouvement
                </button>
            </form>
        </div>

        {{-- Historique --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-bold text-stone-900 flex items-center gap-2">
                    <x-icon name="history" size="5" class="text-stone-500" />
                    Historique des mouvements
                </h2>
                <form method="POST" action="{{ route('stocks.destroy', $stock) }}"
                      onsubmit="return confirm('Supprimer ce stock ? Toutes les données seront perdues.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-bold flex items-center gap-1">
                        <x-icon name="trash-2" size="3.5" />
                        Supprimer
                    </button>
                </form>
            </div>

            @if($mouvements->isEmpty())
                <div class="px-6 py-12 text-center text-stone-400">
                    <x-icon name="inbox" size="10" class="mx-auto mb-2 text-stone-300" />
                    <p class="text-sm">Aucun mouvement enregistré</p>
                </div>
            @else
                <div class="divide-y divide-stone-100">
                    @foreach($mouvements as $m)
                        @php
                            $isPositif = $m->quantite >= 0;
                            $couleur = $isPositif
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700';

                            // Chercher la commande liée si applicable
                            $commandeLiee = null;
                            if ($m->reference_type === \App\Models\Commande::class && $m->reference_id) {
                                $commandeLiee = \App\Models\Commande::find($m->reference_id);
                            }

                            // Chercher le besoin lié si applicable
                            $besoinLie = null;
                            if ($m->reference_type === \App\Models\Besoin::class && $m->reference_id) {
                                $besoinLie = \App\Models\Besoin::with('unite.utilisateur')->find($m->reference_id);
                            }
                        @endphp
                        <div class="px-6 py-4 flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-xl {{ $couleur }} flex items-center justify-center flex-shrink-0">
                                    @if($isPositif)
                                        <x-icon name="arrow-down-circle" size="5" />
                                    @else
                                        <x-icon name="arrow-up-circle" size="5" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 text-sm">
                                        {{ $isPositif ? '+' : '' }}{{ number_format($m->quantite, 0, ',', ' ') }}
                                        {{ $stock->uniteMesure->nom }}{{ abs($m->quantite) > 1 ? 's' : '' }}
                                    </p>
                                    <p class="text-xs text-stone-500">
                                        {{ ucfirst($m->type) }}
                                        @if($m->motif) · {{ $m->motif }} @endif
                                    </p>

                                    {{-- Lien vers la commande --}}
                                    @if($commandeLiee)
                                        <a href="{{ route('commandes.show', $commandeLiee) }}"
                                           class="inline-flex items-center gap-1 text-xs text-green-700 hover:text-green-800 font-bold mt-1">
                                            <x-icon name="external-link" size="3" />
                                            Voir la commande {{ $commandeLiee->reference }}
                                        </a>
                                    @endif

                                    {{-- Lien vers le besoin --}}
                                    @if($besoinLie)
                                        <a href="{{ route('besoins.show', $besoinLie) }}"
                                           class="inline-flex items-center gap-1 text-xs text-amber-700 hover:text-amber-800 font-bold mt-1">
                                            <x-icon name="external-link" size="3" />
                                            Voir le besoin #{{ $besoinLie->id }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs text-stone-400 whitespace-nowrap">
                                {{ $m->created_at ? $m->created_at->format('d/m/Y H:i') : '—' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>