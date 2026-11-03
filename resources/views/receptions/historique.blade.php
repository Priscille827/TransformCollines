<x-app-layout title="Historique des réceptions">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Historique des réceptions</h1>
                <p class="text-stone-500">Toutes vos livraisons confirmées</p>
            </div>
            <a href="{{ route('receptions.index') }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-white hover:bg-stone-50 text-stone-700 border-2 border-stone-200 rounded-2xl text-sm font-bold transition">
                <x-icon name="arrow-left" size="4" />
                Réceptions en attente
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Total</span>
                    <x-icon name="package-check" size="5" class="text-stone-400" />
                </div>
                <p class="text-3xl font-black text-stone-900">{{ $stats['total'] }}</p>
                <p class="text-xs text-stone-500 mt-1">réceptions</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-green-700 font-black uppercase tracking-wider">Conformité</span>
                    <x-icon name="check-circle-2" size="5" class="text-green-600" />
                </div>
                <p class="text-3xl font-black text-green-700">{{ $stats['taux_conformite'] }}%</p>
                <p class="text-xs text-green-600 mt-1">{{ $stats['conformes'] }} conformes</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-amber-700 font-black uppercase tracking-wider">Partielles</span>
                    <x-icon name="alert-triangle" size="5" class="text-amber-600" />
                </div>
                <p class="text-3xl font-black text-amber-700">{{ $stats['partielles'] }}</p>
                <p class="text-xs text-amber-600 mt-1">livraisons incomplètes</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-blue-700 font-black uppercase tracking-wider">Volume</span>
                    <x-icon name="scale" size="5" class="text-blue-600" />
                </div>
                <p class="text-3xl font-black text-blue-700">
                    {{ number_format($stats['volume_total_kg'] / 1000, 1, ',', ' ') }}
                    <span class="text-lg font-bold">T</span>
                </p>
                <p class="text-xs text-blue-600 mt-1">reçus au total</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <select name="produit_id"
                        class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Tous les produits</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}" @selected(request('produit_id') == $p->id)>{{ $p->nom }}</option>
                    @endforeach
                </select>

                <select name="periode"
                        class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Toutes les périodes</option>
                    <option value="7" @selected(request('periode') === '7')>7 derniers jours</option>
                    <option value="30" @selected(request('periode') === '30')>30 derniers jours</option>
                    <option value="90" @selected(request('periode') === '90')>3 derniers mois</option>
                    <option value="365" @selected(request('periode') === '365')>12 derniers mois</option>
                </select>

                <select name="ecart"
                        class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Tous les écarts</option>
                    <option value="conforme" @selected(request('ecart') === 'conforme')>Conformes</option>
                    <option value="partiel" @selected(request('ecart') === 'partiel')>Partielles</option>
                    <option value="absent" @selected(request('ecart') === 'absent')>Absentes</option>
                </select>

                <button type="submit"
                        class="bg-stone-900 hover:bg-stone-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                    <x-icon name="filter" size="4" />
                    Filtrer
                </button>
            </div>

            @if(request()->hasAny(['produit_id', 'periode', 'ecart']))
                <div class="mt-3 pt-3 border-t border-stone-100 flex items-center justify-between">
                    <p class="text-xs text-stone-500">Filtres actifs</p>
                    <a href="{{ route('receptions.historique') }}"
                       class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center gap-1">
                        <x-icon name="x" size="3.5" />
                        Réinitialiser
                    </a>
                </div>
            @endif
        </form>

        {{-- Liste --}}
        @if($receptions->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-stone-100 text-stone-400 flex items-center justify-center">
                    <x-icon name="history" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune réception</p>
                <p class="text-sm text-stone-500">
                    Vos réceptions confirmées apparaîtront ici.
                </p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($receptions as $reception)
                    @php
                        $promis = (float) $reception->quantite;
                        $livre = (float) ($reception->quantite_livree ?? 0);
                        $ratio = $promis > 0 ? $livre / $promis : 0;

                        if ($ratio >= 0.95 && $ratio <= 1.05) {
                            $statut = ['success', 'Conforme', 'check-circle-2'];
                            $couleur = 'green';
                        } elseif ($ratio >= 0.5) {
                            $statut = ['warning', 'Partielle', 'alert-triangle'];
                            $couleur = 'amber';
                        } elseif ($ratio > 0) {
                            $statut = ['danger', 'Très partielle', 'alert-circle'];
                            $couleur = 'red';
                        } else {
                            $statut = ['danger', 'Absente', 'x-circle'];
                            $couleur = 'red';
                        }

                        $uniteNom = $reception->uniteMesure?->nom ?? 'unité';
                        $ecart = $livre - $promis;
                        $ecartPct = $promis > 0 ? round(($ecart / $promis) * 100, 1) : 0;
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:shadow-md transition">
                        <div class="flex items-start justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                <div class="w-12 h-12 rounded-2xl bg-{{ $couleur }}-100 text-{{ $couleur }}-700 flex items-center justify-center flex-shrink-0">
                                    <x-icon :name="$statut[2]" size="6" />
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <p class="font-bold text-stone-900 text-lg">{{ $reception->produit?->nom ?? 'Produit' }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-{{ $couleur }}-100 text-{{ $couleur }}-800">
                                            {{ $statut[1] }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-stone-500">
                                        <strong class="text-stone-700">{{ $reception->producteur?->utilisateur?->nom ?? '—' }}</strong>
                                        · {{ $reception->producteur?->utilisateur?->commune?->nom ?? '—' }}
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <p class="text-xs text-stone-500 uppercase font-bold tracking-wider mb-1">Confirmée</p>
                                <p class="text-sm font-bold text-stone-700">
                                    {{ $reception->updated_at?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                        </div>

                        {{-- Détail quantités --}}
                        <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-stone-100">
                            <div>
                                <p class="text-[10px] text-stone-500 font-black uppercase tracking-wider mb-1">Promis</p>
                                <p class="font-bold text-stone-700">
                                    {{ number_format($promis, 0, ',', ' ') }} {{ $uniteNom }}{{ $promis > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-stone-500 font-black uppercase tracking-wider mb-1">Livré</p>
                                <p class="font-bold text-{{ $couleur }}-700">
                                    {{ number_format($livre, 0, ',', ' ') }} {{ $uniteNom }}{{ $livre > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <div>
                                <p class="text-[10px] text-stone-500 font-black uppercase tracking-wider mb-1">Écart</p>
                                <p class="font-bold {{ $ecart == 0 ? 'text-stone-500' : ($ecart > 0 ? 'text-blue-700' : 'text-red-700') }}">
                                    @if($ecart == 0)
                                        Aucun
                                    @else
                                        {{ $ecart > 0 ? '+' : '' }}{{ number_format($ecart, 0, ',', ' ') }} {{ $uniteNom }}
                                        <span class="text-xs">({{ $ecart > 0 ? '+' : '' }}{{ $ecartPct }} %)</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if($reception->besoin)
                            <div class="mt-3 pt-3 border-t border-stone-100 flex items-center gap-2 text-xs text-stone-500">
                                <x-icon name="clipboard-list" size="3.5" />
                                Besoin : {{ number_format($reception->besoin->quantite_recherchee, 0, ',', ' ') }} kg
                                · échéance {{ $reception->besoin->delai?->format('d/m/Y') ?? '—' }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $receptions->links() }}</div>
        @endif
    </div>
</x-app-layout>