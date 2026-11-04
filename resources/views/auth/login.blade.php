<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion — Transform'Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen">

    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2">

        <div class="hidden lg:flex relative bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white p-12 flex-col justify-between overflow-hidden">

            <div class="absolute -top-32 -right-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-20 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl"></div>

            <div class="relative flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/20">
                    <x-icon name="sprout" size="6" class="text-white" />
                </div>
                <span class="text-xl font-bold">Transform'Collines</span>
            </div>

            <div class="relative">
                <h2 class="text-4xl xl:text-5xl font-black leading-tight mb-6">
                    Bienvenue
                    <span class="block text-lime-300">à la maison.</span>
                </h2>
                <p class="text-lg text-white/80 mb-8 leading-relaxed max-w-md">
                    Retrouvez vos besoins, vos offres, vos commandes et vos produits en un seul endroit.
                </p>

                <div class="grid grid-cols-3 gap-6 max-w-md">
                    <div>
                        <p class="text-3xl font-black text-lime-300">6</p>
                        <p class="text-xs text-white/70 uppercase tracking-wider mt-1">Communes</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-lime-300">5</p>
                        <p class="text-xs text-white/70 uppercase tracking-wider mt-1">Filières</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-lime-300">100%</p>
                        <p class="text-xs text-white/70 uppercase tracking-wider mt-1">Local</p>
                    </div>
                </div>
            </div>

            <div class="relative text-xs text-white/50">
                © 2026 Transform'Collines · Développé par Ulrich KASSIN et Priscille KINDOHO
            </div>
        </div>

        <div class="flex flex-col justify-center p-6 sm:p-10 lg:p-12 bg-[#fef9f3]">

            <div class="lg:hidden mb-8 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center">
                    <x-icon name="sprout" size="6" class="text-white" />
                </div>
                <span class="text-xl font-black text-green-900">Transform'Collines</span>
            </div>

            <div class="max-w-md w-full mx-auto lg:mx-0">

                <div class="mb-8">
                    <h1 class="text-3xl sm:text-4xl font-black text-stone-900 mb-2 leading-tight">
                        Content de vous revoir
                    </h1>
                    <p class="text-stone-500">Connectez-vous avec votre téléphone</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-xl mb-6 text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-stone-800 mb-2">Téléphone</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <x-icon name="phone" size="5" class="text-stone-400" />
                            </div>
                            <input type="tel" name="telephone" value="{{ old('telephone') }}"
                                   placeholder="01 97 00 00 01"
                                   pattern="^(\+229|229)?0?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}\s?[0-9]{2}$"
                                   title="Veuillez entrer un numéro béninois valide"
                                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-stone-200 bg-white/70 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono" required autofocus>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-800 mb-2">Mot de passe</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <x-icon name="lock" size="5" class="text-stone-400" />
                            </div>
                            <input type="password" name="mot_de_passe"
                                   class="w-full pl-11 pr-4 py-3.5 rounded-2xl border-2 border-stone-200 bg-white/70 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium" required>
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-stone-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-stone-300 text-green-700 focus:ring-green-500">
                        Se souvenir de moi
                    </label>

                    <button type="submit"
                            class="w-full bg-green-800 hover:bg-green-900 text-white font-bold py-4 rounded-2xl transition shadow-lg shadow-green-800/20 hover:shadow-xl flex items-center justify-center gap-2 text-base">
                        <x-icon name="log-in" size="5" />
                        Se connecter
                    </button>
                </form>

                <p class="text-center text-sm text-stone-500 mt-8">
                    Pas encore de compte ?
                    <a href="{{ route('register') }}" class="text-green-800 font-bold hover:underline">
                        S'inscrire
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>