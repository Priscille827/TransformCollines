<x-app-layout title="Mes ventes">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Mes ventes</h1>
                <p class="text-stone-500">Les produits que vous mettez en vente sur le marché</p>
            </div>
            <a href="{{ route('offres.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-lg shadow-green-800/20 transition">
                <x-icon name="plus" size="4" />
                Mettre en vente
            </a>
        </div>

        @if($offres->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center">
                    <x-icon name="tag" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune vente publiée</p>
                <p class="text-sm text-stone-500 mb-6 max-w-md mx-auto">
                    Mettez en vente vos produits pour qu'ils soient visibles par la population et les commerçants.
                </p>
                <a href="{{ route('offres.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                    <x-icon name="plus" size="4" />
                    Mettre en vente
                </a>
            </div>
        @else
            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total</p>
                    <p class="text-3xl font-black text-stone-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-5">
                    <p class="text-xs text-green-700 font-bold uppercase tracking-wider">Actives</p>
                    <p class="text-3xl font-black text-green-700 mt-1">{{ $stats['actives'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
                    <p class="text-xs text-stone-600 font-bold uppercase tracking-wider">Épuisées</p>
                    <p class="text-3xl font-black text-stone-600 mt-1">{{ $stats['epuisees'] }}</p>
                </div>
            </div>

            {{-- Liste --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($offres as $offre)
                    @php
                        $badge = match($offre->statut) {
                            'active'   => ['bg-green-100 text-green-800', 'En vente'],
                            'brouillon'=> ['bg-stone-100 text-stone-600', 'Brouillon'],
                            'epuisee'  => ['bg-amber-100 text-amber-800', 'Épuisée'],
                            'archivee' => ['bg-stone-100 text-stone-500', 'Archivée'],
                            default    => ['bg-stone-100 text-stone-700', $offre->statut],
                        };
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden hover:shadow-md transition group">
                        {{-- Image --}}
                        <div class="aspect-video bg-gradient-to-br from-green-50 to-lime-50 flex items-center justify-center relative">
                            @if($offre->photo_principale)
                                <img src="{{ Storage::url($offre->photo_principale) }}" alt="{{ $offre->titre }}" class="w-full h-full object-cover">
                            @else
                                <x-icon name="package" size="12" class="text-green-300" />
                            @endif

                            <span class="absolute top-2 right-2 px-2.5 py-1 rounded-full text-xs font-bold {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-stone-900 line-clamp-1 mb-1">{{ $offre->titre }}</h3>
                            <p class="text-xs text-stone-500 mb-3">
                                {{ $offre->produit->nom }} · {{ number_format($offre->quantite_disponible, 0, ',', ' ') }} {{ $offre->uniteMesure->nom }}{{ $offre->quantite_disponible > 1 ? 's' : '' }}
                            </p>

                            <div class="flex items-baseline justify-between mb-3">
                                <p class="text-lg font-black text-green-700">
                                    {{ number_format($offre->prix_detail, 0, ',', ' ') }} F
                                    <span class="text-xs font-bold text-stone-400">/ {{ $offre->uniteMesure->nom }}</span>
                                </p>
                                <p class="text-xs text-stone-400 flex items-center gap-1">
                                    <x-icon name="eye" size="3.5" />
                                    {{ $offre->vues }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2 pt-3 border-t border-stone-100">
                                <a href="{{ route('marketplace.show', $offre) }}"
                                   class="flex-1 text-center text-xs font-bold py-2 text-stone-700 hover:bg-stone-100 rounded-lg transition">
                                    Voir
                                </a>
                                <a href="{{ route('offres.edit', $offre) }}"
                                   class="flex-1 text-center text-xs font-bold py-2 text-green-700 hover:bg-green-50 rounded-lg transition">
                                    Modifier
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $offres->links() }}</div>
        @endif
    </div>
</x-app-layout>