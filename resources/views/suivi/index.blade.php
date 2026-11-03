<x-app-layout title="Suivi financier">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2">Suivi financier</h1>
            <p class="text-stone-500">Vue d'ensemble de votre stock et de votre chiffre d'affaires</p>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BLOC 1 : VUE D'ENSEMBLE --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

            {{-- Patrimoine total --}}
            <div class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white rounded-3xl p-6 shadow-xl shadow-green-800/20 relative overflow-hidden">
                <div class="absolute -top-20 -right-20 w-60 h-60 bg-lime-400/20 rounded-full blur-3xl"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-xs text-green-100 font-black uppercase tracking-wider">Patrimoine total</span>
                        <x-icon name="wallet" size="6" class="text-lime-300" />
                    </div>
                    <p class="text-4xl font-black mb-1">{{ number_format($totalPatrimoine, 0, ',', ' ') }}</p>
                    <p class="text-xs text-green-100 mb-4">FCFA (CA réalisé + valeur stock)</p>

                    <div class="pt-4 border-t border-white/20 space-y-2 text-xs text-green-100">
                        <div class="flex items-center justify-between">
                            <span>Dont CA réalisé</span>
                            <strong class="text-white">{{ number_format($caRealise, 0, ',', ' ') }} F</strong>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Dont valeur stock</span>
                            <strong class="text-white">{{ number_format($stockValeurDetail, 0, ',', ' ') }} F</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CA réalisé --}}
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-stone-500 font-black uppercase tracking-wider">CA réalisé</span>
                    <div class="w-11 h-11 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                        <x-icon name="check-circle-2" size="6" />
                    </div>
                </div>
                <p class="text-3xl font-black text-green-800 mb-1">{{ number_format($caRealise, 0, ',', ' ') }}</p>
                <p class="text-xs text-stone-500 mb-4">FCFA · Ventes terminées</p>

                <div class="pt-4 border-t border-stone-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">En attente</span>
                        <strong class="text-amber-700">{{ number_format($caEnAttente, 0, ',', ' ') }} F</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Annulé</span>
                        <strong class="text-red-600">{{ number_format($caPerdu, 0, ',', ' ') }} F</strong>
                    </div>
                </div>
            </div>

            {{-- Stock valorisé --}}
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs text-stone-500 font-black uppercase tracking-wider">Stock actuel</span>
                    <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                        <x-icon name="warehouse" size="6" />
                    </div>
                </div>
                <p class="text-3xl font-black text-blue-800 mb-1">{{ number_format($stockValeurDetail, 0, ',', ' ') }}</p>
                <p class="text-xs text-stone-500 mb-4">FCFA · valorisation détail</p>

                <div class="pt-4 border-t border-stone-100 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Au prix de gros</span>
                        <strong class="text-stone-800">{{ number_format($stockValeurGros, 0, ',', ' ') }} F</strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-stone-500">Écart détail/gros</span>
                        <strong class="text-amber-700">+{{ number_format($stockValeurDetail - $stockValeurGros, 0, ',', ' ') }} F</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BLOC 2 : GRAPHIQUE CA 30J --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}

        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="font-black text-stone-900 flex items-center gap-2">
                        <x-icon name="trending-up" size="5" class="text-green-700" />
                        Chiffre d'affaires · 30 derniers jours
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">Commandes livrées ou terminées</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total 30j</p>
                    <p class="text-xl font-black text-green-800">{{ number_format(collect($ventes30j)->sum('total'), 0, ',', ' ') }} F</p>
                </div>
            </div>

            @php
                $maxVente = collect($ventes30j)->max('total') ?: 1;
            @endphp

            <div class="flex items-end justify-between gap-1 h-40">
                @foreach($ventes30j as $jour)
                    @php
                        $height = $maxVente > 0 ? ($jour['total'] / $maxVente) * 100 : 0;
                        $isToday = $jour['date'] === now()->format('Y-m-d');
                    @endphp
                    <div class="flex-1 flex flex-col items-center gap-1 group relative">
                        <div class="absolute bottom-full mb-2 bg-stone-900 text-white text-[10px] px-2 py-1 rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">
                            {{ $jour['label'] }} : {{ number_format($jour['total'], 0, ',', ' ') }} F
                        </div>
                        <div class="w-full bg-stone-100 rounded-t-lg relative overflow-hidden" style="height: {{ max($height, 3) }}%">
                            <div class="absolute inset-0 bg-gradient-to-t {{ $isToday ? 'from-lime-400 to-lime-300' : 'from-green-700 to-green-500' }} rounded-t-lg"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-between text-[10px] text-stone-400 mt-3">
                <span>{{ $ventes30j[0]['label'] ?? '' }}</span>
                <span>{{ $ventes30j[14]['label'] ?? '' }}</span>
                <span>{{ $ventes30j[29]['label'] ?? '' }}</span>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BLOC 3 : DÉTAIL PAR PRODUIT --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}

        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="font-black text-stone-900 flex items-center gap-2">
                        <x-icon name="layers" size="5" class="text-blue-700" />
                        Détail par produit
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">Stock valorisé + chiffre d'affaires réalisé</p>
                </div>
                <span class="text-xs text-stone-500">{{ count($produitsSuivi) }} produit{{ count($produitsSuivi) > 1 ? 's' : '' }}</span>
            </div>

            @if(empty($produitsSuivi))
                <div class="px-6 py-16 text-center text-stone-400">
                    <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                    <p class="text-sm">Aucune donnée à afficher</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-stone-50 border-b border-stone-100">
                            <tr>
                                <th class="text-left px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Produit</th>
                                <th class="text-right px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Stock restant</th>
                                <th class="text-right px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Valeur détail</th>
                                <th class="text-right px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Valeur gros</th>
                                <th class="text-right px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Qté vendue</th>
                                <th class="text-right px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">CA réalisé</th>
                                <th class="text-right px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($produitsSuivi as $suivi)
                                @php
                                    $produit = $suivi['produit'];
                                    $total = $suivi['stock_valeur'] + $suivi['ca_realise'];
                                    $uniteNom = $suivi['unite']?->nom ?? 'u.';
                                    $plurielStock = $suivi['stock_qte'] > 1 ? 's' : '';
                                @endphp
                                <tr class="hover:bg-stone-50 transition">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-green-50 text-green-700 flex items-center justify-center flex-shrink-0">
                                                <x-icon name="sprout" size="5" />
                                            </div>
                                            <div>
                                                <p class="font-bold text-stone-900">{{ $produit->nom ?? '—' }}</p>
                                                <p class="text-xs text-stone-500">{{ ucfirst($produit->type_produit ?? '') }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <p class="font-bold text-stone-900">{{ number_format($suivi['stock_qte'], 0, ',', ' ') }}</p>
                                        <p class="text-xs text-stone-500">{{ $uniteNom }}{{ $plurielStock }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <p class="font-bold text-blue-700">{{ number_format($suivi['stock_valeur'], 0, ',', ' ') }} F</p>
                                        @if($suivi['prix_detail'] > 0)
                                            <p class="text-xs text-stone-500">{{ number_format($suivi['prix_detail'], 0, ',', ' ') }} F/{{ $uniteNom }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <p class="font-bold text-stone-700">{{ number_format($suivi['stock_valeur_gros'], 0, ',', ' ') }} F</p>
                                        @if($suivi['prix_gros'] > 0 && $suivi['prix_gros'] != $suivi['prix_detail'])
                                            <p class="text-xs text-stone-500">{{ number_format($suivi['prix_gros'], 0, ',', ' ') }} F/{{ $uniteNom }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <p class="font-bold text-stone-900">{{ number_format($suivi['qte_vendue'], 0, ',', ' ') }}</p>
                                        <p class="text-xs text-stone-500">vendu{{ $suivi['qte_vendue'] > 1 ? 's' : '' }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <p class="font-bold text-green-700">{{ number_format($suivi['ca_realise'], 0, ',', ' ') }} F</p>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <p class="font-black text-stone-900">{{ number_format($total, 0, ',', ' ') }} F</p>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-stone-50 border-t-2 border-stone-200">
                            <tr>
                                <td class="px-5 py-4 font-black text-stone-900">TOTAL</td>
                                <td class="px-5 py-4 text-right font-black text-stone-900">
                                    {{ number_format($stockQuantiteTotale, 0, ',', ' ') }} kg éq.
                                </td>
                                <td class="px-5 py-4 text-right font-black text-blue-700">
                                    {{ number_format($stockValeurDetail, 0, ',', ' ') }} F
                                </td>
                                <td class="px-5 py-4 text-right font-black text-stone-700">
                                    {{ number_format($stockValeurGros, 0, ',', ' ') }} F
                                </td>
                                <td class="px-5 py-4 text-right font-black text-stone-900">—</td>
                                <td class="px-5 py-4 text-right font-black text-green-700">
                                    {{ number_format($caRealise, 0, ',', ' ') }} F
                                </td>
                                <td class="px-5 py-4 text-right font-black text-stone-900">
                                    {{ number_format($totalPatrimoine, 0, ',', ' ') }} F
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </div>

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- BLOC 4 : TOP CLIENTS + COMMANDES RÉCENTES --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Top clients --}}
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100">
                    <h2 class="font-black text-stone-900 flex items-center gap-2">
                        <x-icon name="users" size="5" class="text-purple-700" />
                        Meilleurs clients
                    </h2>
                </div>

                @if($topClients->isEmpty())
                    <div class="px-6 py-12 text-center text-stone-400 text-sm">
                        Aucun client pour le moment
                    </div>
                @else
                    <div class="divide-y divide-stone-100">
                        @foreach($topClients as $index => $client)
                            <div class="px-6 py-4 flex items-center gap-4">
                                <div class="w-10 h-10 rounded-2xl {{ $index === 0 ? 'bg-gradient-to-br from-amber-400 to-amber-500 text-white' : 'bg-stone-100 text-stone-700' }} flex items-center justify-center font-black flex-shrink-0">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-stone-900 text-sm truncate">{{ $client->acheteur->nom ?? '—' }}</p>
                                    <p class="text-xs text-stone-500">{{ $client->nb_commandes }} commande{{ $client->nb_commandes > 1 ? 's' : '' }}</p>
                                </div>
                                <p class="font-black text-green-800 whitespace-nowrap">{{ number_format($client->total_achats, 0, ',', ' ') }} F</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Commandes récentes --}}
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <h2 class="font-black text-stone-900 flex items-center gap-2">
                        <x-icon name="history" size="5" class="text-stone-700" />
                        Commandes récentes
                    </h2>
                    <a href="{{ route('commandes.recues.index') }}"
                       class="text-xs text-green-700 font-bold hover:underline">Voir tout →</a>
                </div>

                @if($commandesRecentes->isEmpty())
                    <div class="px-6 py-12 text-center text-stone-400 text-sm">
                        Aucune commande
                    </div>
                @else
                    <div class="divide-y divide-stone-100">
                        @foreach($commandesRecentes->take(5) as $commande)
                            @php
                                $badge = match($commande->statut) {
                                    'en_attente'         => ['bg-amber-100 text-amber-800', 'En attente'],
                                    'acceptee'           => ['bg-blue-100 text-blue-800', 'Acceptée'],
                                    'en_preparation'     => ['bg-indigo-100 text-indigo-800', 'Préparation'],
                                    'expediee'           => ['bg-purple-100 text-purple-800', 'Expédiée'],
                                    'disponible_retrait' => ['bg-cyan-100 text-cyan-800', 'Disponible'],
                                    'livree'             => ['bg-green-100 text-green-800', 'Livrée'],
                                    'terminee'           => ['bg-green-200 text-green-900', 'Terminée'],
                                    'refusee'            => ['bg-red-100 text-red-800', 'Refusée'],
                                    'annulee'            => ['bg-stone-100 text-stone-600', 'Annulée'],
                                    default              => ['bg-stone-100 text-stone-700', $commande->statut],
                                };
                            @endphp
                            <a href="{{ route('commandes.show', $commande) }}"
                               class="px-6 py-4 flex items-center justify-between gap-3 hover:bg-stone-50 transition">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <p class="font-mono text-xs text-stone-500">{{ $commande->reference }}</p>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $badge[0] }}">
                                            {{ $badge[1] }}
                                        </span>
                                    </div>
                                    <p class="font-bold text-stone-900 text-sm truncate">{{ $commande->acheteur->nom ?? '—' }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="font-black text-green-800">{{ number_format($commande->montant_total, 0, ',', ' ') }} F</p>
                                    <p class="text-xs text-stone-400">{{ $commande->created_at->format('d/m/Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>