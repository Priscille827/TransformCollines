<x-app-layout title="Paiement échoué">
    <div class="max-w-2xl mx-auto">

        {{-- Icône échec --}}
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-gradient-to-br from-red-500 to-red-600 text-white flex items-center justify-center shadow-2xl shadow-red-500/40">
                <x-icon name="x-circle" size="14" />
            </div>

            <h1 class="text-4xl sm:text-5xl font-black text-red-800 mb-3">
                Paiement échoué
            </h1>
            <p class="text-lg text-stone-600 mb-2">
                Le paiement n'a pas pu être traité.
            </p>
            <p class="text-sm text-stone-500">
                Commande {{ $commande->reference }}
            </p>
        </div>

        {{-- Raisons possibles --}}
        <div class="bg-amber-50 border-2 border-amber-200 rounded-3xl p-6 mb-6">
            <h2 class="font-black text-amber-900 mb-4 flex items-center gap-2">
                <x-icon name="alert-triangle" size="5" />
                Raisons possibles
            </h2>
            <ul class="space-y-2 text-sm text-amber-900">
                <li class="flex items-start gap-2">
                    <x-icon name="dot" size="4" class="mt-0.5 flex-shrink-0" />
                    <span>Solde insuffisant sur votre compte Mobile Money</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-icon name="dot" size="4" class="mt-0.5 flex-shrink-0" />
                    <span>Numéro de téléphone incorrect ou non reconnu</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-icon name="dot" size="4" class="mt-0.5 flex-shrink-0" />
                    <span>Transaction annulée ou expirée</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-icon name="dot" size="4" class="mt-0.5 flex-shrink-0" />
                    <span>Problème technique temporaire</span>
                </li>
            </ul>
        </div>

        {{-- Que faire --}}
        <div class="bg-blue-50 border-2 border-blue-200 rounded-3xl p-6 mb-6">
            <h2 class="font-black text-blue-900 mb-4 flex items-center gap-2">
                <x-icon name="lightbulb" size="5" />
                Que faire ?
            </h2>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">1</div>
                    <p class="text-sm text-blue-900">Vérifiez votre solde Mobile Money</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">2</div>
                    <p class="text-sm text-blue-900">Vérifiez que votre numéro est bien actif</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-blue-600 text-white flex items-center justify-center flex-shrink-0 font-bold text-sm">3</div>
                    <p class="text-sm text-blue-900">Réessayez le paiement</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('paiement.checkout', $commande) }}"
               class="flex-1 bg-gradient-to-r from-green-800 to-green-600 hover:from-green-900 hover:to-green-700 text-white font-black py-4 rounded-2xl shadow-lg transition text-center flex items-center justify-center gap-2">
                <x-icon name="refresh-cw" size="5" />
                Réessayer le paiement
            </a>
            <a href="{{ route('commandes.show', $commande) }}"
               class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-800 font-bold py-4 rounded-2xl transition text-center flex items-center justify-center gap-2">
                <x-icon name="eye" size="5" />
                Voir ma commande
            </a>
        </div>

        {{-- Contact --}}
        <div class="text-center mt-8 text-sm text-stone-500">
            <p>
                Problème persistant ?
                <a href="mailto:contact@transformcollines.bj" class="text-green-700 font-bold hover:underline">
                    Contactez le support
                </a>
            </p>
        </div>
    </div>
</x-app-layout>