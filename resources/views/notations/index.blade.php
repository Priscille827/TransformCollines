<x-app-layout title="Notations">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $role === 'unite' ? 'Noter la qualité des lots' : 'Noter les paiements' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $role === 'unite'
                    ? 'Évaluez la qualité des lots reçus'
                    : 'Indiquez si le paiement a été à temps ou en retard' }}
            </p>
        </div>

        @if($dispos->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <x-icon name="star" size="12" class="mx-auto mb-3 text-gray-300" />
                <p class="text-sm text-gray-500">Aucune livraison à noter</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($dispos as $d)
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $d->produit->nom }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    @if($role === 'unite')
                                        {{ $d->producteur->utilisateur->nom }}
                                    @else
                                        {{ $d->besoin->unite->utilisateur->nom }}
                                    @endif
                                    · {{ number_format($d->quantite_livree ?? $d->quantite, 0, ',', ' ') }} kg livrés
                                </div>
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ $d->updated_at->format('d/m/Y') }}
                            </div>
                        </div>

                        @if($role === 'unite')
                            <form method="POST" action="{{ route('notations.qualite', $d) }}" class="flex gap-2">
                                @csrf
                                <button name="note_qualite" value="bon"
                                        class="flex-1 py-2 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-500 transition text-sm font-medium">
                                    Bon
                                </button>
                                <button name="note_qualite" value="moyen"
                                        class="flex-1 py-2 border border-gray-200 rounded-lg hover:bg-amber-50 hover:border-amber-500 transition text-sm font-medium">
                                    Moyen
                                </button>
                                <button name="note_qualite" value="faible"
                                        class="flex-1 py-2 border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-500 transition text-sm font-medium">
                                    Faible
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('notations.paiement', $d) }}" class="flex gap-2">
                                @csrf
                                <button name="note_paiement" value="a_temps"
                                        class="flex-1 py-2 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-500 transition text-sm font-medium">
                                    Payé à temps
                                </button>
                                <button name="note_paiement" value="en_retard"
                                        class="flex-1 py-2 border border-gray-200 rounded-lg hover:bg-red-50 hover:border-red-500 transition text-sm font-medium">
                                    Payé en retard
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $dispos->links() }}</div>
        @endif
    </div>
</x-app-layout>