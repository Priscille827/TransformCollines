{{-- KPI --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Stock total</span>
            <div class="w-10 h-10 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                <x-icon name="warehouse" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ number_format($totalKg ?? 0, 0, ',', ' ') }}</p>
        <p class="text-xs text-stone-500 mt-1">kg disponibles</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Propositions</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <x-icon name="send" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $disposActives ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $disposConfirmees ?? 0 }} confirmées</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Mes ventes</span>
            <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                <x-icon name="tag" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $offresActives ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">sur {{ $offresTotal ?? 0 }} au total</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5 {{ $commandesEnAttente > 0 ? 'ring-2 ring-amber-500' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Commandes</span>
            <div class="w-10 h-10 rounded-2xl {{ $commandesEnAttente > 0 ? 'bg-amber-500 text-white' : 'bg-purple-100 text-purple-700' }} flex items-center justify-center">
                <x-icon name="shopping-bag" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $commandesEnAttente ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $commandesEnCours ?? 0 }} en cours</p>
    </div>
</div>

{{-- Actions rapides --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('stocks.create') }}"
       class="group bg-gradient-to-br from-green-800 to-green-700 text-white rounded-3xl p-5 shadow-lg shadow-green-800/20 hover:shadow-xl transition flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                <x-icon name="plus-circle" size="6" />
            </div>
            <div>
                <p class="font-black">Ajouter du stock</p>
                <p class="text-green-100 text-xs">Récolte, achat...</p>
            </div>
        </div>
        <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
    </a>

    <a href="{{ route('disponibilites.create') }}"
       class="group bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-3xl p-5 shadow-lg shadow-amber-500/20 hover:shadow-xl transition flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                <x-icon name="send" size="6" />
            </div>
            <div>
                <p class="font-black">Proposer un produit</p>
                <p class="text-amber-100 text-xs">Répondre à un besoin</p>
            </div>
        </div>
        <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
    </a>

    <a href="{{ route('offres.create') }}"
       class="group bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-3xl p-5 shadow-lg shadow-blue-500/20 hover:shadow-xl transition flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                <x-icon name="tag" size="6" />
            </div>
            <div>
                <p class="font-black">Mettre en vente</p>
                <p class="text-blue-100 text-xs">Sur le marché</p>
            </div>
        </div>
        <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
    </a>
</div>

{{-- Contenu en 2 colonnes --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Mon stock --}}
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-black text-stone-900 flex items-center gap-2">
                <x-icon name="warehouse" size="5" class="text-green-700" />
                Mon stock
            </h2>
            <a href="{{ route('stocks.index') }}"
               class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
        </div>

        @if($stocks->isEmpty())
            <div class="px-6 py-12 text-center">
                <x-icon name="inbox" size="10" class="mx-auto mb-3 text-stone-300" />
                <p class="text-sm text-stone-500">Aucun stock enregistré</p>
                <a href="{{ route('stocks.create') }}"
                   class="inline-flex items-center gap-1.5 text-xs text-green-700 font-bold mt-3 hover:underline">
                    <x-icon name="plus" size="3.5" />
                    Ajouter du stock
                </a>
            </div>
        @else
            <div class="divide-y divide-stone-100">
                @foreach($stocks as $stock)
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-700 flex items-center justify-center flex-shrink-0">
                                <x-icon name="sprout" size="5" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-stone-900 text-sm truncate">{{ $stock->produit->nom }}</p>
                                <p class="text-xs text-stone-500">{{ $stock->uniteMesure->nom }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-green-800 text-sm">{{ number_format($stock->quantite_vendable, 0, ',', ' ') }}</p>
                            <p class="text-xs text-stone-400">vendable</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Besoins correspondant à mes produits --}}
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-black text-stone-900 flex items-center gap-2">
                <x-icon name="clipboard-list" size="5" class="text-amber-700" />
                Besoins correspondant à mes produits
            </h2>
            <a href="{{ route('besoins.publics') }}"
               class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
        </div>

        @if($besoinsProches->isEmpty())
            <div class="px-6 py-12 text-center">
                <x-icon name="inbox" size="10" class="mx-auto mb-3 text-stone-300" />
                <p class="text-sm text-stone-500">Aucun besoin pour vos produits</p>
            </div>
        @else
            <div class="divide-y divide-stone-100">
                @foreach($besoinsProches as $besoin)
                    @php
                        $pct = min(100, max(0, (float) $besoin->taux_couverture));
                    @endphp
                    <a href="{{ route('besoins.show', $besoin) }}"
                       class="px-6 py-4 block hover:bg-stone-50 transition">
                        <div class="flex items-center justify-between gap-3 mb-2">
                            <p class="font-bold text-stone-900 text-sm">{{ $besoin->produit->nom }}</p>
                            <span class="text-xs font-bold {{ $pct >= 70 ? 'text-green-700' : ($pct >= 40 ? 'text-amber-700' : 'text-red-700') }}">
                                {{ $pct }}%
                            </span>
                        </div>
                        <p class="text-xs text-stone-500 mb-2">
                            {{ $besoin->unite->utilisateur->nom }} · {{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg · {{ $besoin->delai->format('d/m/Y') }}
                        </p>
                        <div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>