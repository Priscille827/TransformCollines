<x-app-layout title="Paiement réussi">
    <div class="max-w-2xl mx-auto">

        {{-- Confettis animés --}}
        <style>
            @keyframes confetti-fall {
                0% { transform: translateY(-100vh) rotate(0deg); opacity: 1; }
                100% { transform: translateY(100vh) rotate(720deg); opacity: 0; }
            }
            .confetti {
                position: fixed;
                width: 10px;
                height: 10px;
                animation: confetti-fall 3s linear;
                z-index: 100;
            }
        </style>

        {{-- Icône succès --}}
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-green-500 to-lime-500 text-white flex items-center justify-center shadow-2xl shadow-green-500/40 animate-bounce">
                <x-icon name="check-circle-2" size="14" />
            </div>

            <h1 class="text-4xl sm:text-5xl font-black text-green-800 mb-3">
                Paiement réussi !
            </h1>
            <p class="text-lg text-stone-600 mb-2">
                Merci pour votre confiance.
            </p>
            <p class="text-sm text-stone-500">
                Commande {{ $commande->reference }}
            </p>
        </div>

        {{-- Récap --}}
        <div class="bg-white rounded-3xl shadow-xl border-2 border-green-200 p-6 mb-6">
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-stone-500">Montant payé</span>
                    <span class="font-black text-green-800 text-xl">{{ number_format($commande->montant_total, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-stone-100">
                    <span class="text-stone-500">Vendeur</span>
                    <span class="font-bold">{{ $commande->producteur->utilisateur->nom }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-stone-500">Transaction</span>
                    <span class="font-mono text-xs text-stone-600">{{ $commande->transaction_id }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-stone-500">Payé le</span>
                    <span class="font-medium text-sm">{{ $commande->paye_le?->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>

        {{-- Prochaines étapes --}}
        <div class="bg-blue-50 border-2 border-blue-200 rounded-3xl p-6 mb-6">
            <h2 class="font-black text-blue-900 mb-4 flex items-center gap-2">
                <x-icon name="info" size="5" />
                Prochaines étapes
            </h2>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">1</div>
                    <p class="text-sm text-blue-900"><strong>Le vendeur prépare votre commande</strong></p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">2</div>
                    <p class="text-sm text-blue-900">Vous recevrez une notification quand la commande sera prête</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">3</div>
                    <p class="text-sm text-blue-900">Confirmez la réception une fois livré</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('commandes.show', $commande) }}"
               class="flex-1 bg-green-800 hover:bg-green-900 text-white font-black py-4 rounded-2xl shadow-lg transition text-center flex items-center justify-center gap-2">
                <x-icon name="eye" size="5" />
                Voir ma commande
            </a>
            <a href="{{ route('marketplace.index') }}"
               class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold py-4 rounded-2xl transition text-center flex items-center justify-center gap-2">
                <x-icon name="shopping-basket" size="5" />
                Continuer mes achats
            </a>
        </div>
    </div>

    {{-- Confettis JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colors = ['#10b981', '#84cc16', '#f59e0b', '#3b82f6', '#ec4899'];

            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDuration = (2 + Math.random() * 2) + 's';
                    confetti.style.animationDelay = (Math.random() * 0.5) + 's';
                    document.body.appendChild(confetti);

                    setTimeout(() => confetti.remove(), 5000);
                }, i * 50);
            }
        });
    </script>
</x-app-layout>