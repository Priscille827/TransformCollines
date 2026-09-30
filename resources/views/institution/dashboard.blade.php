<x-app-layout title="Données institutionnelles">
    <div class="mb-6 flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Données institutionnelles</h1>
            <p class="text-gray-500 text-sm mt-1 flex items-center gap-1.5">
                <x-icon name="lock" size="3" />
                Vue agrégée et anonymisée — aucune donnée nominative
            </p>
        </div>
        <a href="{{ route('institution.export', request()->query()) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-lg font-medium text-sm shadow-sm transition">
            <x-icon name="download" size="4" />
            Exporter CSV
        </a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <select name="produit_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                <option value="">Tous les produits</option>
                @foreach($produits as $p)
                    <option value="{{ $p->id }}" @selected(request('produit_id') == $p->id)>{{ $p->nom }}</option>
                @endforeach
            </select>

            <select name="commune_id"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                <option value="">Toutes les communes</option>
                @foreach($communes as $c)
                    <option value="{{ $c->id }}" @selected(request('commune_id') == $c->id)>{{ $c->nom }}</option>
                @endforeach
            </select>

            <select name="periode"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                <option value="7"  @selected(request('periode', '30') == '7')>7 derniers jours</option>
                <option value="30" @selected(request('periode', '30') == '30')>30 derniers jours</option>
                <option value="90" @selected(request('periode') == '90')>3 derniers mois</option>
                <option value="365" @selected(request('periode') == '365')>12 derniers mois</option>
            </select>

            <button type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                <x-icon name="filter" size="4" />
                Appliquer
            </button>
        </div>
    </form>

    {{-- KPI principaux --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Besoins publiés</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $statsGlobales['besoins_total'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $statsGlobales['besoins_actifs'] }} actifs</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                    <x-icon name="clipboard-list" size="5" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Volume recherché</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($statsGlobales['volume_recherche'] / 1000, 1, ',', ' ') }} T</p>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($statsGlobales['volume_couvert'] / 1000, 1, ',', ' ') }} T couverts</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center">
                    <x-icon name="trending-up" size="5" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Disponibilités</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $statsGlobales['dispos_total'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ number_format($statsGlobales['volume_dispo'] / 1000, 1, ',', ' ') }} T disponibles</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-green-100 text-green-700 flex items-center justify-center">
                    <x-icon name="package" size="5" />
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm text-gray-500 font-medium">Taux moyen</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ $statsGlobales['taux_moyen'] }}%</p>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $statsGlobales['producteurs_actifs'] }} producteurs · {{ $statsGlobales['unites_actives'] }} unités
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center">
                    <x-icon name="bar-chart-3" size="5" />
                </div>
            </div>
        </div>
    </div>

    {{-- Répartition par produit --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <x-icon name="layers" size="5" />
                Répartition par produit
            </h2>
        </div>

        @if(empty($parProduit))
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                Aucune donnée pour cette période
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Produit</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Besoins</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Volume recherché</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Volume couvert</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Dispos</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Taux moyen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($parProduit as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $s['produit'] }}</td>
                            <td class="px-6 py-4 text-right">{{ $s['besoins_count'] }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s['volume_recherche'], 0, ',', ' ') }} kg</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s['volume_couvert'], 0, ',', ' ') }} kg</td>
                            <td class="px-6 py-4 text-right">{{ $s['dispos_count'] }}</td>
                            <td class="px-6 py-4 text-right">
                                @php $t = $s['taux_moyen']; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $t >= 70 ? 'bg-green-100 text-green-800' : ($t >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $t }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Répartition par commune --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <x-icon name="map-pin" size="5" />
                Répartition par commune
            </h2>
        </div>

        @if(empty($parCommune))
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                Aucune donnée pour cette période
            </div>
        @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Commune</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Producteurs</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Besoins</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Volume recherché</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Dispos</th>
                        <th class="text-right px-6 py-3 font-medium text-gray-600">Taux moyen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($parCommune as $s)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $s['commune'] }}</td>
                            <td class="px-6 py-4 text-right">{{ $s['producteurs'] }}</td>
                            <td class="px-6 py-4 text-right">{{ $s['besoins_count'] }}</td>
                            <td class="px-6 py-4 text-right">{{ number_format($s['volume_recherche'], 0, ',', ' ') }} kg</td>
                            <td class="px-6 py-4 text-right">{{ $s['dispos_count'] }}</td>
                            <td class="px-6 py-4 text-right">
                                @php $t = $s['taux_moyen']; @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $t >= 70 ? 'bg-green-100 text-green-800' : ($t >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $t }}%
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Zones de tension --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <x-icon name="alert-triangle" size="5" class="text-red-600" />
                Zones de tension (besoins &lt; 50% couverts)
            </h2>
            <span class="text-xs text-gray-500">Top {{ count($zonesTension) }}</span>
        </div>

        @if($zonesTension->isEmpty())
            <div class="px-6 py-12 text-center text-gray-400 text-sm">
                Aucune zone de tension identifiée
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($zonesTension as $b)
                    @php $t = (float) $b->taux_couverture; @endphp
                    <div class="px-6 py-4 flex items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-medium text-gray-900">{{ $b->produit->nom }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $t >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $t }}%
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                <x-icon name="factory" size="3" />
                                {{ $b->unite->utilisateur->nom }}
                                <span class="text-gray-300">·</span>
                                <x-icon name="map-pin" size="3" />
                                {{ $b->unite->utilisateur->commune->nom }}
                            </p>
                        </div>
                        <div class="text-right shrink-0 text-sm">
                            <div class="font-semibold text-gray-900">{{ number_format($b->quantite_recherchee, 0, ',', ' ') }} kg</div>
                            <div class="text-xs text-gray-500">échéance {{ $b->delai->format('d/m/Y') }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Zones sous-mobilisées --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                <x-icon name="users" size="5" class="text-amber-600" />
                Zones sous-mobilisées (à accompagner)
            </h2>
        </div>

        <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($zonesSousMobilisees as $z)
                <div class="border border-amber-200 bg-amber-50 rounded-lg p-4">
                    <p class="text-sm font-semibold text-amber-900">{{ $z['commune'] }}</p>
                    <p class="text-xs text-amber-700 mt-1">{{ $z['producteurs'] }} producteur(s) inscrit(s)</p>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>