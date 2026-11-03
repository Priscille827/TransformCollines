<x-app-layout title="Mes paiements">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2 flex items-center gap-2">
                <x-icon name="wallet" size="6" class="text-green-700" />
                Mes paiements
            </h1>
            <p class="text-stone-500">Suivez ce qui vous est dû et confirmez les paiements reçus</p>
        </div>
{{-- Onglets --}}
<div class="mb-6 flex items-center gap-2 border-b border-stone-200">
    <a href="{{ route('suivi.payouts') }}"
       class="px-4 py-2.5 text-sm font-bold border-b-2 border-green-700 text-green-700">
        En cours
    </a>
    <a href="{{ route('suivi.payouts.historique') }}"
       class="px-4 py-2.5 text-sm font-bold border-b-2 border-transparent text-stone-500 hover:text-stone-900">
        Historique
    </a>
</div>
        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-600 text-red-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Stats principales --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-amber-100 font-black uppercase tracking-wider">En attente</span>
                    <x-icon name="clock" size="5" />
                </div>
                <p class="text-2xl font-black">{{ number_format($stats['total_du'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-amber-100 mt-1">À recevoir</p>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-blue-100 font-black uppercase tracking-wider">À confirmer</span>
                    <x-icon name="alert-circle" size="5" />
                </div>
                <p class="text-2xl font-black">{{ number_format($stats['total_attente_confirmation'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-blue-100 mt-1">L'admin a payé, confirmez</p>
            </div>

            <div class="bg-gradient-to-br from-green-700 to-green-600 text-white rounded-2xl p-5 shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-green-100 font-black uppercase tracking-wider">Reçus</span>
                    <x-icon name="check-circle-2" size="5" />
                </div>
                <p class="text-2xl font-black">{{ number_format($stats['total_paye'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-green-100 mt-1">Total confirmé</p>
            </div>
        </div>

        {{-- ✅ NOUVEAU : Stats cumulées --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-stone-500 font-black uppercase tracking-wider">CA brut cumulé</span>
                    <x-icon name="trending-up" size="4" class="text-stone-400" />
                </div>
                <p class="text-2xl font-black text-stone-900">{{ number_format($stats['total_brut_cumule'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-stone-500 mt-1">Depuis le début</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Commission</span>
                    <x-icon name="percent" size="4" class="text-red-500" />
                </div>
                <p class="text-2xl font-black text-red-600">-{{ number_format($stats['total_commission'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-stone-500 mt-1">2 % prélevés</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Commandes</span>
                    <x-icon name="shopping-bag" size="4" class="text-stone-400" />
                </div>
                <p class="text-2xl font-black text-stone-900">{{ $stats['nb_commandes_total'] }}</p>
                <p class="text-xs text-stone-500 mt-1">Vendues via la plateforme</p>
            </div>
        </div>

        {{-- ✅ NOUVEAU : Graphique revenus mensuels --}}
        @if($caParMois->count() > 1)
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
                <div class="flex items-center justify-between mb-6 flex-wrap gap-2">
                    <div>
                        <h2 class="font-black text-stone-900 flex items-center gap-2">
                            <x-icon name="trending-up" size="5" class="text-green-700" />
                            Revenus nets par mois
                        </h2>
                        <p class="text-xs text-stone-500 mt-1">12 derniers mois · montant net reçu</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total</p>
                        <p class="text-xl font-black text-green-800">
                            {{ number_format($caParMois->sum('total'), 0, ',', ' ') }} F
                        </p>
                    </div>
                </div>

                @php $maxMois = $caParMois->max('total') ?: 1; @endphp

                <div class="flex items-end justify-between gap-2 h-32">
                    @foreach($caParMois as $m)
                        @php
                            $height = ($m['total'] / $maxMois) * 100;
                            $label  = \Carbon\Carbon::createFromFormat('Y-m', $m['mois'])->translatedFormat('M');
                        @endphp
                        <div class="flex-1 flex flex-col items-center gap-1 group relative">
                            <div class="absolute bottom-full mb-2 bg-stone-900 text-white text-[10px] px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                                {{ $label }} : {{ number_format($m['total'], 0, ',', ' ') }} F
                            </div>
                            <div class="w-full bg-stone-100 rounded-t-lg relative overflow-hidden" style="height: {{ max($height, 3) }}%">
                                <div class="absolute inset-0 bg-gradient-to-t from-green-700 to-green-500 rounded-t-lg"></div>
                            </div>
                            <span class="text-[10px] text-stone-400">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Liste des payouts --}}
        @if($payouts->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="wallet" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-700 text-lg mb-2">Aucun paiement</p>
                <p class="text-sm text-stone-500">Vos paiements apparaîtront ici à la fin de chaque semaine.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($payouts as $payout)
                    @php
                        $badge = match(true) {
                            $payout->confirme_vendeur    => ['bg-green-100 text-green-800', 'Reçu'],
                            $payout->marque_par_admin    => ['bg-blue-100 text-blue-800', 'À confirmer'],
                            default                      => ['bg-amber-100 text-amber-800', 'En attente'],
                        };
                        $commandesIncluses = $commandesParPayout[$payout->id] ?? collect();
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5"
                         x-data="{ showDetails: false }">
                        <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                            <div>
                                <p class="font-bold text-stone-900">
                                    Période {{ $payout->periode_debut->format('d/m') }} → {{ $payout->periode_fin->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-stone-500">
                                    Montant brut : {{ number_format($payout->montant, 0, ',', ' ') }} F
                                    · Commission : {{ number_format($payout->commission, 0, ',', ' ') }} F
                                </p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between pt-3 border-t border-stone-100 flex-wrap gap-3">
                            <div>
                                <p class="text-xs text-stone-500">Montant net à recevoir</p>
                                <p class="text-2xl font-black text-green-800">{{ number_format($payout->montant_net, 0, ',', ' ') }} FCFA</p>
                            </div>

                            @if($payout->marque_par_admin && !$payout->confirme_vendeur)
                                <form method="POST" action="{{ route('suivi.payouts.confirmer', $payout) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-700 hover:bg-green-800 text-white font-bold rounded-xl shadow-md">
                                        <x-icon name="check-circle-2" size="4" />
                                        Confirmer la réception
                                    </button>
                                </form>
                            @elseif($payout->confirme_vendeur)
                                <div class="flex items-center gap-2 text-sm text-green-700 font-bold">
                                    <x-icon name="check-circle-2" size="5" />
                                    Confirmé le {{ $payout->confirme_vendeur_le?->format('d/m/Y à H:i') ?? '—' }}
                                </div>
                            @else
                                <p class="text-xs text-stone-500">Le paiement sera effectué par l'admin en fin de semaine.</p>
                            @endif
                        </div>

                        {{-- ✅ NOUVEAU : détail des commandes --}}
                        @if($commandesIncluses->isNotEmpty())
                            <div class="mt-4 pt-4 border-t border-stone-100">
                                <button type="button"
                                        @click="showDetails = !showDetails"
                                        class="text-xs text-stone-600 hover:text-stone-900 font-bold flex items-center gap-1.5">
                                    <x-icon name="chevron-right" size="3" x-bind:class="showDetails ? 'rotate-90' : ''" class="transition-transform" />
                                    <span x-text="showDetails ? 'Masquer' : 'Voir les {{ $commandesIncluses->count() }} commande{{ $commandesIncluses->count() > 1 ? 's' : '' }}'">Voir les {{ $commandesIncluses->count() }} commande{{ $commandesIncluses->count() > 1 ? 's' : '' }}</span>
                                </button>

                                <div x-show="showDetails" x-transition x-cloak class="mt-3 space-y-1.5 pl-4 border-l-2 border-stone-100">
                                    @foreach($commandesIncluses as $cmd)
                                        <a href="{{ route('commandes.show', $cmd) }}"
                                           class="block text-xs hover:bg-stone-50 rounded px-2 py-2 transition">
                                            <div class="flex items-center justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-2 mb-0.5">
                                                        <p class="font-mono text-stone-500">{{ $cmd->reference }}</p>
                                                        <p class="text-stone-400">·</p>
                                                        <p class="text-stone-400">{{ $cmd->created_at->format('d/m/Y') }}</p>
                                                    </div>
                                                    <p class="text-stone-700 truncate">
                                                        {{ $cmd->lignes->pluck('produit.nom')->filter()->join(', ') }}
                                                    </p>
                                                </div>
                                                <p class="font-bold text-green-800 whitespace-nowrap">
                                                    {{ number_format($cmd->montant_total, 0, ',', ' ') }} F
                                                </p>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $payouts->links() }}</div>
        @endif
    </div>
</x-app-layout>