<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscription — Transform'Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen">

    @php
        $config = match($role) {
            'population' => [
                'titre'     => 'Créer mon compte',
                'sousTitre' => 'Achetez auprès des producteurs des Collines',
                'icone'     => 'shopping-basket',
                'couleur'   => 'blue',
                'gradient'  => 'from-blue-800 via-blue-700 to-blue-600',
                'accent'    => 'from-blue-400 to-blue-500',
                'avantages' => [
                    'Accès immédiat au marché',
                    'Commandez en 2 clics',
                    'Suivi de vos commandes',
                ],
            ],
            'producteur' => [
                'titre'     => 'Devenir producteur',
                'sousTitre' => 'Vendez vos récoltes aux meilleurs prix',
                'icone'     => 'sprout',
                'couleur'   => 'green',
                'gradient'  => 'from-green-900 via-green-800 to-green-700',
                'accent'    => 'from-lime-400 to-lime-500',
                'avantages' => [
                    'Publiez vos offres',
                    'Répondez aux besoins des unités',
                    'Gagnez en visibilité',
                ],
            ],
            'unite_transformation' => [
                'titre'     => 'Inscrire mon unité',
                'sousTitre' => 'Sécurisez votre approvisionnement',
                'icone'     => 'factory',
                'couleur'   => 'amber',
                'gradient'  => 'from-amber-900 via-amber-800 to-amber-700',
                'accent'    => 'from-amber-400 to-amber-500',
                'avantages' => [
                    'Publiez vos besoins',
                    'Trouvez des producteurs fiables',
                    'Suivez vos livraisons',
                ],
            ],
            default => [
                'titre'     => 'Créer un compte',
                'sousTitre' => 'Rejoignez Transform\'Collines',
                'icone'     => 'user',
                'couleur'   => 'green',
                'gradient'  => 'from-green-900 via-green-800 to-green-700',
                'accent'    => 'from-lime-400 to-lime-500',
                'avantages' => [],
            ],
        };
    @endphp

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

        {{-- Colonne gauche : Illustration --}}
        <div class="hidden lg:flex relative bg-gradient-to-br {{ $config['gradient'] }} text-white p-12 flex-col justify-between overflow-hidden">

            <div class="absolute -top-32 -right-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 right-1/4 w-24 h-24 bg-gradient-to-br {{ $config['accent'] }} rounded-full blur-2xl opacity-30"></div>

            <div class="relative flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/20">
                    <x-icon name="sprout" size="6" class="text-white" />
                </div>
                <span class="text-xl font-bold">Transform'Collines</span>
            </div>

            <div class="relative">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs font-semibold uppercase tracking-wider mb-6">
                    <span class="w-1.5 h-1.5 rounded-full bg-lime-400 animate-pulse"></span>
                    Rejoignez la plateforme
                </div>

                <h2 class="text-4xl xl:text-5xl font-black leading-tight mb-6">
                    {{ $config['titre'] }}
                </h2>

                <p class="text-lg text-white/80 mb-10 leading-relaxed">
                    {{ $config['sousTitre'] }}
                </p>

                <ul class="space-y-4">
                    @foreach($config['avantages'] as $avantage)
                        <li class="flex items-center gap-3">
                            <div class="w-6 h-6 rounded-full bg-white/15 backdrop-blur flex items-center justify-center flex-shrink-0">
                                <x-icon name="check" size="3.5" class="text-lime-400" />
                            </div>
                            <span class="text-white/90">{{ $avantage }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="relative text-xs text-white/50">
                © 2026 Transform'Collines · Développé par Ulrich KASSIN et Priscille KINDOHO
            </div>
        </div>

        {{-- Colonne droite : Formulaire --}}
        <div class="flex flex-col justify-center p-6 sm:p-10 lg:p-12 relative bg-[#fef9f3]">

            <div class="absolute inset-0 grain opacity-40 pointer-events-none"></div>

            <div class="lg:hidden mb-8 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center">
                    <x-icon name="sprout" size="6" class="text-white" />
                </div>
                <span class="text-xl font-black text-green-900">Transform'Collines</span>
            </div>

            <div class="relative max-w-md w-full mx-auto lg:mx-0">

                <a href="{{ route('register') }}"
                   class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
                    <x-icon name="arrow-left" size="4" />
                    Changer de profil
                </a>

                <div class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-black text-stone-900 mb-2 leading-tight">
                        {{ $config['titre'] }}
                    </h1>
                    <p class="text-stone-500">{{ $config['sousTitre'] }}</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-6 text-sm">
                        <p class="font-bold mb-1">Veuillez corriger :</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="type_compte" value="{{ $role }}">

                    {{-- Nom --}}
                    <div>
                        <label class="block text-sm font-semibold text-stone-800 mb-2">
                            {{ $role === 'unite_transformation' ? 'Nom de la structure' : 'Nom complet' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nom" value="{{ old('nom') }}" required
                               placeholder="{{ $role === 'unite_transformation' ? 'Ex. Unité de transformation de Paouignan' : 'Ex. Fifamè AGBODJAN' }}"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                    </div>

                    @if($role === 'population')
                        <div>
                            <label class="block text-sm font-semibold text-stone-800 mb-2">
                                Adresse e-mail <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   placeholder="vous@exemple.com"
                                   class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                        </div>
                    @endif

                    @if($role === 'unite_transformation')
                        <div>
                            <label class="block text-sm font-semibold text-stone-800 mb-2">
                                Nom du responsable <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nom_responsable" value="{{ old('nom_responsable') }}" required
                                   placeholder="Ex. Jean HOUNKPATIN"
                                   class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                        </div>
                    @endif

                    {{-- Téléphone --}}
                    <div>
                        <label class="block text-sm font-semibold text-stone-800 mb-2">
                            Téléphone <span class="text-red-500">*</span>
                        </label>
                        <input type="tel" name="telephone" value="{{ old('telephone') }}" required
                               pattern="^(\+229|229)?0?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}$"
                               title="Veuillez entrer un numéro béninois valide (ex: 0197000001)"
                               placeholder="01 97 00 00 01"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                        <p class="text-xs text-stone-500 mt-1">Format béninois (ex: 0197000001 ou +229 01 97 00 00 01)</p>
                    </div>

                    @if(in_array($role, ['producteur', 'unite_transformation']))
                        <div>
                            <label class="block text-sm font-semibold text-stone-800 mb-2">
                                Numéro WhatsApp
                                <span class="text-stone-400 text-xs font-normal">(optionnel)</span>
                            </label>
                            <input type="tel" name="whatsapp" value="{{ old('whatsapp') }}"
                                   pattern="^(\+229|229)?0?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}$"
                                   title="Veuillez entrer un numéro béninois valide"
                                   placeholder="01 97 00 00 01"
                                   class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-stone-800 mb-2">
                                    Commune <span class="text-red-500">*</span>
                                </label>
                                <select name="commune_id" required
                                        class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                                    <option value="">— Choisir —</option>
                                    @foreach($communes as $c)
                                        <option value="{{ $c->id }}" @selected(old('commune_id') == $c->id)>{{ $c->nom }}</option>
                                    @endforeach
                                </select>
                            </div>

                            @if($role === 'producteur')
                                <div>
                                    <label class="block text-sm font-semibold text-stone-800 mb-2">
                                        Village
                                        <span class="text-stone-400 text-xs font-normal">(optionnel)</span>
                                    </label>
                                    <input type="text" name="village" value="{{ old('village') }}"
                                           placeholder="Ex. Savalou-Agbado"
                                           class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($role === 'unite_transformation')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-stone-800 mb-2">
                                    RCCM
                                    <span class="text-stone-400 text-xs font-normal">(optionnel)</span>
                                </label>
                                <input type="text" name="rccm" value="{{ old('rccm') }}"
                                       placeholder="RB/XXX/2026"
                                       class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-stone-800 mb-2">
                                    IFU
                                    <span class="text-stone-400 text-xs font-normal">(optionnel)</span>
                                </label>
                                <input type="text" name="ifu" value="{{ old('ifu') }}"
                                       placeholder="1234567890"
                                       class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-sm">
                            </div>
                        </div>
                    @endif

                    {{-- Mot de passe --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-stone-800 mb-2">
                                Mot de passe <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="mot_de_passe" required minlength="6"
                                   class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-800 mb-2">
                                Confirmer <span class="text-red-500">*</span>
                            </label>
                            <input type="password" name="mot_de_passe_confirmation" required minlength="6"
                                   class="w-full rounded-2xl border-2 border-stone-200 bg-white/70 px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                        </div>
                    </div>

                    @if($role !== 'population')
                        <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-xl p-4">
                            <div class="flex items-start gap-3">
                                <x-icon name="shield-check" size="5" class="text-amber-600 flex-shrink-0 mt-0.5" />
                                <div class="text-sm">
                                    <p class="font-bold text-amber-900">Validation requise</p>
                                    <p class="text-amber-800 mt-0.5 leading-relaxed">
                                        Votre compte sera examiné par un administrateur avant activation. Vous recevrez une notification dès qu'il sera validé.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <button type="submit"
                            class="w-full bg-green-800 hover:bg-green-900 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-green-800/20 hover:shadow-xl hover:shadow-green-800/30 flex items-center justify-center gap-2 text-base">
                        <x-icon name="user-plus" size="5" />
                        Créer mon compte
                    </button>
                </form>

                <p class="text-center text-sm text-stone-500 mt-8 pt-6 border-t border-stone-200">
                    Déjà inscrit ?
                    <a href="{{ route('login') }}" class="text-green-800 font-bold hover:underline">
                        Se connecter
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>