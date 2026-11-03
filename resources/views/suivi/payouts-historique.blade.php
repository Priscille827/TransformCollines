<x-app-layout title="Historique de mes paiements">
    <div class="max-w-4xl mx-auto">

        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2 flex items-center gap-2">
                <x-icon name="history" size="6" class="text-stone-700" />
                Historique de mes paiements
            </h1>
            <p class="text-stone-500">Tous les paiements reçus et confirmés</p>
        </div>

        {{-- Onglets --}}
        <div class="mb-6 flex items-center gap-2 border-b border-stone-200">
            <a href="{{ route('suivi.payouts') }}"
               class="px-4 py-2.5 text-sm font-bold border-b-2 border-transparent text-stone-500 hover:text-stone-900">
                En cours
            </a>
            <a href="{{ route('suivi.payouts.historique') }}"
               class="px-4 py-2.5 text-sm font-bold border-b-2 border-green-700 text-green-700">
                Historique
            </a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-gradient-to-br from-green-700 to-green-600 text-white rounded-2xl p-5 shadow-md">
                <p class="text-xs text-green-100 font-black uppercase tracking-wider mb-1">Total reçu</p>
                <p class="text-2xl font-black">{{ number_format($stats['total_recu'], 0, ',', ' ') }} F</p>
                <p class="text-xs text-green-100 mt-1">Depuis le début</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <p class="text-xs text-stone-500 font-black uppercase tracking-wider mb-1">Payouts reçus</p>
                <p class="text-2xl font-black text-stone-900">{{ $stats['nb_payouts'] }}</p>
                <p class="text-xs text-stone-500 mt-1">Confirmés</p>
            </div>
        </div>

        {{-- Liste --}}
        @if($payouts->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-700">Aucun paiement reçu</p>
                <p class="text-sm text-stone-500 mt-1">Vos paiements confirmés apparaîtront ici.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($payouts as $payout)
                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                        <div class="flex items-start justify-between gap-4 mb-3 flex-wrap">
                            <div>
                                <p class="font-bold text-stone-900">
                                    Période {{ $payout->periode_debut->format('d/m') }} → {{ $payout->periode_fin->format('d/m/Y') }}
                                </p>
                                <p class="text-xs text-stone-500">
                                    Confirmé le {{ $payout->confirme_vendeur_le?->format('d/m/Y à H:i') ?? '—' }}
                                </p>
                            </div>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-300">
                                <x-icon name="check-circle-2" size="3" />
                                Reçu
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Brut</p>
                                <p class="font-bold text-stone-900">{{ number_format($payout->montant, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-stone-50 rounded-lg p-3">
                                <p class="text-xs text-stone-500">Commission</p>
                                <p class="font-bold text-red-600">-{{ number_format($payout->commission, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <p class="text-xs text-green-700 font-bold">Net reçu</p>
                                <p class="font-black text-green-800 text-lg">{{ number_format($payout->montant_net, 0, ',', ' ') }} F</p>
                            </div>
                        </div>

                        @if($payout->reference_kkiapay)
                            <p class="text-xs text-stone-500 mt-3">
                                Référence Kkiapay : <span class="font-mono font-bold text-stone-900">{{ $payout->reference_kkiapay }}</span>
                            </p>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $payouts->links() }}</div>
        @endif
    </div>
</x-app-layout>