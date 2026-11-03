{{-- ALERTES PRIORITAIRES --}}
@if($stats['comptes_en_attente'] > 0 || $stats['propositions'] > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
        @if($stats['comptes_en_attente'] > 0)
            <a href="{{ route('admin.comptes', ['statut_validation' => 'en_attente']) }}"
               class="group bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-3xl p-5 shadow-lg shadow-amber-500/20 hover:shadow-xl transition flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <x-icon name="user-clock" size="6" />
                    </div>
                    <div>
                        <p class="font-black text-lg">{{ $stats['comptes_en_attente'] }} compte{{ $stats['comptes_en_attente'] > 1 ? 's' : '' }} à valider</p>
                        <p class="text-amber-100 text-sm">Action requise</p>
                    </div>
                </div>
                <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
            </a>
        @endif

        @if($stats['propositions'] > 0)
            <a href="{{ route('admin.propositions') }}"
               class="group bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-3xl p-5 shadow-lg shadow-blue-500/20 hover:shadow-xl transition flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center">
                        <x-icon name="package-plus" size="6" />
                    </div>
                    <div>
                        <p class="font-black text-lg">{{ $stats['propositions'] }} produit{{ $stats['propositions'] > 1 ? 's' : '' }} à vérifier</p>
                        <p class="text-blue-100 text-sm">Action requise</p>
                    </div>
                </div>
                <x-icon name="arrow-right" size="6" class="group-hover:translate-x-1 transition" />
            </a>
        @endif
    </div>
@endif

{{-- KPI --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Utilisateurs</span>
            <div class="w-10 h-10 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                <x-icon name="users" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $stats['comptes_total'] }}</p>
        <p class="text-xs text-stone-500 mt-2">
            {{ $stats['producteurs'] }} prod. · {{ $stats['unites'] }} unités · {{ $stats['population'] }} pop.
        </p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Besoins actifs</span>
            <div class="w-10 h-10 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                <x-icon name="clipboard-list" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $stats['besoins_actifs'] }}</p>
        <p class="text-xs text-stone-500 mt-2">en cours sur la plateforme</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Offres actives</span>
            <div class="w-10 h-10 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                <x-icon name="package" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $stats['offres_actives'] }}</p>
        <p class="text-xs text-stone-500 mt-2">sur le marché</p>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-5">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Commandes</span>
            <div class="w-10 h-10 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center">
                <x-icon name="shopping-cart" size="5" />
            </div>
        </div>
        <p class="text-3xl font-black text-stone-900">{{ $stats['commandes_total'] }}</p>
        <p class="text-xs text-stone-500 mt-2">{{ $stats['commandes_attente'] }} en attente</p>
    </div>
</div>

{{-- Actions rapides --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8">
    <a href="{{ route('admin.dashboard') }}"
       class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 text-center hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-stone-100 text-stone-700 flex items-center justify-center mx-auto mb-2">
            <x-icon name="layout-dashboard" size="5" />
        </div>
        <p class="text-xs font-bold text-stone-700">Admin</p>
    </a>
    <a href="{{ route('admin.comptes') }}"
       class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 text-center hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mx-auto mb-2">
            <x-icon name="users" size="5" />
        </div>
        <p class="text-xs font-bold text-stone-700">Comptes</p>
    </a>
    <a href="{{ route('admin.produits') }}"
       class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 text-center hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mx-auto mb-2">
            <x-icon name="package" size="5" />
        </div>
        <p class="text-xs font-bold text-stone-700">Catalogue</p>
    </a>
    <a href="{{ route('marketplace.index') }}"
       class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 text-center hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mx-auto mb-2">
            <x-icon name="shopping-basket" size="5" />
        </div>
        <p class="text-xs font-bold text-stone-700">Marché</p>
    </a>
</div>

{{-- Listes --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Derniers comptes --}}
    @if($derniersComptes->isNotEmpty())
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-black text-stone-900 flex items-center gap-2">
                    <x-icon name="user-clock" size="5" class="text-amber-600" />
                    Derniers comptes en attente
                </h2>
                <a href="{{ route('admin.comptes', ['statut_validation' => 'en_attente']) }}"
                   class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @foreach($derniersComptes as $c)
                    <a href="{{ route('admin.comptes.show', $c) }}"
                       class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-stone-50 transition">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-stone-100 text-stone-700 flex items-center justify-center font-black flex-shrink-0">
                                {{ strtoupper(substr($c->nom, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="font-bold text-stone-900 text-sm truncate">{{ $c->nom }}</p>
                                <p class="text-xs text-stone-500">
                                    {{ ucfirst(str_replace('_', ' ', $c->type_compte)) }} · {{ $c->commune->nom ?? '—' }}
                                </p>
                            </div>
                        </div>
                        <x-icon name="chevron-right" size="5" class="text-stone-400 flex-shrink-0" />
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Dernières propositions --}}
    @if($dernieresPropositions->isNotEmpty())
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-black text-stone-900 flex items-center gap-2">
                    <x-icon name="package-plus" size="5" class="text-blue-600" />
                    Propositions récentes
                </h2>
                <a href="{{ route('admin.propositions') }}"
                   class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @foreach($dernieresPropositions as $prop)
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="font-bold text-stone-900 text-sm truncate">« {{ $prop->nom_saisi }} »</p>
                            <p class="text-xs text-stone-500">par {{ $prop->proposeur->nom }}</p>
                        </div>
                        <span class="text-xs text-stone-400">{{ $prop->created_at->diffForHumans() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>