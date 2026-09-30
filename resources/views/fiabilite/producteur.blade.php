<x-app-layout title="Historique de fiabilité">
    <div class="max-w-4xl mx-auto">

        <a href="{{ $estMonProfil ? route('dashboard') : url()->previous() }}"
           class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour
        </a>

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-amber-400 text-green-900 flex items-center justify-center font-bold text-xl">
                        {{ strtoupper(substr($producteur->utilisateur->nom, 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $producteur->utilisateur->nom }}</h1>
                        <p class="text-sm text-gray-500 flex items-center gap-1.5 mt-0.5">
                            <x-icon name="map-pin" size="4" />
                            {{ $producteur->utilisateur->commune->nom }}
                            @if($producteur->est_cooperative)
                                · Coopérative ({{ $producteur->nombre_membres_approx }} membres)
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Badge de fiabilité --}}
                @php
                    $badgeConfig = match($producteur->score_fiabilite) {
                        'fiable'       => ['bg-green-100 text-green-800 border-green-200', 'check-circle-2', 'Fiable'],
                        'a_surveiller' => ['bg-amber-100 text-amber-800 border-amber-200', 'alert-triangle', 'À surveiller'],
                        default        => ['bg-gray-100 text-gray-700 border-gray-200', 'sparkles', 'Nouveau'],
                    };
                @endphp
                <div class="px-4 py-2 rounded-lg border-2 {{ $badgeConfig[0] }} flex items-center gap-2">
                    <x-icon :name="$badgeConfig[1]" size="5" />
                    <span class="font-semibold">{{ $badgeConfig[2] }}</span>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Transactions</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Taux de respect</p>
                <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['taux'] }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Volume livré</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ number_format($stats['volume_livre'] / 1000, 1, ',', ' ') }} T
                </p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Engagements tenus</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $stats['tenus'] }}<span class="text-gray-400 text-base">/{{ $stats['total'] }}</span>
                </p>
            </div>
        </div>

        {{-- Répartition visuelle --}}
        @if($stats['total'] > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <p class="text-sm font-medium text-gray-700 mb-3">Répartition des engagements</p>
                <div class="flex h-3 rounded-full overflow-hidden bg-gray-100">
                    @php
                        $pctTenus = $stats['total'] > 0 ? ($stats['tenus'] / $stats['total']) * 100 : 0;
                        $pctPartiels = $stats['total'] > 0 ? ($stats['partiels'] / $stats['total']) * 100 : 0;
                        $pctNonTenus = $stats['total'] > 0 ? ($stats['non_tenus'] / $stats['total']) * 100 : 0;
                    @endphp
                    <div class="bg-green-500" style="width: {{ $pctTenus }}%"></div>
                    <div class="bg-amber-500" style="width: {{ $pctPartiels }}%"></div>
                    <div class="bg-red-500" style="width: {{ $pctNonTenus }}%"></div>
                </div>
                <div class="flex gap-4 mt-3 text-xs text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        Tenus ({{ $stats['tenus'] }})
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        Partiels ({{ $stats['partiels'] }})
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        Non tenus ({{ $stats['non_tenus'] }})
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <x-icon name="history" size="5" />
                    Historique des livraisons
                </h2>
            </div>

            @if($historique->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400">
                    <x-icon name="inbox" size="12" class="mx-auto mb-3 text-gray-300" />
                    <p class="text-sm">Aucune livraison enregistrée pour l'instant</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($historique as $h)
                        @php
                            $config = match($h->resultat) {
                                'tenu'             => ['bg-green-100 text-green-700', 'check-circle-2', 'Tenu', 'text-green-700'],
                                'partiellement_tenu' => ['bg-amber-100 text-amber-700', 'alert-triangle', 'Partiellement tenu', 'text-amber-700'],
                                'non_tenu'         => ['bg-red-100 text-red-700', 'x-circle', 'Non tenu', 'text-red-700'],
                                default            => ['bg-gray-100 text-gray-700', 'minus-circle', $h->resultat, 'text-gray-700'],
                            };
                        @endphp
                        <div class="px-6 py-4 flex items-start gap-4">
                            <div class="w-9 h-9 rounded-lg {{ $config[0] }} flex items-center justify-center flex-shrink-0">
                                <x-icon :name="$config[1]" size="5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3 mb-1">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $h->disponibilite?->produit?->nom ?? 'Produit supprimé' }}
                                            <span class="font-normal text-gray-500">
                                                — {{ number_format($h->disponibilite?->quantite_livree ?? 0, 0, ',', ' ') }} kg livrés
                                            </span>
                                        </p>
                                        @if($h->disponibilite?->besoin?->unite?->utilisateur)
                                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
    <x-icon name="factory" size="3" />
    <a href="{{ route('fiabilite.unite', $h->disponibilite->besoin->unite) }}"
       class="hover:text-green-700 hover:underline">
        {{ $h->disponibilite->besoin->unite->utilisateur->nom }}
    </a>
</p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $config[0] }} whitespace-nowrap">
                                        {{ $config[2] }}
                                    </span>
                                </div>
                                @if($h->commentaire)
                                    <p class="text-xs text-gray-500 mt-1">{{ $h->commentaire }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-1.5">
                                    {{ $h->created_at?->format('d/m/Y à H:i') ?? '—' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>