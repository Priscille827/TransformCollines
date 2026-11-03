<x-app-layout title="Paiement">
    <div class="max-w-2xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center shadow-xl">
                <x-icon name="credit-card" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Paiement sécurisé</h1>
            <p class="text-stone-500">Commande {{ $commande->reference }}</p>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl flex items-start gap-3">
                <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Récap --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-stone-500">Vendeur</span>
                <span class="font-bold">{{ $commande->producteur->utilisateur->nom }}</span>
            </div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-stone-500">Articles</span>
                <span class="font-bold">{{ $commande->lignes->count() }}</span>
            </div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-stone-500">Type</span>
                <span class="font-bold">
                    @if($commande->origine === 'besoin')
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs">
                            <x-icon name="clipboard-list" size="3" />
                            Achat B2B
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-800 rounded-full text-xs">
                            <x-icon name="shopping-basket" size="3" />
                            Marketplace
                        </span>
                    @endif
                </span>
            </div>
            <div class="flex items-center justify-between pt-3 border-t border-stone-100">
                <span class="text-stone-900 font-black text-lg">Total à payer</span>
                <span class="text-2xl font-black text-green-800">{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        {{-- Widget Kkiapay --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 text-center">
            <p class="text-sm text-stone-500 mb-6">
                Cliquez sur le bouton ci-dessous pour payer via Mobile Money ou Carte bancaire.
            </p>

            <div class="flex justify-center">
                <kkiapay-widget
                    amount="{{ (int) $commande->montant_total }}"
                    key="{{ config('services.kkiapay.public_key') }}"
                    phone="{{ auth()->user()->telephone }}"
                    name="{{ auth()->user()->nom }}"
                    email="{{ auth()->user()->email ?? '' }}"
                    sandbox="{{ config('services.kkiapay.sandbox') ? 'true' : 'false' }}"
                    callback="{{ route('paiement.callback') }}"
                    data='{"commande_id": {{ $commande->id }}, "reference": "{{ $commande->reference }}", "source": "commande"}'>
                </kkiapay-widget>
            </div>

            <p class="text-xs text-stone-400 mt-6 flex items-center justify-center gap-1.5">
                <x-icon name="lock" size="3.5" />
                Paiement sécurisé par Kkiapay
            </p>
        </div>

        {{-- Info sandbox --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 mt-6">
            <div class="flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-blue-900">
                    <strong>Mode sandbox :</strong> utilisez le numéro <strong class="font-mono">61000000</strong> pour simuler un paiement réussi.
                </p>
            </div>
        </div>

        {{-- Annuler --}}
        <div class="text-center mt-6">
            <a href="{{ route('dashboard') }}"
               class="text-sm text-stone-500 hover:text-stone-800 transition">
                Annuler et revenir au tableau de bord
            </a>
        </div>
    </div>

    {{-- Script Kkiapay --}}
    <script src="https://cdn.kkiapay.me/k.js"></script>
</x-app-layout>