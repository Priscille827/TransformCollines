<x-app-layout title="Mon profil">
    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2">Mon profil</h1>
            <p class="text-stone-500">Gérez vos informations personnelles</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Carte d'identité --}}
        <div class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white rounded-3xl p-6 mb-6 relative overflow-hidden">
            <div class="absolute -top-20 -right-20 w-60 h-60 bg-lime-400/20 rounded-full blur-3xl"></div>

            <div class="relative flex items-center gap-5">
                <div class="w-20 h-20 rounded-3xl bg-white/15 backdrop-blur border-2 border-white/30 flex items-center justify-center font-black text-3xl flex-shrink-0">
                    {{ strtoupper(substr($user->nom, 0, 1)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-black mb-1">{{ $user->nom }}</h2>
                    <p class="text-sm text-green-100 flex items-center gap-2 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 bg-white/15 backdrop-blur border border-white/20 rounded-full text-xs font-bold uppercase tracking-wider">
                            {{ ucfirst(str_replace('_', ' ', $user->type_compte)) }}
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <x-icon name="map-pin" size="3.5" />
                            {{ $user->commune->nom ?? '—' }}
                        </span>
                    </p>
                    <p class="text-xs text-green-100/70 mt-2 font-mono">{{ $user->telephone }}</p>
                </div>
            </div>
        </div>

        {{-- Informations personnelles --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-stone-100">
                <div class="w-11 h-11 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                    <x-icon name="user" size="5" />
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-lg">Informations personnelles</h3>
                    <p class="text-xs text-stone-500">Vos coordonnées et localisation</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-5">
                @csrf @method('PATCH')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nom" value="{{ old('nom', $user->nom) }}" required
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">Téléphone</label>
                        <input type="text" value="{{ $user->telephone }}" disabled
                               class="w-full rounded-2xl border-2 border-stone-200 bg-stone-50 px-4 py-3 text-stone-500 font-mono cursor-not-allowed">
                        <p class="text-xs text-stone-500 mt-1">Le téléphone ne peut pas être modifié (identifiant unique).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}"
                               placeholder="+22997000001"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">
                            Commune <span class="text-red-500">*</span>
                        </label>
                        <select name="commune_id" required
                                class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                            @foreach($communes as $c)
                                <option value="{{ $c->id }}" @selected(old('commune_id', $user->commune_id) == $c->id)>
                                    {{ $c->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">Village / Quartier</label>
                        <input type="text" name="village" value="{{ old('village', $user->village) }}"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                    <button type="submit"
                            class="px-6 py-3 bg-green-800 hover:bg-green-900 text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                        <x-icon name="check-circle-2" size="5" />
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- Changer le mot de passe --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-stone-100">
                <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center">
                    <x-icon name="lock" size="5" />
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-lg">Changer le mot de passe</h3>
                    <p class="text-xs text-stone-500">Sécurisez votre compte</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Mot de passe actuel <span class="text-red-500">*</span>
                    </label>
                    <input type="password" name="current_password" required
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">
                            Nouveau mot de passe <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required minlength="6"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">
                            Confirmer <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required minlength="6"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                    <button type="submit"
                            class="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-2xl shadow-lg shadow-amber-600/20 transition flex items-center gap-2">
                        <x-icon name="lock" size="5" />
                        Changer le mot de passe
                    </button>
                </div>
            </form>
        </div>

        {{-- Informations selon le rôle --}}
        @if($user->isProducteur() || $user->isUnite())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b border-stone-100">
                    <div class="w-11 h-11 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center">
                        <x-icon name="file-badge" size="5" />
                    </div>
                    <div>
                        <h3 class="font-black text-stone-900 text-lg">Mon dossier professionnel</h3>
                        <p class="text-xs text-stone-500">Informations administratives</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    @if($user->isProducteur())
                        <div class="bg-stone-50 rounded-2xl p-4">
                            <p class="text-xs text-stone-500 font-bold uppercase tracking-wider mb-1">NPI</p>
                            <p class="font-bold text-stone-900 font-mono">{{ $user->npi ?? '—' }}</p>
                        </div>
                    @endif

                    @if($user->isUnite())
                        <div class="bg-stone-50 rounded-2xl p-4">
                            <p class="text-xs text-stone-500 font-bold uppercase tracking-wider mb-1">RCCM</p>
                            <p class="font-bold text-stone-900 font-mono">{{ $user->rccm ?? '—' }}</p>
                        </div>
                        <div class="bg-stone-50 rounded-2xl p-4">
                            <p class="text-xs text-stone-500 font-bold uppercase tracking-wider mb-1">IFU</p>
                            <p class="font-bold text-stone-900 font-mono">{{ $user->ifu ?? '—' }}</p>
                        </div>
                    @endif

                    <div class="bg-stone-50 rounded-2xl p-4">
                        <p class="text-xs text-stone-500 font-bold uppercase tracking-wider mb-1">Document d'identification</p>
                        @if($user->aUnDocument())
                            <p class="font-bold text-stone-900">
                                {{ strtoupper($user->document_type) }}
                                @if($user->document_verifie)
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-green-700 ml-2">
                                        <x-icon name="check-circle-2" size="3" />
                                        Vérifié
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-bold text-amber-700 ml-2">
                                        <x-icon name="clock" size="3" />
                                        En attente
                                    </span>
                                @endif
                            </p>
                        @else
                            <p class="text-sm text-stone-400 italic">Aucun document</p>
                        @endif
                    </div>
                </div>

                @if(!$user->aUnDocument() && $user->isEnAttente())
                    <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl p-4 flex items-start gap-3">
                        <x-icon name="alert-triangle" size="5" class="text-amber-600 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="font-bold text-amber-900 mb-1">Document d'identification manquant</p>
                            <p class="text-sm text-amber-800 mb-2">Fournissez votre CIP (ou passeport) pour compléter votre dossier.</p>
                            <a href="{{ route('documents.index') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition">
                                <x-icon name="upload" size="4" />
                                Fournir mon document
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Zone de danger --}}
        <div class="bg-white rounded-3xl shadow-sm border-2 border-red-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-2xl bg-red-100 text-red-700 flex items-center justify-center">
                    <x-icon name="alert-triangle" size="5" />
                </div>
                <div>
                    <h3 class="font-black text-stone-900 text-lg">Zone de danger</h3>
                    <p class="text-xs text-stone-500">Actions irréversibles</p>
                </div>
            </div>

            <p class="text-sm text-stone-600 mb-4">
                La suppression de votre compte entraînera la perte définitive de toutes vos données (stock, commandes, historique).
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}"
                  onsubmit="return confirm('⚠️ Êtes-vous SÛR de vouloir supprimer votre compte ? Cette action est irréversible.')">
                @csrf @method('DELETE')

                <div class="mb-4">
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Tapez votre mot de passe pour confirmer
                    </label>
                    <input type="password" name="password" required
                           placeholder="Mot de passe"
                           class="w-full rounded-2xl border-2 border-red-200 bg-white px-4 py-3 focus:ring-4 focus:ring-red-500/20 focus:border-red-500 outline-none transition">
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-2xl shadow-lg shadow-red-600/20 transition">
                    <x-icon name="trash-2" size="5" />
                    Supprimer mon compte
                </button>
            </form>
        </div>
    </div>
</x-app-layout>