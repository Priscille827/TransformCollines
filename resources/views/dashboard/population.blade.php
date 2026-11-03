{{-- KPI --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">En cours</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <x-icon name="clock" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $commandesEnCours ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">commandes actives</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Terminées</span>
            <div class="w-10 h-10 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                <x-icon name="check-circle-2" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $commandesTerminees ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">commandes livrées</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5 {{ ($panierItems ?? 0) > 0 ? 'ring-2 ring-green-500' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Panier</span>
            <div class="w-10 h-10 rounded-2xl {{ ($panierItems ?? 0) > 0 ? 'bg-green-500 text-white' : 'bg-stone-100 text-stone-700' }} flex items-center justify-center">
                <x-icon name="shopping-cart" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $panierItems ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">article{{ ($panierItems ?? 0) > 1 ? 's' : '' }} en attente</p>
    </div>
</div>

{{-- Actions rapides --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
    <a href="{{ route('marketplace.index') }}"
       class="group bg-gradient-to-br from-green-800 to-green-700 text-white rounded-3xl p-6 shadow-lg shadow-green-800/20 hover:shadow-xl transition flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                <x-icon name="shopping-basket" size="7" />
            </div>
            <div>
                <p class="font-black text-lg">Explorer le marché</p>
                <p class="text-green-100 text-sm">Découvrez les produits locaux</p>
            </div>
        </div>
        <x-icon name="arrow-right" size="7" class="group-hover:translate-x-1 transition" />
    </a>

    @if(($panierItems ?? 0) > 0)
        <a href="{{ route('panier.index') }}"
           class="group bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-3xl p-6 shadow-lg shadow-amber-500/20 hover:shadow-xl transition flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                    <x-icon name="shopping-cart" size="7" />
                </div>
                <div>
                    <p class="font-black text-lg">Voir mon panier</p>
                    <p class="text-amber-100 text-sm">{{ $panierItems }} article{{ $panierItems > 1 ? 's' : '' }} prêt{{ $panierItems > 1 ? 's' : '' }}</p>
                </div>
            </div>
            <x-icon name="arrow-right" size="7" class="group-hover:translate-x-1 transition" />
        </a>
    @else
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-stone-100 text-stone-400 flex items-center justify-center">
                    <x-icon name="shopping-cart" size="7" />
                </div>
                <div>
                    <p class="font-black text-stone-900">Votre panier est vide</p>
                    <p class="text-stone-500 text-sm">Ajoutez des produits depuis le marché</p>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Commandes récentes --}}
<div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
        <h2 class="font-black text-stone-900 flex items-center gap-2">
            <x-icon name="shopping-bag" size="5" class="text-green-700" />
            Mes commandes récentes
        </h2>
        <a href="{{ route('commandes.index') }}"
           class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
    </div>

    @if($commandesRecentes->isEmpty())
        <div class="px-6 py-12 text-center">
            <x-icon name="inbox" size="10" class="mx-auto mb-3 text-stone-300" />
            <p class="text-sm text-stone-500">Aucune commande pour le moment</p>
            <a href="{{ route('marketplace.index') }}"
               class="inline-flex items-center gap-1.5 text-xs text-green-700 font-bold mt-3 hover:underline">
                <x-icon name="arrow-right" size="3.5" />
                Découvrir le marché
            </a>
        </div>
    @else
        <div class="divide-y divide-stone-100">
            @foreach($commandesRecentes as $commande)
                @php
                    $badge = match($commande->statut) {
                        'en_attente'         => ['bg-amber-100 text-amber-800', 'En attente'],
                        'acceptee'           => ['bg-blue-100 text-blue-800', 'Acceptée'],
                        'en_preparation'     => ['bg-indigo-100 text-indigo-800', 'Préparation'],
                        'expediee'           => ['bg-purple-100 text-purple-800', 'Expédiée'],
                        'disponible_retrait' => ['bg-cyan-100 text-cyan-800', 'Disponible'],
                        'livree'             => ['bg-green-100 text-green-800', 'Livrée'],
                        'terminee'           => ['bg-green-200 text-green-900', 'Terminée'],
                        'refusee'            => ['bg-red-100 text-red-800', 'Refusée'],
                        'annulee'            => ['bg-stone-100 text-stone-600', 'Annulée'],
                        default              => ['bg-stone-100 text-stone-700', $commande->statut],
                    };
                @endphp
                <a href="{{ route('commandes.show', $commande) }}"
                   class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-stone-50 transition">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-mono text-xs text-stone-500">{{ $commande->reference }}</p>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </div>
                        <p class="font-bold text-stone-900 text-sm truncate">
                            {{ $commande->producteur->utilisateur->nom }}
                            <span class="text-stone-400 font-normal">· {{ $commande->lignes->count() }} article{{ $commande->lignes->count() > 1 ? 's' : '' }}</span>
                        </p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="font-black text-green-800">{{ number_format($commande->montant_total, 0, ',', ' ') }} F</p>
                        <p class="text-xs text-stone-400">{{ $commande->created_at->format('d/m/Y') }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>