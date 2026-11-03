<x-app-layout title="Tableau de bord admin">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Tableau de bord administrateur</h1>
        <p class="text-gray-500 text-sm mt-1">Vue d'ensemble de la plateforme</p>
    </div>

    {{-- Alertes --}}
    @if($stats['comptes_en_attente'] > 0 || $stats['propositions'] > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            @if($stats['comptes_en_attente'] > 0)
                <a href="{{ route('admin.comptes', ['statut_validation' => 'en_attente']) }}"
                   class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center justify-between hover:bg-amber-100 transition">
                    <div class="flex items-center gap-3">
                        <x-icon name="user-clock" size="8" class="text-amber-600" />
                        <div>
                            <p class="font-semibold text-amber-900">{{ $stats['comptes_en_attente'] }} compte(s) en attente</p>
                            <p class="text-xs text-amber-700">À valider ou refuser</p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" size="5" class="text-amber-600" />
                </a>
            @endif

            @if($stats['propositions'] > 0)
                <a href="{{ route('admin.propositions') }}"
                   class="bg-blue-50 border border-blue-200 rounded-xl p-5 flex items-center justify-between hover:bg-blue-100 transition">
                    <div class="flex items-center gap-3">
                        <x-icon name="package-plus" size="8" class="text-blue-600" />
                        <div>
                            <p class="font-semibold text-blue-900">{{ $stats['propositions'] }} proposition(s) de produit</p>
                            <p class="text-xs text-blue-700">À vérifier</p>
                        </div>
                    </div>
                    <x-icon name="arrow-right" size="5" class="text-blue-600" />
                </a>
            @endif
        </div>
    @endif

    {{-- KPI --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Utilisateurs</span>
                <x-icon name="users" size="5" class="text-blue-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['comptes_total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $stats['producteurs'] }} prod. · {{ $stats['unites'] }} unités · {{ $stats['population'] }} pop.
            </p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Besoins actifs</span>
                <x-icon name="clipboard-list" size="5" class="text-amber-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['besoins_actifs'] }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Offres actives</span>
                <x-icon name="package" size="5" class="text-green-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['offres_actives'] }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-500 font-medium">Commandes</span>
                <x-icon name="shopping-cart" size="5" class="text-purple-500" />
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['commandes_total'] }}</p>
            <p class="text-xs text-gray-500 mt-1">{{ $stats['commandes_attente'] }} en attente</p>
        </div>
    </div>

    {{-- Derniers comptes --}}
    @if($derniersComptes->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Derniers comptes en attente</h2>
                <a href="{{ route('admin.comptes', ['statut_validation' => 'en_attente']) }}"
                   class="text-sm text-green-700 font-medium hover:underline">Voir tout →</a>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($derniersComptes as $c)
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $c->nom }}</p>
                            <p class="text-xs text-gray-500">
                                {{ ucfirst(str_replace('_', ' ', $c->type_compte)) }} · {{ $c->commune->nom ?? '—' }}
                            </p>
                        </div>
                        <a href="{{ route('admin.comptes.show', $c) }}"
                           class="text-xs font-medium px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            Examiner
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Liens rapides --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <a href="{{ route('admin.comptes') }}"
           class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
            <x-icon name="users" size="7" class="mx-auto text-gray-600 mb-2" />
            <p class="text-xs font-medium text-gray-700">Comptes</p>
        </a>
        <a href="{{ route('admin.produits') }}"
           class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
            <x-icon name="package" size="7" class="mx-auto text-gray-600 mb-2" />
            <p class="text-xs font-medium text-gray-700">Catalogue</p>
        </a>
        <a href="{{ route('admin.propositions') }}"
           class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
            <x-icon name="package-plus" size="7" class="mx-auto text-gray-600 mb-2" />
            <p class="text-xs font-medium text-gray-700">Propositions</p>
        </a>
        <a href="{{ route('marketplace.index') }}"
           class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center hover:shadow-md transition">
            <x-icon name="shopping-basket" size="7" class="mx-auto text-gray-600 mb-2" />
            <p class="text-xs font-medium text-gray-700">Marché</p>
        </a>
    </div>
</x-app-layout>