<x-guest-layout title="Inscription">
    <div class="w-full max-w-3xl bg-white rounded-2xl shadow-lg border border-gray-100 p-8"
         x-data="{ type: '{{ old('type_compte', 'producteur') }}' }">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Créer un compte</h1>
            <p class="text-gray-500 text-sm mt-1">Rejoignez la plateforme en 2 minutes</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-lg mb-4 text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            {{-- Type de compte --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Je suis…</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="cursor-pointer border-2 rounded-xl p-4 transition"
                           :class="type === 'producteur' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="type_compte" value="producteur" x-model="type" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-lg bg-green-100 text-green-700 flex items-center justify-center">
                                <x-icon name="sprout" size="6" />
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-sm">Producteur / Coopérative</div>
                                <div class="text-xs text-gray-500">Je vends mes récoltes</div>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-xl p-4 transition"
                           :class="type === 'unite_transformation' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="type_compte" value="unite_transformation" x-model="type" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                                <x-icon name="factory" size="6" />
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 text-sm">Unité de transformation</div>
                                <div class="text-xs text-gray-500">J'achète des matières premières</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Identité --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet / Raison sociale</label>
                    <input type="text" name="nom" value="{{ old('nom') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                    <input type="text" name="telephone" value="{{ old('telephone') }}" placeholder="+22997000001"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Commune</label>
                    <select name="commune_id" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required>
                        <option value="">— Choisir —</option>
                        @foreach ($communes as $c)
                            <option value="{{ $c->id }}" @selected(old('commune_id') == $c->id)>{{ $c->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Village / Quartier</label>
                    <input type="text" name="village" value="{{ old('village') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>
            </div>

            {{-- Produits --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Produit(s) <span x-text="type === 'producteur' ? 'cultivé(s)' : 'transformé(s)'"></span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach ($produits as $p)
                        <label class="flex items-center gap-2 p-2.5 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <input type="checkbox" name="produits[]" value="{{ $p->id }}"
                                   class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                   @checked(in_array($p->id, old('produits', [])))>
                            <span class="text-sm">{{ $p->nom }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- Producteur --}}
            <div x-show="type === 'producteur'" x-transition
                 class="bg-green-50 border border-green-100 rounded-xl p-4 space-y-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="est_cooperative" value="1"
                           class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium">Je m'inscris au nom d'une coopérative</span>
                </label>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre approximatif de membres</label>
                    <input type="number" name="nombre_membres" min="1"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                </div>
            </div>

            {{-- Unité --}}
            <div x-show="type === 'unite_transformation'" x-transition
                 class="bg-amber-50 border border-amber-100 rounded-xl p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Capacité de traitement (tonnes)</label>
                <input type="number" name="capacite" step="0.01" min="0"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
            </div>

            {{-- Mots de passe --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <input type="password" name="mot_de_passe"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
                    <input type="password" name="mot_de_passe_confirmation"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" required>
                </div>
            </div>

            <button type="submit"
                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-lg font-medium transition shadow-sm flex items-center justify-center gap-2">
                <x-icon name="user-plus" size="5" />
                Créer mon compte
            </button>
        </form>

        <p class="text-center text-sm text-gray-600 mt-6">
            Déjà inscrit ?
            <a href="{{ route('login') }}" class="text-green-700 font-semibold hover:underline">Se connecter</a>
        </p>
    </div>
</x-guest-layout>