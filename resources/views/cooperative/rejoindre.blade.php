<x-app-layout title="Rejoindre une coopérative">
    <div class="max-w-2xl mx-auto">

        <a href="{{ route('cooperative.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour
        </a>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-1">Rejoindre la coopérative virtuelle</h1>
            <p class="text-sm text-gray-500 mb-6">
                Indiquez la quantité que vous apportez à ce pool collectif.
            </p>

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Récap besoin --}}
            <div class="bg-gray-50 rounded-lg p-4 mb-6 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Produit</span>
                    <span class="font-medium">{{ $besoin->produit->nom }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Unité</span>
                    <span class="font-medium">{{ $besoin->unite->utilisateur->nom }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Quantité recherchée</span>
                    <span class="font-medium">{{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Déjà couvert</span>
                    <span class="font-medium">{{ $besoin->taux_couverture }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Échéance</span>
                    <span class="font-medium">{{ $besoin->delai->format('d/m/Y') }}</span>
                </div>
            </div>

            <form method="POST" action="{{ route('cooperative.rejoindre', $besoin) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Ma contribution (kg) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantite" step="0.01" min="1"
                           value="{{ old('quantite') }}" required
                           placeholder="Ex. 500"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1.5">
                        Même une petite quantité compte. La plateforme additionne toutes les contributions.
                    </p>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-900 flex items-start gap-2">
                    <x-icon name="info" size="4" class="flex-shrink-0 mt-0.5" />
                    <span>
                        Votre contribution sera visible par l'unité destinataire. Une fois le pool complet,
                        elle pourra valider la collecte groupée.
                    </span>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <a href="{{ route('cooperative.index') }}"
                       class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Annuler
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition flex items-center gap-2">
                        <x-icon name="users" size="4" />
                        Rejoindre le pool
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>