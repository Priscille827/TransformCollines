<x-app-layout title="Mes commandes">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2 flex items-center gap-2">
                <x-icon name="shopping-bag" size="6" class="text-green-700" />
                Mes commandes
            </h1>
            <p class="text-stone-500">Suivez l'état de vos achats</p>
        </div>

        @if($commandes->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-stone-100 text-stone-400 flex items-center justify-center">
                    <x-icon name="shopping-bag" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune commande</p>
                <p class="text-sm text-stone-500 mb-6">Passez votre première commande sur le marché.</p>
                <a href="{{ route('marketplace.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                    <x-icon name="shopping-basket" size="4" />
                    Voir le marché
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($commandes as $commande)
                    @php
                        // Badge statut commande
                        $badgeStatut = match($commande->statut) {
                            'en_attente'         => ['bg-amber-100 text-amber-800', 'À traiter'],
                            'acceptee'           => ['bg-blue-100 text-blue-800', 'Acceptée'],
                            'en_preparation'     => ['bg-indigo-100 text-indigo-800', 'En préparation'],
                            'expediee'           => ['bg-purple-100 text-purple-800', 'Expédiée'],
                            'disponible_retrait' => ['bg-cyan-100 text-cyan-800', 'Disponible'],
                            'livree'             => ['bg-green-100 text-green-800', 'Livrée'],
                            'terminee'           => ['bg-green-200 text-green-900', 'Terminée'],
                            'refusee'            => ['bg-red-100 text-red-800', 'Refusée'],
                            'annulee'            => ['bg-stone-100 text-stone-600', 'Annulée'],
                            default              => ['bg-stone-100 text-stone-700', $commande->statut],
                        };

                        // Badge paiement
                        $badgePaiement = match($commande->statut_paiement) {
                            'paye'       => ['bg-green-100 text-green-800 border-green-300', 'check-circle-2', 'Payé'],
                            'en_attente' => ['bg-amber-100 text-amber-800 border-amber-300', 'clock', 'Paiement en attente'],
                            'rembourse'  => ['bg-blue-100 text-blue-800 border-blue-300', 'refresh-cw', 'Remboursé'],
                            'echoue'     => ['bg-red-100 text-red-800 border-red-300', 'x-circle', 'Échoué'],
                            default      => ['bg-stone-100 text-stone-700 border-stone-300', 'alert-circle', ucfirst($commande->statut_paiement)],
                        };
                    @endphp

                    <a href="{{ route('commandes.show', $commande) }}"
                       class="block bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md hover:border-green-200 transition">

                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    <p class="font-mono text-xs text-stone-500">{{ $commande->reference }}</p>
                                    @if($commande->origine === 'besoin')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-[10px] font-black uppercase tracking-wider">
                                            <x-icon name="clipboard-list" size="3" />
                                            B2B
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-[10px] font-black uppercase tracking-wider">
                                            <x-icon name="shopping-basket" size="3" />
                                            Marché
                                        </span>
                                    @endif
                                </div>
                                <p class="font-bold text-stone-900">
                                    {{ $commande->lignes->count() }} article{{ $commande->lignes->count() > 1 ? 's' : '' }}
                                    · {{ $commande->producteur->utilisateur->nom }}
                                </p>
                            </div>

                            <div class="flex flex-col items-end gap-1.5">
                                {{-- Badge statut --}}
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeStatut[0] }}">
                                    {{ $badgeStatut[1] }}
                                </span>

                                {{-- Badge paiement --}}
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider border-2 {{ $badgePaiement[0] }}">
                                    <x-icon :name="$badgePaiement[1]" size="3" />
                                    {{ $badgePaiement[2] }}
                                </span>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between pt-3 border-t border-stone-100 flex-wrap gap-2">
                            <p class="text-xs text-stone-400 flex items-center gap-1.5">
                                <x-icon name="calendar" size="3" />
                                {{ $commande->created_at->format('d/m/Y à H:i') }}
                            </p>
                            <p class="font-black text-green-800 text-lg">
                                {{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $commandes->links() }}</div>
        @endif
    </div>
</x-app-layout>