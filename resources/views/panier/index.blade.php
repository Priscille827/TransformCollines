<x-app-layout title="Mon panier">
    <div class="max-w-4xl mx-auto">

        <h1 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
            <x-icon name="shopping-cart" size="6" class="text-green-700" />
            Mon panier
        </h1>

        @if($items->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-16 text-center">
                <x-icon name="shopping-cart" size="12" class="mx-auto mb-3 text-gray-300" />
                <p class="font-medium text-gray-700 mb-1">Votre panier est vide</p>
                <p class="text-sm text-gray-500 mb-5">Découvrez les produits du marché.</p>
                <a href="{{ route('marketplace.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                    <x-icon name="shopping-basket" size="4" />
                    Aller au marché
                </a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="divide-y divide-gray-100">
                    @foreach($items as $item)
                        <div class="p-4 flex items-center gap-4">
                            <div class="w-16 h-16 rounded-lg bg-gradient-to-br from-green-50 to-amber-50 flex items-center justify-center flex-shrink-0">
                                @if($item->offre->photo_principale)
                                    <img src="{{ Storage::url($item->offre->photo_principale) }}" class="w-full h-full object-cover rounded-lg">
                                @else
                                    <x-icon name="package" size="6" class="text-green-300" />
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium text-gray-900 line-clamp-1">{{ $item->offre->titre }}</p>
                                <p class="text-xs text-gray-500">{{ $item->offre->nomVendeur() }}</p>
                                <p class="text-sm font-medium text-green-700 mt-1">
                                    {{ number_format($item->offre->prixPourQuantite((float)$item->quantite), 0, ',', ' ') }} F
                                    <span class="text-xs font-normal text-gray-500">/ {{ $item->offre->uniteMesure->symbole }}</span>
                                </p>
                            </div>
                            <form method="POST" action="{{ route('panier.modifier', $item) }}" class="flex items-center gap-2">
                                @csrf @method('PATCH')
                                <input type="number" name="quantite" value="{{ $item->quantite }}" step="0.01" min="0.01"
                                       class="w-20 border border-gray-300 rounded px-2 py-1 text-sm text-center focus:ring-2 focus:ring-green-500 outline-none">
                                <button type="submit" class="text-xs text-green-700 hover:underline font-medium">OK</button>
                            </form>
                            <p class="font-bold text-gray-900 whitespace-nowrap">
                                {{ number_format($item->sousTotal(), 0, ',', ' ') }} F
                            </p>
                            <form method="POST" action="{{ route('panier.retirer', $item) }}">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700 p-1.5 rounded hover:bg-red-50">
                                    <x-icon name="trash-2" size="4" />
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <div class="flex items-center justify-between text-lg">
                    <span class="font-medium text-gray-700">Total</span>
                    <span class="font-bold text-green-700">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('marketplace.index') }}"
                   class="px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Continuer mes achats
                </a>
                <a href="{{ route('commandes.checkout') }}"
   class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition flex items-center gap-2">
    <x-icon name="check-circle" size="5" />
    Valider la commande
</a>
            </div>
        @endif
    </div>
</x-app-layout>