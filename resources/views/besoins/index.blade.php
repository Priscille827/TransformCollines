<x-app-layout title="Besoins">
    @php
        $user = auth()->user();
        $estModePublic = request()->routeIs('besoins.publics');
        $estUnite = $user->isUnite();
    @endphp

    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">
                    {{ $estModePublic ? 'Besoins actifs' : 'Mes besoins' }}
                </h1>
                <p class="text-stone-500">
                    {{ $estModePublic
                        ? 'Tous les besoins publiés sur la plateforme'
                        : 'Les besoins que vous avez publiés' }}
                </p>
            </div>

            {{-- Bouton "Publier" UNIQUEMENT pour les unités --}}
            @if($estUnite)
                <a href="{{ route('besoins.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-lg shadow-green-800/20 transition">
                    <x-icon name="plus" size="4" />
                    Publier un besoin
                </a>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Filtres (uniquement sur la vue publique) --}}
        @if($estModePublic)
            <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <select name="produit_id"
                            class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                        <option value="">Tous les produits</option>
                        @foreach($produits as $p)
                            <option value="{{ $p->id }}" @selected(request('produit_id') == $p->id)>{{ $p->nom }}</option>
                        @endforeach
                    </select>

                    <select name="commune_id"
                            class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                        <option value="">Toutes les communes</option>
                        @foreach($communes as $c)
                            <option value="{{ $c->id }}" @selected(request('commune_id') == $c->id)>{{ $c->nom }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center gap-2 bg-stone-900 hover:bg-stone-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition">
                            <x-icon name="search" size="4" />
                            Filtrer
                        </button>
                        @if(request()->hasAny(['produit_id', 'commune_id']))
                            <a href="{{ route('besoins.publics') }}"
                               class="inline-flex items-center justify-center px-3 py-2.5 text-sm text-stone-600 hover:bg-stone-100 rounded-xl transition">
                                <x-icon name="x" size="4" />
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        @endif

        {{-- Stats (uniquement sur "Mes besoins" pour les unités) --}}
        @if(!$estModePublic && $estUnite && isset($stats))
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                    <p class="text-xs text-stone-500 font-black uppercase tracking-wider">Total</p>
                    <p class="text-3xl font-black text-stone-900 mt-1">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5">
                    <p class="text-xs text-amber-700 font-black uppercase tracking-wider">Actifs</p>
                    <p class="text-3xl font-black text-amber-700 mt-1">{{ $stats['actifs'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-5">
                    <p class="text-xs text-green-700 font-black uppercase tracking-wider">Couverts</p>
                    <p class="text-3xl font-black text-green-700 mt-1">{{ $stats['couverts'] }}</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-5">
                    <p class="text-xs text-stone-600 font-black uppercase tracking-wider">Expirés</p>
                    <p class="text-3xl font-black text-stone-600 mt-1">{{ $stats['expires'] }}</p>
                </div>
            </div>
        @endif

        {{-- Liste --}}
        @if($besoins->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-stone-100 text-stone-400 flex items-center justify-center">
                    <x-icon name="inbox" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">
                    {{ $estModePublic ? 'Aucun besoin actif' : 'Aucun besoin publié' }}
                </p>
                <p class="text-sm text-stone-500 mb-6">
                    @if($estUnite && !$estModePublic)
                        Publiez votre premier besoin pour mobiliser les producteurs.
                    @elseif($estModePublic)
                        Revenez plus tard pour découvrir les nouveaux besoins.
                    @else
                        Aucun besoin pour le moment.
                    @endif
                </p>
                @if($estUnite && !$estModePublic)
                    <a href="{{ route('besoins.create') }}"
                       class="inline-flex items-center gap-2 px-6 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                        <x-icon name="plus" size="4" />
                        Publier mon premier besoin
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($besoins as $besoin)
                    @php
                        $pct = min(100, max(0, (float) $besoin->taux_couverture));
                        $color = $pct >= 100 ? 'green' : ($pct >= 70 ? 'lime' : ($pct >= 40 ? 'amber' : 'red'));

                        $statutBadge = match(true) {
                            $besoin->statut === 'couvert'             => ['bg-green-100 text-green-800', 'Couvert'],
                            $besoin->statut === 'cloture'             => ['bg-stone-100 text-stone-600', 'Clôturé'],
                            $besoin->statut === 'expire'              => ['bg-red-100 text-red-800', 'Expiré'],
                            $pct >= 70                                => ['bg-lime-100 text-lime-800', 'Bien couvert'],
                            $pct >= 40                                => ['bg-amber-100 text-amber-800', 'En cours'],
                            default                                    => ['bg-red-100 text-red-800', 'Urgent'],
                        };
                    @endphp

                    <a href="{{ route('besoins.show', $besoin) }}"
                       class="group bg-white rounded-3xl shadow-sm border border-stone-100 p-5 hover:shadow-lg hover:-translate-y-0.5 transition-all">

                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="clipboard-list" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <p class="font-black text-stone-900 text-lg">{{ $besoin->produit->nom }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $statutBadge[0] }}">
                                            {{ $statutBadge[1] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-stone-500 flex items-center gap-1">
                                        <x-icon name="factory" size="3" />
                                        <span class="truncate">{{ $besoin->unite->utilisateur->nom }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="text-right flex-shrink-0">
                                <p class="font-black text-stone-900">
                                    {{ number_format($besoin->quantite_recherchee, 0, ',', ' ') }}
                                    <span class="text-xs font-bold text-stone-500">kg</span>
                                </p>
                                <p class="text-[10px] text-stone-400 mt-0.5">
                                    échéance {{ $besoin->delai->format('d/m/Y') }}
                                </p>
                            </div>
                        </div>

                        {{-- Localisation --}}
                        <div class="text-xs text-stone-500 flex items-center gap-1 mb-3">
                            <x-icon name="map-pin" size="3" />
                            @if($besoin->communes->isNotEmpty())
                                {{ $besoin->communes->pluck('nom')->join(', ') }}
                            @else
                                {{ $besoin->unite->utilisateur->commune->nom ?? '—' }}
                            @endif
                        </div>

                        {{-- Progression --}}
                        <div class="mb-3">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-bold text-stone-600">Couverture</span>
                                <span class="text-xs font-black text-{{ $color }}-700">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                                <div class="bg-{{ $color }}-500 h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-between text-xs pt-3 border-t border-stone-100">
                            <span class="text-stone-500">
                                {{ $besoin->disponibilites->count() }} proposition(s)
                            </span>
                            <span class="flex items-center gap-1 text-green-700 font-bold">
                                Voir le détail
                                <x-icon name="arrow-right" size="3" />
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8">{{ $besoins->links() }}</div>
        @endif
    </div>
</x-app-layout>