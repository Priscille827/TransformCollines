<x-app-layout title="Mes propositions">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Mes propositions</h1>
                <p class="text-stone-500">Les produits que vous proposez aux unités</p>
            </div>
            <a href="{{ route('disponibilites.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-lg shadow-green-800/20 transition">
                <x-icon name="plus" size="4" />
                Proposer un produit
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($dispos->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center">
                    <x-icon name="send" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune proposition</p>
                <p class="text-sm text-stone-500 mb-6 max-w-md mx-auto">
                    Proposez vos produits aux unités de transformation qui recherchent vos matières premières.
                </p>
                <a href="{{ route('disponibilites.create') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                    <x-icon name="plus" size="4" />
                    Proposer mon premier produit
                </a>
            </div>
        @else
            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total</p>
                    <p class="text-3xl font-black text-stone-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5">
                    <p class="text-xs text-amber-700 font-bold uppercase tracking-wider">En cours</p>
                    <p class="text-3xl font-black text-amber-700 mt-1">{{ $stats['actives'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-5">
                    <p class="text-xs text-green-700 font-bold uppercase tracking-wider">Confirmées</p>
                    <p class="text-3xl font-black text-green-700 mt-1">{{ $stats['confirmees'] }}</p>
                </div>
            </div>

            {{-- Liste --}}
            <div class="space-y-4">
                @foreach($dispos as $dispo)
                    @php
                        $badge = match($dispo->statut) {
                            'declaree'  => ['bg-blue-100 text-blue-800', 'Libre'],
                            'associee'  => ['bg-amber-100 text-amber-800', 'En attente'],
                            'confirmee' => ['bg-green-100 text-green-800', 'Confirmée'],
                            'expiree'   => ['bg-stone-100 text-stone-600', 'Expirée'],
                            default     => ['bg-stone-100 text-stone-700', $dispo->statut],
                        };

                        // Sécuriser les relations (anciennes lignes sans unité)
                        $uniteNom = $dispo->uniteMesure?->nom
                            ?? ($dispo->quantite_kg_equivalent ? 'kg' : 'unité');
                        $produitNom = $dispo->produit?->nom ?? 'Produit supprimé';
                    @endphp
                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="package" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <p class="font-bold text-stone-900 text-lg">{{ $produitNom }}</p>
                                    <p class="text-sm text-stone-500">
                                        {{ number_format($dispo->quantite, 0, ',', ' ') }}
                                        {{ $uniteNom }}{{ $dispo->quantite > 1 && $uniteNom !== 'kg' ? 's' : '' }}
                                        @if($dispo->quantite_kg_equivalent && $uniteNom !== 'kg')
                                            <span class="text-stone-400">·</span>
                                            {{ number_format($dispo->quantite_kg_equivalent, 0, ',', ' ') }} kg
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                                @if($dispo->statut !== 'confirmee')
                                    <form method="POST" action="{{ route('disponibilites.destroy', $dispo) }}"
                                          onsubmit="return confirm('Supprimer cette proposition ? Le stock sera libéré.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Supprimer">
                                            <x-icon name="trash-2" size="4" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>

                        @if($dispo->besoin && $dispo->besoin->unite && $dispo->besoin->unite->utilisateur)
                            <div class="mt-4 pt-4 border-t border-stone-100">
                                <p class="text-xs text-stone-500 uppercase font-bold tracking-wider mb-2">Besoin associé</p>
                                <div class="flex items-center gap-2 text-sm flex-wrap">
                                    <x-icon name="factory" size="4" class="text-amber-600" />
                                    <span class="font-medium text-stone-700">{{ $dispo->besoin->unite->utilisateur->nom }}</span>
                                    <span class="text-stone-300">·</span>
                                    <span class="text-stone-500">{{ number_format($dispo->besoin->quantite_recherchee, 0, ',', ' ') }} kg recherchés</span>
                                    <span class="text-stone-300">·</span>
                                    <span class="text-stone-500">échéance {{ $dispo->besoin->delai->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $dispos->links() }}</div>
        @endif
    </div>
</x-app-layout>