<x-app-layout title="Besoins">
    @php $user = auth()->user(); @endphp

    <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ request()->routeIs('besoins.publics') ? 'Besoins actifs' : 'Mes besoins' }}
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ request()->routeIs('besoins.publics')
                    ? 'Tous les besoins publiés sur la plateforme'
                    : 'Les besoins que vous avez publiés' }}
            </p>
        </div>

        @if($user->isUnite() || $user->type_compte === 'admin')
            <a href="{{ route('besoins.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm shadow-sm transition">
                <x-icon name="plus" size="4" />
                Publier un besoin
            </a>
        @endif
    </div>

    {{-- Filtres (uniquement sur la vue publique) --}}
    @if(request()->routeIs('besoins.publics'))
        <form method="GET" class="mb-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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

                <div class="flex gap-2">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center gap-2 bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        <x-icon name="search" size="4" />
                        Filtrer
                    </button>
                    @if(request()->hasAny(['produit_id', 'commune_id']))
                        <a href="{{ route('besoins.publics') }}"
                           class="inline-flex items-center justify-center px-3 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition">
                            <x-icon name="x" size="4" />
                        </a>
                    @endif
                </div>
            </div>
        </form>
    @endif

    {{-- Liste --}}
    @if($besoins->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <x-icon name="inbox" size="12" class="mx-auto mb-3 text-gray-300" />
            <p class="font-medium text-gray-700 mb-1">Aucun besoin pour le moment</p>
            <p class="text-sm text-gray-500">
                @if($user->isUnite())
                    Publiez votre premier besoin pour mobiliser les producteurs.
                @else
                    Revenez plus tard, ou déclarez une disponibilité.
                @endif
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($besoins as $besoin)
                @php
                    $pct = min(100, max(0, (float) $besoin->taux_couverture));
                    $color = $pct >= 100 ? 'bg-green-500' : ($pct >= 70 ? 'bg-lime-500' : ($pct >= 40 ? 'bg-amber-500' : 'bg-red-500'));
                    $badge = match(true) {
                        $besoin->statut === 'couvert' => 'bg-green-100 text-green-800',
                        $besoin->statut === 'cloture' => 'bg-gray-100 text-gray-700',
                        $besoin->statut === 'expire'  => 'bg-red-100 text-red-800',
                        $pct >= 70                    => 'bg-lime-100 text-lime-800',
                        $pct >= 40                    => 'bg-amber-100 text-amber-800',
                        default                       => 'bg-red-100 text-red-800',
                    };
                @endphp

                <a href="{{ route('besoins.show', $besoin) }}"
                   class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 hover:shadow-md transition block">

                    {{-- Header --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-semibold text-gray-900">{{ $besoin->produit->nom }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                    {{ $pct }}%
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 flex items-center gap-1.5">
                                <x-icon name="factory" size="4" />
                                <span class="truncate">{{ $besoin->unite->utilisateur->nom }}</span>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="font-semibold text-gray-900">
                                {{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }} kg
                            </div>
                            <div class="text-xs text-gray-500">échéance {{ $besoin->delai->format('d/m/Y') }}</div>
                        </div>
                    </div>

                    {{-- Localisation --}}
                    <div class="text-xs text-gray-500 flex items-center gap-1.5 mb-3">
                        <x-icon name="map-pin" size="3" />
                        @if($besoin->communes->isNotEmpty())
                            {{ $besoin->communes->pluck('nom')->join(', ') }}
                        @else
                            {{ $besoin->unite->utilisateur->commune->nom }}
                            @if($besoin->unite->zone_collecte_rayon_km)
                                · rayon {{ (int) $besoin->unite->zone_collecte_rayon_km }} km
                            @endif
                        @endif
                    </div>

                    {{-- Progress --}}
                    <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                        <div class="{{ $color }} h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                    </div>

                    {{-- Footer --}}
                    <div class="mt-3 text-xs text-gray-400 flex items-center justify-between">
                        <span>{{ $besoin->disponibilites->count() }} déclaration(s)</span>
                        <span class="flex items-center gap-1 text-green-700 font-medium">
                            Voir le détail
                            <x-icon name="arrow-right" size="3" />
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $besoins->links() }}
        </div>
    @endif
</x-app-layout>