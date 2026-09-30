<x-app-layout title="Réceptions">
    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Réceptions à confirmer</h1>
            <p class="text-gray-500 text-sm mt-1">Livraisons annoncées par les producteurs</p>
        </div>

        @if($dispos->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <x-icon name="package-check" size="12" class="mx-auto mb-3 text-gray-300" />
                <p class="font-medium text-gray-700 mb-1">Aucune livraison en attente</p>
                <p class="text-sm text-gray-500">Toutes les réceptions ont été traitées.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Producteur</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Produit</th>
                            <th class="text-right px-6 py-3 font-medium text-gray-600">Quantité promise</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">Date annoncée</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dispos as $d)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $d->producteur->utilisateur->nom }}</div>
                                    <div class="text-xs text-gray-500">{{ $d->producteur->utilisateur->commune->nom }}</div>
                                </td>
                                <td class="px-6 py-4">{{ $d->produit->nom }}</td>
                                <td class="px-6 py-4 text-right font-semibold">{{ number_format($d->quantite, 0, ',', ' ') }} kg</td>
                                <td class="px-6 py-4 text-gray-600">{{ $d->date_disponibilite->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('receptions.create', $d) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-medium transition">
                                        <x-icon name="check" size="3" />
                                        Confirmer
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $dispos->links() }}</div>
        @endif
    </div>
</x-app-layout>