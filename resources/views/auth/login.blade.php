<x-guest-layout title="Connexion">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg border border-gray-100 p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Connexion</h1>
            <p class="text-gray-500 text-sm mt-1">Accédez à votre espace</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg mb-4 text-sm flex items-start gap-2">
                <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icon name="phone" size="5" class="text-gray-400" />
                    </div>
                    <input type="text" name="telephone" value="{{ old('telephone') }}"
                           placeholder="+22997000001"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" required autofocus>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icon name="lock" size="5" class="text-gray-400" />
                    </div>
                    <input type="password" name="mot_de_passe"
                           class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none transition" required>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                Se souvenir de moi
            </label>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-lg font-medium transition shadow-sm flex items-center justify-center gap-2">
                <x-icon name="log-in" size="5" />
                Se connecter
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Pas encore de compte ?
            <a href="{{ route('register') }}" class="text-green-700 font-semibold hover:underline">S'inscrire</a>
        </p>

        <div class="mt-6 pt-5 border-t border-gray-100">
            <p class="text-xs font-semibold text-gray-600 mb-2 flex items-center gap-1.5">
                <x-icon name="key" size="4" />
                Comptes de test (mdp : password)
            </p>
            <div class="space-y-1 text-xs text-gray-500 font-mono">
                <p>Producteur : +22997000001</p>
                <p>Unité : +22996000001</p>
                <p>MAEP : +22991000000</p>
            </div>
        </div>
    </div>
</x-guest-layout>