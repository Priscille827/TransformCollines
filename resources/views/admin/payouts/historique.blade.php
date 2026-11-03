<x-app-layout title="Historique des payouts">
    <div class="max-w-6xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2 flex items-center gap-2">
                <x-icon name="history" size="6" class="text-stone-700" />
                Historique des payouts
            </h1>
            <p class="text-stone-500">Tous les paiements confirmés par les vendeurs</p>
        </div>

        {{-- Onglets --}}
        <div class="mb-6 flex items-center gap-2 border-b border-stone-200">
            <a href="{{ route('admin.payouts') }}"
               class="px-4 py-2.5 text-sm font-bold border-b-2 border-transparent text-stone-500 hover:text-stone-900">
                Actifs
            </a>
            <a href="{{ route('admin.payouts.historique') }}"
               class="px-4 py-2.5 text-sm font-bold border-b-2 border-amber-600 text-amber-700">
                Historique
                <span class="ml-1 px-1.5 py-0.5 rounded-full text-[10px] bg-amber-100 text-amber-800">
                    {{ $stats['nb_historique'] }}
                </span>
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-gradient-to-br from-green-700 to-green-600 text-white rounded-2xl p-5 shadow-md">
                <p class="text-xs text-green-100 font-black uppercase tracking-wider mb-1">Total reversé</p>
                <p class="text-2xl font-black">{{ number_format($stats['total_historique'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-green-100 mt-1">{{ $stats['nb_historique'] }} payout(s)</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <p class="text-xs text-stone-500 font-black uppercase tracking-wider mb-1">Commission totale</p>
                <p class="text-2xl font-black text-red-600">-{{ number_format($stats['total_commission'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-stone-500 mt-1">2 % prélevés</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <p class="text-xs text-stone-500 font-black uppercase tracking-wider mb-1">7 derniers jours</p>
                <p class="text-2xl font-black text-stone-900">{{ number_format($stats['derniere_semaine'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-stone-500 mt-1">Reversés récemment</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="text" name="vendeur" value="{{ request('vendeur') }}"
                       placeholder="Rechercher un vendeur..."
                       class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 outline-none">

                <input type="month" name="periode" value="{{ request('periode') }}"
                       class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-amber-500/20 focus:border-amber-600 outline-none">

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
                <p class="font-bold text-stone-700">Aucun payout terminé</p>
                <p class="text-sm text-stone-500 mt-1">Les payouts confirmés par les vendeurs apparaîtront ici.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($payouts as $payout)
                    <a href="{{ route('admin.payouts.show', $payout) }}"
                       class="block bg-white rounded-2xl shadow-sm border border-stone-100 p-5 hover:bg-stone-50 hover:border-stone-200 transition">

                        <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                            <div>
                                <p class="font-bold text-stone-900">{{ $payout->vendeurNom() }}</p>
                                <p class="text-xs text-stone-500">
                                    Période {{ $payout->periode_debut->format('d/m') }} → {{ $payout->periode_fin->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-stone-400 font-mono mt-1">#{{ $payout->id }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold border-2 bg-green-100 text-green-800 border-green-300">
                                <x-icon name="check-circle-2" size="3" />
                                Terminé
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Brut</p>
                                <p class="font-bold text-stone-900">{{ number_format($payout->montant, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Commission</p>
                                <p class="font-bold text-red-600">-{{ number_format($payout->commission, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <p class="text-xs text-green-700 font-bold">Net payé</p>
                                <p class="font-black text-green-800">{{ number_format($payout->montant_net, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Confirmé le</p>
                                <p class="font-bold text-stone-900">{{ $payout->confirme_vendeur_le?->format('d/m/Y') ?? '—' }}</p>
                            </div>
                        </div>

                        @if($payout->reference_kkiapay)
                            <p class="text-xs text-stone-500 mt-3">
                                Référence Kkiapay : <span class="font-mono font-bold text-stone-900">{{ $payout->reference_kkiapay }}</span>
                            </p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-6">{{ $payouts->links() }}</div>
        @endif
    </div>
</x-app-layout>