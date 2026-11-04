<x-app-layout :title="'Commande ' . $commande->reference">
    <div class="max-w-3xl mx-auto">

        <a href="{{ route('commandes.recues.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour
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

        {{-- ═══ HEADER ═══ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-start justify-between gap-4 mb-4 flex-wrap">
                <div>
                    <p class="font-mono text-xs text-gray-500">{{ $commande->reference }}</p>
                    <h1 class="text-2xl font-bold text-gray-900 mt-1">Commande reçue</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        De <strong>{{ $commande->acheteur->nom }}</strong>
                        · {{ $commande->created_at->format('d/m/Y H:i') }}
                    </p>
                    @if($commande->origine === 'besoin')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold mt-2">
                            <x-icon name="clipboard-list" size="3" />
                            Achat B2B (besoin)
                        </span>
                    @endif
                </div>

                <div class="flex flex-col items-end gap-2">
                    @php
                        $badge = match($commande->statut) {
                            'en_attente'         => ['bg-amber-100 text-amber-800', 'À traiter'],
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

            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Mode</p>
                    <p class="font-medium">{{ $commande->mode_reception === 'retrait' ? 'Retrait' : 'Livraison' }}</p>
                </div>
                @if($commande->adresse_livraison)
                    <div>
                        <p class="text-gray-500">Adresse</p>
                        <p class="font-medium text-sm">{{ $commande->adresse_livraison }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ LIGNES ═══ --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Articles à préparer</h2>
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

        {{-- ═══ ACTIONS ═══ --}}

        {{-- 🔴 AJOUT ICI : Accepter ou refuser une nouvelle commande --}}
        @if($commande->statut === 'en_attente')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-icon name="bell" size="5" class="text-amber-600" />
                    Nouvelle commande à traiter
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    Acceptez la commande pour permettre au client de procéder au paiement.
                </p>
                
                <div class="flex gap-3">
                    {{-- Bouton ACCEPTER --}}
                    <form method="POST" action="{{ route('commandes.recues.accepter', $commande) }}" class="flex-1">
                        @csrf
                        <button type="submit"
                                class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                            <x-icon name="check" size="5" />
                            Accepter la commande
                        </button>
                    </form>

                    {{-- Bouton REFUSER --}}
                    <div class="flex-1" x-data="{ openRefus: false }">
                        <button @click="openRefus = !openRefus" type="button" class="w-full bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                            <x-icon name="x" size="5" />
                            Refuser
                        </button>
                        
                        <div x-show="openRefus" x-cloak class="mt-3 p-4 bg-red-50 rounded-xl border border-red-200">
                            <form method="POST" action="{{ route('commandes.recues.refuser', $commande) }}">
                                @csrf
                                <label class="block text-sm font-bold text-red-900 mb-2">Motif du refus (ex: stock épuisé)</label>
                                <textarea name="motif_annulation" rows="2" required class="w-full rounded-lg border-red-200 focus:border-red-500 focus:ring-red-500 mb-2 text-sm bg-white"></textarea>
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-lg text-sm">Confirmer le refus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($commande->statut === 'acceptee' && $commande->statut_paiement === 'en_attente')
            <div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <x-icon name="clock" size="6" />
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-amber-900 text-lg">En attente du paiement client</p>
                        <p class="text-sm text-amber-800 mt-1">
                            Vous avez accepté la commande. Vous pourrez la préparer dès que le client aura payé.
                        </p>
                    </div>
                </div>
            </div>

        @elseif($commande->statut === 'acceptee' && $commande->statut_paiement === 'paye')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-icon name="play-circle" size="5" class="text-green-700" />
                    Commencer la préparation
                </h2>
                <p class="text-sm text-gray-500 mb-4">
                    Le paiement est confirmé. Vous pouvez maintenant préparer la commande.
                </p>
                <form method="POST" action="{{ route('commandes.recues.statut', $commande) }}">
                    @csrf
                    <input type="hidden" name="statut" value="en_preparation">
                    <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                        <x-icon name="arrow-right" size="5" />
                        Marquer en « En préparation »
                    </button>
                </form>
            </div>

        @elseif($commande->statut === 'en_preparation' && $commande->statut_paiement === 'paye')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-icon name="package" size="5" class="text-blue-700" />
                    Commande prête
                </h2>
                <p class="text-sm text-gray-500 mb-4">Choisissez le mode de réception finale :</p>

                @if($commande->mode_reception === 'livraison')
                    <form method="POST" action="{{ route('commandes.recues.statut', $commande) }}">
                        @csrf
                        <input type="hidden" name="statut" value="expediee">
                        <button type="submit"
                                class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                            <x-icon name="truck" size="5" />
                            Marquer comme « Expédiée »
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('commandes.recues.statut', $commande) }}">
                        @csrf
                        <input type="hidden" name="statut" value="disponible_retrait">
                        <button type="submit"
                                class="w-full bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                            <x-icon name="store" size="5" />
                            Marquer comme « Disponible pour retrait »
                        </button>
                    </form>
                @endif
            </div>

        @elseif($commande->statut === 'expediee' && $commande->statut_paiement === 'paye')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-icon name="truck" size="5" class="text-purple-700" />
                    En cours de livraison
                </h2>
                <form method="POST" action="{{ route('commandes.recues.statut', $commande) }}">
                    @csrf
                    <input type="hidden" name="statut" value="livree">
                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                        <x-icon name="check-circle-2" size="5" />
                        Marquer comme « Livrée »
                    </button>
                </form>
            </div>

        @elseif($commande->statut === 'disponible_retrait' && $commande->statut_paiement === 'paye')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                    <x-icon name="store" size="5" class="text-cyan-700" />
                    En attente du retrait
                </h2>
                <form method="POST" action="{{ route('commandes.recues.statut', $commande) }}">
                    @csrf
                    <input type="hidden" name="statut" value="livree">
                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-lg transition flex items-center justify-center gap-2">
                        <x-icon name="check-circle-2" size="5" />
                        Marquer comme « Livrée (retirée) »
                    </button>
                </form>
            </div>

        @elseif($commande->statut === 'livree' && $commande->statut_paiement === 'paye' && !$commande->confirme_vendeur)
            <div class="bg-gradient-to-br from-green-50 to-lime-50 border-2 border-green-300 rounded-xl p-5">
                <h2 class="font-semibold text-green-900 mb-3 flex items-center gap-2">
                    <x-icon name="truck" size="5" />
                    Confirmer la livraison
                </h2>
                <p class="text-sm text-green-800 mb-4">
                    Confirmez que vous avez bien livré cette commande.
                    Le client devra ensuite confirmer la réception pour finaliser.
                </p>
                <form method="POST" action="{{ route('commandes.recues.confirmer-livraison', $commande) }}">
                    @csrf
                    <button type="submit"
                            class="w-full bg-green-700 hover:bg-green-800 text-white font-bold py-3 rounded-xl shadow-md transition flex items-center justify-center gap-2">
                        <x-icon name="check-circle-2" size="5" />
                        Confirmer la livraison
                    </button>
                </form>
            </div>

        @elseif($commande->statut === 'livree' && $commande->statut_paiement === 'paye' && $commande->confirme_vendeur)
            <div class="bg-blue-50 border-2 border-blue-200 rounded-xl p-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center flex-shrink-0 shadow-md">
                        <x-icon name="clock" size="6" />
                    </div>
                    <div class="flex-1">
                        <p class="font-black text-blue-900 text-lg">En attente de la confirmation du client</p>
                        <p class="text-sm text-blue-800 mt-1">
                            Vous avez confirmé la livraison. Le client doit confirmer la réception.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-sm">
                            <x-icon name="check-circle-2" size="4" class="text-green-600" />
                            <span class="text-green-800 font-bold">Vous avez confirmé</span>
                        </div>
                        <div class="mt-2 flex items-center gap-2 text-sm">
                            <x-icon name="clock" size="4" class="text-amber-600" />
                            <span class="text-amber-700">En attente du client</span>
                        </div>
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
                            Les 2 parties ont confirmé. Le paiement de
                            <strong>{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</strong>
                            sera reversé à la fin de la semaine.
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>