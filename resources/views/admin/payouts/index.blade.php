<x-app-layout title="Payouts">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2 flex items-center gap-2">
                <x-icon name="wallet" size="6" class="text-amber-700" />
                Payouts vendeurs
            </h1>
            <p class="text-stone-500">Gérez les reversements hebdomadaires aux vendeurs</p>
        </div>
        {{-- Onglets --}}
<div class="mb-6 flex items-center gap-2 border-b border-stone-200">
    <a href="{{ route('admin.payouts') }}"
       class="px-4 py-2.5 text-sm font-bold border-b-2 border-amber-600 text-amber-700">
        Actifs
        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800">
            {{ $stats['nb_en_attente'] + $stats['nb_confirmes'] - $stats['nb_confirmes'] }}
        </span>
    </a>
    <a href="{{ route('admin.payouts.historique') }}"
       class="px-4 py-2.5 text-sm font-bold border-b-2 border-transparent text-stone-500 hover:text-stone-900">
        Historique
        <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-stone-100 text-stone-700">
            {{ $stats['nb_confirmes'] }}
        </span>
    </a>
</div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-600 text-red-800 px-4 py-3 rounded-r-xl">
                {{ session('error') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-600 text-blue-800 px-4 py-3 rounded-r-xl">
                {{ session('info') }}
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-2xl p-5 shadow-md">
                <p class="text-xs text-amber-100 font-black uppercase tracking-wider mb-1">À payer</p>
                <p class="text-2xl font-black">{{ number_format($stats['total_a_payer'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-amber-100 mt-1">{{ $stats['nb_en_attente'] }} payout(s)</p>
            </div>

            <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl p-5 shadow-md">
                <p class="text-xs text-blue-100 font-black uppercase tracking-wider mb-1">En attente confirmation</p>
                <p class="text-2xl font-black">{{ number_format($stats['en_attente_vendeur'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-blue-100 mt-1">Vendeur n'a pas confirmé</p>
            </div>

            <div class="bg-gradient-to-br from-green-700 to-green-600 text-white rounded-2xl p-5 shadow-md">
                <p class="text-xs text-green-100 font-black uppercase tracking-wider mb-1">Total payé</p>
                <p class="text-2xl font-black">{{ number_format($stats['total_paye'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-green-100 mt-1">{{ $stats['nb_confirmes'] }} confirmé(s)</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <p class="text-xs text-stone-500 font-black uppercase tracking-wider mb-1">Total</p>
                <p class="text-2xl font-black text-stone-900">{{ $payouts->total() }}</p>
                <p class="text-xs text-stone-500 mt-1">tous les payouts</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="statut" class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 outline-none">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" @selected(request('statut') === 'en_attente')>En attente</option>
                    <option value="en_cours" @selected(request('statut') === 'en_cours')>En cours</option>
                    <option value="paye" @selected(request('statut') === 'paye')>Payé</option>
                </select>

                <select name="confirmation" class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 outline-none">
                    <option value="">Toutes confirmations</option>
                    <option value="non_confirme" @selected(request('confirmation') === 'non_confirme')>Non confirmés</option>
                    <option value="confirme" @selected(request('confirmation') === 'confirme')>Confirmés</option>
                </select>

                <button type="submit" class="bg-stone-900 hover:bg-stone-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                    <x-icon name="filter" size="4" />
                    Filtrer
                </button>
            </div>
        </form>

        {{-- Liste --}}
        @if($payouts->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-700">Aucun payout</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($payouts as $payout)
                    @php
                        $badge = match(true) {
                            $payout->confirme_vendeur    => ['bg-green-100 text-green-800 border-green-300', 'Terminé'],
                            $payout->marque_par_admin    => ['bg-blue-100 text-blue-800 border-blue-300', 'En attente vendeur'],
                            default                      => ['bg-amber-100 text-amber-800 border-amber-300', 'À payer'],
                        };
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">

                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                            <div>
                                <p class="font-bold text-stone-900">{{ $payout->vendeurNom() }}</p>
                                <p class="text-xs text-stone-500">
                                    Période {{ $payout->periode_debut->format('d/m') }} → {{ $payout->periode_fin->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-stone-400 font-mono mt-1">#{{ $payout->id }}</p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border-2 {{ $badge[0] }}">
                                {{ $badge[1] }}
                            </span>
                        </div>

                        {{-- Détail --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-4 text-sm">
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Brut</p>
                                <p class="font-bold text-stone-900">{{ number_format($payout->montant, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Commission 2%</p>
                                <p class="font-bold text-red-600">-{{ number_format($payout->commission, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <p class="text-xs text-green-700 font-bold">Net à payer</p>
                                <p class="font-black text-green-800 text-lg">{{ number_format($payout->montant_net, 0, ',', ' ') }} F</p>
                            </div>
                        </div>

                        {{-- Actions : lien vers la page show --}}
                        @if(!$payout->marque_par_admin)
                            <a href="{{ route('admin.payouts.show', $payout) }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold rounded-xl transition bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white shadow-md">
                                <x-icon name="eye" size="4" />
                                Auditer et payer
                                @if($payout->nombreCommandes() > 0)
                                    <span class="ml-1 bg-white/20 px-2 py-0.5 rounded-full text-xs">
                                        {{ $payout->nombreCommandes() }} cmd
                                    </span>
                                @endif
                            </a>
                        @elseif($payout->marque_par_admin && !$payout->confirme_vendeur)
                            <a href="{{ route('admin.payouts.show', $payout) }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold rounded-xl transition bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200">
                                <x-icon name="eye" size="4" />
                                Voir le détail
                                <span class="ml-1 bg-blue-200/50 px-2 py-0.5 rounded-full text-xs">
                                    En attente vendeur
                                </span>
                            </a>
                        @else
                            <a href="{{ route('admin.payouts.show', $payout) }}"
                               class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-bold rounded-xl transition bg-green-50 hover:bg-green-100 text-green-800 border border-green-200">
                                <x-icon name="check-circle-2" size="4" />
                                Voir le détail
                                <span class="ml-1 bg-green-200/50 px-2 py-0.5 rounded-full text-xs">
                                    Terminé
                                </span>
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $payouts->links() }}</div>
        @endif
    </div>
</x-app-layout>