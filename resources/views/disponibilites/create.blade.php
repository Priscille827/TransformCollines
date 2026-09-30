<x-app-layout title="Déclarer une disponibilité">
    <div class="max-w-3xl mx-auto">

        <div class="mb-6">
            <a href="{{ route('disponibilites.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                <x-icon name="arrow-left" size="4" />
                Retour à mes disponibilités
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-3">Déclarer une disponibilité</h1>
            <p class="text-gray-500 text-sm mt-1">Annoncez vos produits : les unités proches seront notifiées.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg text-sm flex items-start gap-2">
                <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('disponibilites.store') }}"
              class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6"
              x-data="{ associer: {{ $besoinPre ? 'true' : 'false' }} }">
            @csrf

            {{-- Produit --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Produit <span class="text-red-500">*</span>
                </label>
                <select name="produit_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <option value="">— Sélectionnez —</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}"
                                @selected(old('produit_id', $besoinPre?->produit_id) == $p->id)>
                            {{ $p->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Quantité + Date --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Quantité disponible (kg) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantite" step="0.01" min="1"
                           value="{{ old('quantite') }}" required placeholder="Ex. 500"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Date de disponibilité <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="date_disponibilite"
                           value="{{ old('date_disponibilite', now()->format('Y-m-d')) }}" required
                           min="{{ now()->format('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>
            </div>

            {{-- Choix : associer à un besoin ou non --}}
            <div>
                <label class="flex items-center gap-2 cursor-pointer mb-3">
                    <input type="checkbox" x-model="associer" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-gray-700">Associer à un besoin précis</span>
                </label>

                <div x-show="associer" x-transition>
                    @if($besoinsActifs->isEmpty())
                        <div class="bg-amber-50 border border-amber-100 text-amber-800 p-4 rounded-lg text-sm flex items-start gap-2">
                            <x-icon name="alert-triangle" size="5" class="flex-shrink-0 mt-0.5" />
                            <span>Aucun besoin actif correspondant à vos produits pour l'instant. Votre déclaration sera <strong>libre</strong> et visible par toutes les unités proches.</span>
                        </div>
                    @else
                        <select name="besoin_id"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                            <option value="">— Choisir un besoin —</option>
                            @foreach($besoinsActifs as $b)
                                <option value="{{ $b->id }}"
                                        @selected(old('besoin_id', $besoinPre?->id) == $b->id)>
                                    {{ $b->produit->nom }} —
                                    {{ number_format($b->quantite_recherchee, 0, ',', ' ') }} kg —
                                    {{ $b->unite->utilisateur->nom }}
                                    (échéance {{ $b->delai->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5">
                            Votre quantité viendra s'ajouter au taux de couverture de ce besoin.
                        </p>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-900">
                    <p class="font-medium">Ce qui se passe après :</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-800">
                        <li>Si associée à un besoin, le taux de couverture se met à jour immédiatement</li>
                        <li>Sinon, votre dispo apparaît comme « libre » aux unités proches</li>
                        <li>Elle reste visible <strong>15 jours</strong>, puis expire automatiquement</li>
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('disponibilites.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Annuler
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition flex items-center gap-2">
                    <x-icon name="send" size="4" />
                    Déclarer
                </button>
            </div>
        </form>
    </div>
</x-app-layout>