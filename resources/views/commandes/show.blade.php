<x-app-layout :title="'Commande ' . $commande->reference">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('commandes.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour à mes commandes
        </a>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- HEADER --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-start justify-between gap-4 mb-4 flex-wrap">
                <div>
                    <p class="font-mono text-xs text-gray-500">{{ $commande->reference }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-1">Commande</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Vendeur : <strong>{{ $commande->producteur->utilisateur->nom }}</strong>
                        · {{ $commande->created_at->format('d/m/Y H:i') }}
                    </p>
                    @if($commande->origine === 'besoin')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold mt-2">
                            <x-icon name="clipboard-list" size="3" />
                            Achat B2B
                        </span>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-2">
                    {{-- Statut commande --}}
                    @php
                        $badge = match($commande->statut) {
                            'en_attente'         => ['bg-amber-100 text-amber-800', 'En attente'],
                            'acceptee'           => ['bg-blue-100 text-blue-800', 'Acceptée'],
                            'en_preparation'     => ['bg-indigo-100 text-indigo-800', 'En préparation'],
                            'expediee'           => ['bg-purple-100 text-purple-800', 'Expédiée'],
                            'disponible_retrait' => ['bg-cyan-100 text-cyan-800', 'Disponible'],
                            'livree'             => ['bg-green-100 text-green-800', 'Livrée'],
                            'terminee'           => ['bg-green-200 text-green-900', 'Terminée'],
                            'refusee'            => ['bg-red-100 text-red-800', 'Refusée'],
                            'annulee'            => ['bg-gray-100 text-gray-600', 'Annulée'],
                            default              => ['bg-gray-100 text-gray-700', $commande->statut],
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $badge[0] }}">
                        {{ $badge[1] }}
                    </span>

                    {{-- Badge paiement --}}
                    @php
                        $badgePaiement = match($commande->statut_paiement) {
                            'paye'       => ['bg-green-100 text-green-800 border-green-300', 'check-circle-2', 'Payé'],
                            'en_attente' => ['bg-amber-100 text-amber-800 border-amber-300', 'clock', 'Paiement en attente'],
                            'rembourse'  => ['bg-blue-100 text-blue-800 border-blue-300', 'refresh-cw', 'Remboursé'],
                            'echoue'     => ['bg-red-100 text-red-800 border-red-300', 'x-circle', 'Paiement échoué'],
                            default      => ['bg-stone-100 text-stone-700 border-stone-300', 'alert-circle', ucfirst($commande->statut_paiement)],
                        };
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider border-2 {{ $badgePaiement[0] }}">
                        <x-icon :name="$badgePaiement[1]" size="3.5" />
                        {{ $badgePaiement[2] }}
                    </span>
                </div>
            </div>

            @if($commande->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-gray-500 text-xs mb-1">Notes</p>
                    <p class="text-sm text-gray-700">{{ $commande->notes }}</p>
                </div>
            @endif
        </div>

        {{-- BANDEAU PAIEMENT --}}
        <div class="mb-6">
            @if($commande->statut_paiement === 'paye')
                <div class="bg-gradient-to-br from-green-50 to-lime-50 border-2 border-green-300 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-green-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <x-icon name="check-circle-2" size="6" />
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-green-900 text-lg">Paiement confirmé</p>
                        <p class="text-sm text-green-800 mt-1">
                            Vous avez payé <strong>{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</strong>
                            le {{ $commande->paye_le?->format('d/m/Y à H:i') ?? '—' }}.
                        </p>
                    </div>
                </div>
            @elseif($commande->statut_paiement === 'en_attente' && $commande->statut === 'acceptee')
                <div class="bg-amber-50 border-2 border-amber-300 rounded-2xl p-5 flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <x-icon name="credit-card" size="6" />
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-amber-900 text-lg">Paiement requis</p>
                        <p class="text-sm text-amber-800 mt-1">
                            Le vendeur a accepté votre commande. Vous devez payer
                            <strong>{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</strong>
                            pour lancer la préparation.
                        </p>
                        <a href="{{ route('paiement.checkout', $commande) }}"
                           class="inline-flex items-center gap-2 mt-3 px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl transition shadow-sm">
                            <x-icon name="credit-card" size="4" />
                            Payer maintenant
                        </a>
                    </div>
                </div>
            @endif
        </div>

        {{-- ARTICLES --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Articles</h2>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($commande->lignes as $ligne)
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-medium">{{ $ligne->produit->nom }}</p>
                            <p class="text-xs text-gray-500">
                                {{ number_format($ligne->quantite, 0, ',', ' ') }} {{ $ligne->uniteMesure->symbole }}
                            </p>
                        </div>
                        <p class="font-semibold">{{ number_format($ligne->sous_total, 0, ',', ' ') }} F</p>
                    </div>
                @endforeach
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                <span class="font-medium text-gray-700">Total</span>
                <span class="text-xl font-bold text-green-700">{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        {{-- ACTIONS --}}
        @if($commande->statut === 'livree' && $commande->statut_paiement === 'paye' && !$commande->confirme_acheteur)
            <div class="bg-gradient-to-br from-green-50 to-lime-50 border-2 border-green-300 rounded-xl p-5">
                <h2 class="font-semibold text-green-900 mb-3 flex items-center gap-2">
                    <x-icon name="package-check" size="5" />
                    Confirmer la réception
                </h2>
                <p class="text-sm text-green-800 mb-4">
                    Confirmez que vous avez bien reçu cette commande.
                </p>
                <form method="POST" action="{{ route('commandes.confirmer-reception', $commande) }}">
                    @csrf
                    <button type="submit"
                            class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <x-icon name="check-circle-2" size="5" />
                        Confirmer la réception
                    </button>
                </form>
            </div>

        @elseif($commande->statut === 'livree' && $commande->confirme_acheteur)
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <x-icon name="clock" size="6" />
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-blue-900 text-lg">En attente de confirmation du vendeur</p>
                        <p class="text-sm text-blue-800 mt-1">
                            Vous avez confirmé la réception. Le vendeur doit confirmer la livraison pour finaliser.
                        </p>
                    </div>
                </div>
            </div>

        @elseif($commande->statut === 'terminee')
            <div class="bg-gradient-to-br from-green-100 to-lime-100 border-2 border-green-400 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-green-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <x-icon name="check-circle-2" size="6" />
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-green-900 text-lg">Transaction terminée</p>
                        <p class="text-sm text-green-800 mt-1">
                            Merci pour votre confiance !
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>