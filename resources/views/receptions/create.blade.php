<x-app-layout title="Confirmer la réception">
    <div class="max-w-2xl mx-auto">
        <a href="{{ route('receptions.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour aux réceptions
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-6">Confirmer la réception</h1>

            {{-- Récap livraison --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Producteur</span>
                    <span class="font-medium">{{ $disponibilite->producteur->utilisateur->nom }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Produit</span>
                    <span class="font-medium">{{ $disponibilite->produit->nom }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Quantité promise</span>
                    <span class="font-medium">{{ number_format($disponibilite->quantite, 0, ',', ' ') }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Date annoncée</span>
                    <span class="font-medium">{{ $disponibilite->date_disponibilite->format('d/m/Y') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('receptions.store', $disponibilite) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Quantité réellement reçue (kg) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantite_livree" step="0.01" min="0"
                           value="{{ old('quantite_livree', $disponibilite->quantite) }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1.5">
                        La quantité peut différer de la quantité promise (écart enregistré dans l'historique).
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-900 flex items-start gap-2">
                    <x-icon name="info" size="4" class="flex-shrink-0 mt-0.5" />
                    <span>Cette confirmation alimente l'historique de fiabilité du producteur.</span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('receptions.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition flex items-center gap-2">
                        <x-icon name="check" size="4" />
                        Confirmer la réception
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>