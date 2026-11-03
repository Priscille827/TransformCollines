<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Créer un compte — Transform'Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }
        .float-anim-slow { animation: float 6s ease-in-out infinite; }
        .gradient-text {
            background: linear-gradient(135deg, #0f5132 0%, #10b981 50%, #a3e635 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .grain {
            background-image: radial-gradient(circle at 1px 1px, rgba(15,81,50,0.05) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen overflow-x-hidden">

    {{-- Formes décoratives en arrière-plan --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-gradient-to-br from-green-200/40 to-lime-100/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-60 -left-40 w-[600px] h-[600px] bg-gradient-to-tr from-amber-100/40 to-green-100/30 rounded-full blur-3xl"></div>
        <div class="absolute top-1/3 right-1/4 w-32 h-32 bg-lime-300/20 rounded-full blur-2xl float-anim"></div>
    </div>

    <div class="relative min-h-screen flex flex-col items-center justify-center p-4 py-12">

        {{-- Logo --}}
        <a href="/" class="mb-12 flex items-center gap-3 group">
            <div class="relative">
                <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-lime-400 rounded-2xl blur-lg opacity-50 group-hover:opacity-80 transition"></div>
                <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center shadow-xl">
                    <x-icon name="sprout" size="8" class="text-white" />
                </div>
            </div>
            <span class="text-3xl font-black text-green-900 tracking-tight">Transform'Collines</span>
        </a>

        <div class="w-full max-w-5xl">

            {{-- Header --}}
            <div class="text-center mb-14">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-100/70 backdrop-blur rounded-full text-xs font-bold text-green-800 uppercase tracking-wider mb-6">
                    <span class="w-2 h-2 rounded-full bg-lime-500 animate-pulse"></span>
                    Inscription gratuite
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-stone-900 leading-[1.05] mb-6 tracking-tight">
                    Qui êtes-vous
                    <span class="gradient-text">aujourd'hui ?</span>
                </h1>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto leading-relaxed">
                    Choisissez le profil qui correspond à votre activité pour commencer l'aventure
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- POPULATION --}}
                <a href="{{ route('register', ['role' => 'population']) }}"
                   class="group relative bg-white rounded-3xl shadow-[0_4px_24px_-8px_rgba(15,81,50,0.1)] hover:shadow-[0_20px_60px_-15px_rgba(59,130,246,0.4)] transition-all duration-300 p-7 border border-stone-100 hover:border-blue-200 hover:-translate-y-1 overflow-hidden">

                    {{-- Fond décoratif --}}
                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-blue-100 to-transparent rounded-full blur-2xl opacity-60 group-hover:opacity-100 transition"></div>

                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center mb-6 shadow-lg shadow-blue-200 group-hover:scale-110 transition-transform">
                            <x-icon name="shopping-basket" size="8" />
                        </div>

                        <div class="mb-6">
                            <span class="inline-block px-2.5 py-1 bg-blue-50 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-md mb-3">
                                Acheteur
                            </span>
                            <h2 class="text-2xl font-bold text-stone-900 mb-3">Population</h2>
                            <p class="text-sm text-stone-500 leading-relaxed">
                                Je veux acheter des produits agricoles frais directement auprès des producteurs des Collines.
                            </p>
                        </div>

                        <ul class="space-y-2 mb-6 text-xs text-stone-600">
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-blue-600" />
                                Accès au marché
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-blue-600" />
                                Commande simplifiée
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-blue-600" />
                                Activation immédiate
                            </li>
                        </ul>

                        <div class="flex items-center gap-2 text-blue-700 font-bold text-sm pt-5 border-t border-stone-100 group-hover:gap-3 transition-all">
                            Créer mon compte
                            <x-icon name="arrow-right" size="4" class="group-hover:translate-x-1 transition" />
                        </div>
                    </div>
                </a>

                {{-- PRODUCTEUR --}}
                <a href="{{ route('register', ['role' => 'producteur']) }}"
                   class="group relative bg-gradient-to-br from-green-800 to-green-700 rounded-3xl shadow-[0_20px_60px_-15px_rgba(15,81,50,0.5)] hover:shadow-[0_30px_80px_-15px_rgba(15,81,50,0.6)] transition-all duration-300 p-7 hover:-translate-y-2 overflow-hidden">

                    <div class="absolute -top-20 -right-20 w-60 h-60 bg-lime-400/20 rounded-full blur-3xl"></div>
                    <div class="absolute top-4 right-4 px-3 py-1 bg-lime-400 text-green-900 text-[10px] font-black uppercase tracking-wider rounded-full">
                        Populaire
                    </div>

                    <div class="relative text-white">
                        <div class="w-16 h-16 rounded-2xl bg-lime-400 text-green-900 flex items-center justify-center mb-6 shadow-lg shadow-lime-500/30 group-hover:scale-110 transition-transform float-anim-slow">
                            <x-icon name="sprout" size="8" />
                        </div>

                        <div class="mb-6">
                            <span class="inline-block px-2.5 py-1 bg-white/15 backdrop-blur text-lime-200 text-[10px] font-bold uppercase tracking-wider rounded-md mb-3">
                                Vendeur
                            </span>
                            <h2 class="text-2xl font-bold mb-3">Producteur</h2>
                            <p class="text-sm text-green-100 leading-relaxed">
                                Je cultive la terre et je veux vendre mes produits aux unités et à la population.
                            </p>
                        </div>

                        <ul class="space-y-2 mb-6 text-xs text-green-50">
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-lime-400" />
                                Publier des offres
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-lime-400" />
                                Répondre aux besoins
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-lime-400" />
                                Gérer mes stocks
                            </li>
                        </ul>

                        <div class="flex items-center gap-2 text-lime-300 font-bold text-sm pt-5 border-t border-white/20 group-hover:gap-3 transition-all">
                            Devenir producteur
                            <x-icon name="arrow-right" size="4" class="group-hover:translate-x-1 transition" />
                        </div>
                    </div>
                </a>

                {{-- UNITÉ DE TRANSFORMATION --}}
                <a href="{{ route('register', ['role' => 'unite_transformation']) }}"
                   class="group relative bg-white rounded-3xl shadow-[0_4px_24px_-8px_rgba(15,81,50,0.1)] hover:shadow-[0_20px_60px_-15px_rgba(245,158,11,0.4)] transition-all duration-300 p-7 border border-stone-100 hover:border-amber-200 hover:-translate-y-1 overflow-hidden">

                    <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-br from-amber-100 to-transparent rounded-full blur-2xl opacity-60 group-hover:opacity-100 transition"></div>

                    <div class="relative">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center mb-6 shadow-lg shadow-amber-200 group-hover:scale-110 transition-transform">
                            <x-icon name="factory" size="8" />
                        </div>

                        <div class="mb-6">
                            <span class="inline-block px-2.5 py-1 bg-amber-50 text-amber-700 text-[10px] font-bold uppercase tracking-wider rounded-md mb-3">
                                Transformateur
                            </span>
                            <h2 class="text-2xl font-bold text-stone-900 mb-3">Unité de transformation</h2>
                            <p class="text-sm text-stone-500 leading-relaxed">
                                Je transforme les produits et je cherche des matières premières fiables.
                            </p>
                        </div>

                        <ul class="space-y-2 mb-6 text-xs text-stone-600">
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-amber-600" />
                                Publier des besoins
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-amber-600" />
                                Suivi des approvisionnements
                            </li>
                            <li class="flex items-center gap-2">
                                <x-icon name="check" size="3" class="text-amber-600" />
                                Vendre les produits finis
                            </li>
                        </ul>

                        <div class="flex items-center gap-2 text-amber-700 font-bold text-sm pt-5 border-t border-stone-100 group-hover:gap-3 transition-all">
                            Inscrire mon unité
                            <x-icon name="arrow-right" size="4" class="group-hover:translate-x-1 transition" />
                        </div>
                    </div>
                </a>
            </div>

            <p class="text-center text-sm text-stone-500 mt-12">
                Déjà inscrit ?
                <a href="{{ route('login') }}" class="text-green-800 font-bold hover:underline ml-1">
                    Se connecter
                </a>
            </p>
        </div>
    </div>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>