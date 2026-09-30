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
                    <div class="w-14 h-14 rounded-lg bg-green-100 text-green-700 flex items-center justify-center">
                        <x-icon name="factory" size="7" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">{{ $unite->utilisateur->nom }}</h1>
                        <p class="text-sm text-gray-500 flex items-center gap-1.5 mt-0.5">
                            <x-icon name="map-pin" size="4" />
                            {{ $unite->utilisateur->commune->nom }}
                            @if($unite->capacite_traitement_approx)
                                · Capacité {{ $unite->capacite_traitement_approx }} T
                            @endif
                        </p>
                    </div>
                </div>

                @php
                    $badgeConfig = match($unite->score_fiabilite) {
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
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Taux de ponctualité</p>
                <p class="text-2xl font-bold text-green-700 mt-1">{{ $stats['taux'] }}%</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Paiements à temps</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['a_temps'] }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">En retard</p>
                <p class="text-2xl font-bold text-amber-700 mt-1">{{ $stats['en_retard'] }}</p>
            </div>
        </div>

        {{-- Répartition --}}
        @if($stats['total'] > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
                <p class="text-sm font-medium text-gray-700 mb-3">Répartition des paiements</p>
                <div class="flex h-3 rounded-full overflow-hidden bg-gray-100">
                    @php
                        $pctATemps = $stats['total'] > 0 ? ($stats['a_temps'] / $stats['total']) * 100 : 0;
                        $pctRetard = $stats['total'] > 0 ? ($stats['en_retard'] / $stats['total']) * 100 : 0;
                    @endphp
                    <div class="bg-green-500" style="width: {{ $pctATemps }}%"></div>
                    <div class="bg-red-500" style="width: {{ $pctRetard }}%"></div>
                </div>
                <div class="flex gap-4 mt-3 text-xs text-gray-600">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        À temps ({{ $stats['a_temps'] }})
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                        En retard ({{ $stats['en_retard'] }})
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900 flex items-center gap-2">
                    <x-icon name="history" size="5" />
                    Historique des paiements
                </h2>
            </div>

            @if($historique->isEmpty())
                <div class="px-6 py-12 text-center text-gray-400">
                    <x-icon name="inbox" size="12" class="mx-auto mb-3 text-gray-300" />
                    <p class="text-sm">Aucun paiement enregistré pour l'instant</p>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($historique as $h)
                        @php
                            $config = match($h->resultat) {
                                'a_temps'   => ['bg-green-100 text-green-700', 'check-circle-2', 'À temps'],
                                'en_retard' => ['bg-red-100 text-red-700', 'clock', 'En retard'],
                                default     => ['bg-gray-100 text-gray-700', 'minus-circle', $h->resultat],
                            };
                        @endphp
                        <div class="px-6 py-4 flex items-start gap-4">
                            <div class="w-9 h-9 rounded-lg {{ $config[0] }} flex items-center justify-center flex-shrink-0">
                                <x-icon :name="$config[1]" size="5" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            {{ $h->disponibilite?->produit?->nom ?? 'Produit supprimé' }}
                                            <span class="font-normal text-gray-500">
                                                — {{ number_format($h->disponibilite?->quantite_livree ?? 0, 0, ',', ' ') }} kg
                                            </span>
                                        </p>
                                        @if($h->disponibilite?->producteur?->utilisateur)
                                            <p class="text-xs text-gray-500 mt-0.5 flex items-center gap-1.5">
                                                <x-icon name="user" size="3" />
                                                {{ $h->disponibilite->producteur->utilisateur->nom }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $config[0] }} whitespace-nowrap">
                                        {{ $config[2] }}
                                    </span>
                                </div>
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