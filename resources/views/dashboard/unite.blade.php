{{-- KPI --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Besoins actifs</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <x-icon name="clipboard-list" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $besoinsActifs ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $besoinsCouverts ?? 0 }} couverts</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Taux moyen</span>
            <div class="w-10 h-10 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                <x-icon name="trending-up" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $tauxMoyen ?? 0 }}%</p>
        <p class="text-xs text-stone-500 mt-1">de couverture</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5 {{ ($receptionsEnAttente ?? 0) > 0 ? 'ring-2 ring-amber-500' : '' }}">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Réceptions</span>
            <div class="w-10 h-10 rounded-2xl {{ ($receptionsEnAttente ?? 0) > 0 ? 'bg-amber-500 text-white' : 'bg-blue-100 text-blue-700' }} flex items-center justify-center">
                <x-icon name="package-check" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $receptionsEnAttente ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">à confirmer</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Mes ventes</span>
            <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center">
                <x-icon name="tag" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $offresActives ?? 0 }}</p>
        <p class="text-xs text-stone-500 mt-1">produits finis</p>
    </div>
</div>

{{-- Actions rapides --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <a href="{{ route('besoins.create') }}"
       class="group bg-gradient-to-br from-green-800 to-green-700 text-white rounded-3xl p-5 shadow-lg shadow-green-800/20 hover:shadow-xl transition flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                <x-icon name="plus-circle" size="6" />
            </div>
            <div>
                <p class="font-black">Publier un besoin</p>
                <p class="text-green-100 text-xs">Rechercher des matières premières</p>
            </div>
        </div>
        <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
    </a>

    @if(($receptionsEnAttente ?? 0) > 0)
        <a href="{{ route('receptions.index') }}"
           class="group bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-3xl p-5 shadow-lg shadow-amber-500/20 hover:shadow-xl transition flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                    <x-icon name="package-check" size="6" />
                </div>
                <div>
                    <p class="font-black">{{ $receptionsEnAttente }} réception{{ $receptionsEnAttente > 1 ? 's' : '' }}</p>
                    <p class="text-amber-100 text-xs">À confirmer</p>
                </div>
            </div>
            <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
        </a>
    @endif

    <a href="{{ route('stocks.create') }}"
       class="group bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-3xl p-5 shadow-lg shadow-blue-500/20 hover:shadow-xl transition flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center">
                <x-icon name="warehouse" size="6" />
            </div>
            <div>
                <p class="font-black">Ajouter du stock</p>
                <p class="text-blue-100 text-xs">Produits finis</p>
            </div>
        </div>
        <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
    </a>
</div>

{{-- Contenu --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Mes besoins récents --}}
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-black text-stone-900 flex items-center gap-2">
                <x-icon name="clipboard-list" size="5" class="text-amber-700" />
                Mes besoins récents
            </h2>
            <a href="{{ route('besoins.index') }}"
               class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
        </div>

        @if($besoinsRecents->isEmpty())
            <div class="px-6 py-12 text-center">
                <x-icon name="inbox" size="10" class="mx-auto mb-3 text-stone-300" />
                <p class="text-sm text-stone-500">Aucun besoin publié</p>
                <a href="{{ route('besoins.create') }}"
                   class="inline-flex items-center gap-1.5 text-xs text-green-700 font-bold mt-3 hover:underline">
                    <x-icon name="plus" size="3.5" />
                    Publier un besoin
                </a>
            </div>
        @else
            <div class="divide-y divide-stone-100">
                @foreach($besoinsRecents as $besoin)
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
                            {{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg · échéance {{ $besoin->delai->format('d/m/Y') }}
                        </p>
                        <div class="w-full bg-stone-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-green-600 h-1.5 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Mon stock produits finis --}}
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-black text-stone-900 flex items-center gap-2">
                <x-icon name="warehouse" size="5" class="text-blue-700" />
                Mes produits finis
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
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center flex-shrink-0">
                                <x-icon name="package" size="5" />
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-stone-900 text-sm truncate">{{ $stock->produit->nom }}</p>
                                <p class="text-xs text-stone-500">{{ $stock->uniteMesure->nom }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-blue-800 text-sm">{{ number_format($stock->quantite_vendable, 0, ',', ' ') }}</p>
                            <p class="text-xs text-stone-400">vendable</p>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>