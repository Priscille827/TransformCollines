<x-app-layout title="Payout #{{ $payout->id }}">
    <div class="max-w-5xl mx-auto">

        {{-- Fil d'ariane --}}
        <div class="mb-6 flex items-center gap-2 text-sm text-stone-500">
            <a href="{{ route('admin.payouts') }}" class="hover:text-stone-900 font-medium">Payouts</a>
            <x-icon name="chevron-right" size="3" />
            <span class="text-stone-900 font-bold">#{{ $payout->id }}</span>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl">
                {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-600 text-blue-800 px-4 py-3 rounded-r-xl">
                {{ session('info') }}
            </div>
        @endif

        {{-- Header payout --}}
        <div class="bg-gradient-to-br from-stone-900 to-stone-800 text-white rounded-3xl p-6 mb-6 relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-64 h-64 bg-lime-400/10 rounded-full blur-3xl"></div>
            <div class="relative">
                <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                    <div>
                        <p class="text-xs text-stone-300 font-black uppercase tracking-wider mb-1">Payout</p>
                        <h1 class="text-3xl font-black">#{{ $payout->id }}</h1>
                        <p class="text-sm text-stone-300 mt-1">
                            {{ $payout->vendeurNom() }}
                        </p>
                        @if($vendeur?->telephone)
                            <p class="text-xs text-stone-400 mt-0.5">📞 {{ $vendeur->telephone }}</p>
                        @endif
                    </div>

                    @php
                        $badge = match(true) {
                            $payout->confirme_vendeur => ['bg-green-500/20 text-green-300 border-green-500/30', 'Terminé'],
                            $payout->marque_par_admin => ['bg-blue-500/20 text-blue-300 border-blue-500/30', 'En attente vendeur'],
                            default                    => ['bg-amber-500/20 text-amber-300 border-amber-500/30', 'À payer'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-black border {{ $badge[0] }}">
                        {{ $badge[1] }}
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-white/10">
                    <div>
                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-wider">Période</p>
                        <p class="font-bold">{{ $payout->periode_debut->format('d/m') }} → {{ $payout->periode_fin->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-wider">Brut</p>
                        <p class="font-bold">{{ number_format($payout->montant, 0, ',', ' ') }} F</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-wider">Commission 2%</p>
                        <p class="font-bold text-red-300">-{{ number_format($payout->commission, 0, ',', ' ') }} F</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-stone-400 font-black uppercase tracking-wider">Net</p>
                        <p class="font-black text-lime-300 text-lg">{{ number_format($payout->montant_net, 0, ',', ' ') }} F</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- AUDIT : vérification de cohérence --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="mb-6">
            @if($ecart < 1)
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3">
                    <x-icon name="check-circle-2" size="5" class="text-green-700 flex-shrink-0 mt-0.5" />
                    <div class="text-sm">
                        <p class="font-bold text-green-900">Cohérence vérifiée</p>
                        <p class="text-green-700">
                            La somme des {{ $commandes->count() }} commande(s) ({{ number_format($totalVerifie, 0, ',', ' ') }} F)
                            correspond au montant brut du payout.
                        </p>
                    </div>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
                    <x-icon name="alert-triangle" size="5" class="text-red-700 flex-shrink-0 mt-0.5" />
                    <div class="text-sm">
                        <p class="font-bold text-red-900">⚠️ Incohérence détectée</p>
                        <p class="text-red-700">
                            Somme des commandes : <strong>{{ number_format($totalVerifie, 0, ',', ' ') }} F</strong>
                            · Montant du payout : <strong>{{ number_format($payout->montant, 0, ',', ' ') }} F</strong>
                            · Écart : <strong>{{ number_format($ecart, 0, ',', ' ') }} F</strong>
                        </p>
                        <p class="text-red-600 text-xs mt-1">Vérifie manuellement avant de marquer comme payé.</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- LISTE DES COMMANDES INCLUSES --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="font-black text-stone-900 flex items-center gap-2">
                        <x-icon name="shopping-bag" size="5" class="text-stone-700" />
                        Commandes incluses
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $commandes->count() }} commande{{ $commandes->count() > 1 ? 's' : '' }} regroupée{{ $commandes->count() > 1 ? 's' : '' }}
                    </p>
                </div>
                <span class="text-xl font-black text-green-800">
                    {{ number_format($totalVerifie, 0, ',', ' ') }} F
                </span>
            </div>

            @if($commandes->isEmpty())
                <div class="px-6 py-12 text-center text-stone-400">
                    <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                    <p class="text-sm">Aucune commande liée à ce payout</p>
                    <p class="text-xs mt-1">Lance <code class="bg-stone-100 px-1.5 py-0.5 rounded">php artisan transform:backfill-payouts</code></p>
                </div>
            @else
                <div class="divide-y divide-stone-100">
                    @foreach($commandes as $cmd)
                        @php
                            $statutBadge = match($cmd->statut) {
                                'terminee' => ['bg-green-100 text-green-800', 'Terminée'],
                                'livree'   => ['bg-emerald-100 text-emerald-800', 'Livrée'],
                                default    => ['bg-stone-100 text-stone-700', ucfirst($cmd->statut)],
                            };
                        @endphp
                        <div class="px-6 py-4 hover:bg-stone-50 transition">
                            <div class="flex items-start justify-between gap-4 mb-2 flex-wrap">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1 flex-wrap">
                                        <span class="font-mono text-sm font-bold text-stone-900">{{ $cmd->reference }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $statutBadge[0] }}">
                                            {{ $statutBadge[1] }}
                                        </span>
                                        <span class="text-xs text-stone-400">{{ $cmd->created_at->format('d/m/Y à H:i') }}</span>
                                    </div>
                                    <p class="text-sm text-stone-600">
                                        Acheteur : <strong>{{ $cmd->acheteur->nom ?? '—' }}</strong>
                                    </p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-lg font-black text-green-800">{{ number_format($cmd->montant_total, 0, ',', ' ') }} F</p>
                                    <a href="{{ route('commandes.show', $cmd) }}"
                                       class="text-xs text-stone-500 hover:text-stone-900 font-medium">
                                        Voir détail →
                                    </a>
                                </div>
                            </div>

                            {{-- Lignes de la commande --}}
                            <div class="mt-3 pl-3 border-l-2 border-stone-100 space-y-1">
                                @foreach($cmd->lignes as $ligne)
                                    <div class="flex items-center justify-between gap-3 text-xs">
                                        <div class="flex items-center gap-2">
                                            <x-icon name="sprout" size="3" class="text-stone-400" />
                                            <span class="font-medium text-stone-700">{{ $ligne->produit->nom ?? '—' }}</span>
                                            <span class="text-stone-500">
                                                {{ number_format($ligne->quantite, 0, ',', ' ') }}
                                                {{ $ligne->uniteMesure->nom ?? '' }}
                                            </span>
                                        </div>
                                        <span class="text-stone-600 whitespace-nowrap">
                                            {{ number_format($ligne->prix_unitaire, 0, ',', ' ') }} F × {{ number_format($ligne->quantite, 0, ',', ' ') }}
                                            = <strong class="text-stone-900">{{ number_format($ligne->sous_total, 0, ',', ' ') }} F</strong>
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- ACTIONS --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6"
             x-data="{ showPayer: false }">

            @if(!$payout->marque_par_admin)
                <div class="mb-4">
                    <h3 class="font-black text-stone-900 mb-1">Action à effectuer</h3>
                    <p class="text-sm text-stone-500">
                        Effectue le virement de <strong class="text-green-800">{{ number_format($payout->montant_net, 0, ',', ' ') }} FCFA</strong>
                        au vendeur via Kkiapay, puis saisis la référence de la transaction.
                    </p>
                </div>

                <button @click="showPayer = !showPayer"
                        class="w-full bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-700 hover:to-amber-600 text-white font-bold py-3 rounded-xl shadow-md flex items-center justify-center gap-2">
                    <x-icon name="credit-card" size="5" />
                    Marquer comme payé
                </button>

                <div x-show="showPayer" x-transition x-cloak class="mt-4 bg-amber-50 border-2 border-amber-200 rounded-xl p-4">
                    <form method="POST" action="{{ route('admin.payouts.marquer-paye', $payout) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm font-bold text-amber-900 mb-2">
                                Référence Kkiapay <span class="text-red-600">*</span>
                            </label>
                            <input type="text" name="reference_kkiapay" required maxlength="100"
                                   placeholder="Ex. TXN123456"
                                   class="w-full border-2 border-amber-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none bg-white">
                            @error('reference_kkiapay')
                                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="flex gap-2">
                            <button type="submit"
                                    class="flex-1 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold py-2.5 rounded-lg">
                                Confirmer le paiement
                            </button>
                            <button type="button" @click="showPayer = false"
                                    class="px-4 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-200 rounded-lg">
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>

            @elseif($payout->marque_par_admin && !$payout->confirme_vendeur)
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start gap-3">
                    <x-icon name="clock" size="5" class="text-blue-700 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-bold text-blue-900">Paiement envoyé</p>
                        <p class="text-sm text-blue-700">
                            Référence Kkiapay : <strong class="font-mono">{{ $payout->reference_kkiapay }}</strong>
                        </p>
                        <p class="text-xs text-blue-600 mt-1">
                            Payé le {{ $payout->marque_par_admin_le?->format('d/m/Y à H:i') ?? '—' }} · En attente de confirmation du vendeur.
                        </p>
                    </div>
                </div>

            @else
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 flex items-start gap-3">
                    <x-icon name="check-circle-2" size="5" class="text-green-700 flex-shrink-0 mt-0.5" />
                    <div>
                        <p class="font-bold text-green-900">Payout terminé</p>
                        <p class="text-sm text-green-700">
                            Référence : <strong class="font-mono">{{ $payout->reference_kkiapay }}</strong>
                        </p>
                        <p class="text-xs text-green-600 mt-1">
                            Confirmé par le vendeur le {{ $payout->confirme_vendeur_le?->format('d/m/Y à H:i') ?? '—' }}.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>