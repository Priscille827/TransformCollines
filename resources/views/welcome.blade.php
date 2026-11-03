<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transform'Collines — Le marché agricole des Collines</title>
    <meta name="description" content="Plateforme de mise en relation entre producteurs et unités de transformation agricole des Collines (Bénin).">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #0f5132 0%, #16a34a 50%, #84cc16 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .grain {
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.08) 1px, transparent 0);
            background-size: 24px 24px;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-12px); }
        }
        .float-anim { animation: float 5s ease-in-out infinite; }
        .float-anim-slow { animation: float 7s ease-in-out infinite; }

        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }

        /* Animation des points connectés (réseau) */
        @keyframes ping-slow {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(2.5); opacity: 0; }
        }
        .ping-slow { animation: ping-slow 2.5s cubic-bezier(0, 0, 0.2, 1) infinite; }

        @keyframes fade-in-up {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .fade-in-up { animation: fade-in-up 0.6s ease-out forwards; }

        /* Animation pluie de particules */
        @keyframes rain {
            0% { transform: translateY(-100%); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(100vh); opacity: 0; }
        }
        .rain-drop { animation: rain 8s linear infinite; }

        /* Barre de progression animée */
        @keyframes progress-fill {
            0% { width: 0%; }
            100% { width: 100%; }
        }
        .progress-fill { animation: progress-fill 3s ease-out infinite; }

        /* Rotation lente */
        @keyframes spin-slow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin-slow { animation: spin-slow 20s linear infinite; }

        /* Carrousel témoignages */
        @keyframes scroll-left {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .carousel-track {
            display: flex;
            animation: scroll-left 40s linear infinite;
            width: max-content;
        }
        .carousel-track:hover {
            animation-play-state: paused;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen overflow-x-hidden">

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- NAV --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">

            <a href="/" class="flex items-center gap-2.5 group flex-shrink-0">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-lime-400 rounded-xl blur opacity-40 group-hover:opacity-60 transition"></div>
                    <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center shadow-md">
                        <x-icon name="sprout" size="6" class="text-white" />
                    </div>
                </div>
                <span class="font-black text-green-900 hidden sm:inline text-lg tracking-tight">Transform'Collines</span>
            </a>

            <div class="hidden md:flex items-center gap-1">
                <a href="#solution" class="px-4 py-2 text-sm font-bold text-stone-700 hover:text-green-800 hover:bg-stone-100 rounded-xl transition">
                    Fonctionnalités
                </a>
                <a href="#how" class="px-4 py-2 text-sm font-bold text-stone-700 hover:text-green-800 hover:bg-stone-100 rounded-xl transition">
                    Comment ça marche
                </a>
                <a href="{{ route('marketplace.index') }}" class="px-4 py-2 text-sm font-bold text-stone-700 hover:text-green-800 hover:bg-stone-100 rounded-xl transition flex items-center gap-1.5">
                    <x-icon name="shopping-basket" size="4" />
                    Marché
                </a>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-sm transition">
                        <x-icon name="layout-dashboard" size="4" />
                        <span class="hidden sm:inline">Mon espace</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-sm transition">
                        <x-icon name="user-plus" size="4" />
                        <span class="hidden sm:inline">S'inscrire</span>
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- HERO --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="relative pt-32 pb-20 sm:pt-40 sm:pb-24 px-4 overflow-hidden bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white">
        <div class="absolute inset-0 grain opacity-40"></div>
        <div class="absolute -top-40 -right-40 w-[600px] h-[600px] bg-lime-400/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-green-400/20 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                {{-- Texte --}}
                <div>
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur border border-white/20 rounded-full text-xs font-black uppercase tracking-widest mb-6">
                        <span class="w-2 h-2 rounded-full bg-lime-400 pulse-soft"></span>
                        Plateforme agricole des Collines
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-6 leading-[1.05] tracking-tight">
                        Le marché agricole<br>
                        <span class="text-lime-300">des Collines.</span>
                    </h1>

                    <p class="text-lg text-green-50/90 mb-8 leading-relaxed max-w-xl">
                        Transform'Collines relie directement les <strong class="text-white">producteurs</strong> et les <strong class="text-white">unités de transformation</strong> du département.
                        Un approvisionnement fiable, des débouchés garantis, zéro intermédiaire.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 mb-8">
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-4 bg-white text-green-800 rounded-2xl font-black shadow-lg hover:bg-green-50 transition">
                            Commencer gratuitement
                            <x-icon name="arrow-right" size="5" />
                        </a>
                        <a href="{{ route('marketplace.index') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-4 bg-lime-400 text-green-900 rounded-2xl font-black shadow-lg hover:bg-lime-300 transition">
                            <x-icon name="shopping-basket" size="5" />
                            Voir le marché
                        </a>
                    </div>

                    <div class="flex flex-wrap items-center gap-6 text-sm text-green-100">
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle-2" size="4" class="text-lime-400" />
                            6 communes couvertes
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle-2" size="4" class="text-lime-400" />
                            Gratuit pour les producteurs
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle-2" size="4" class="text-lime-400" />
                            Marché en direct
                        </div>
                    </div>
                </div>

                {{-- ═══ Animation hero ═══ --}}
                <div class="hidden lg:block relative h-[450px]">

                    {{-- Cercle lumineux animé --}}
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[400px] h-[400px] rounded-full border-2 border-dashed border-lime-400/20 spin-slow"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[300px] h-[300px] rounded-full border border-lime-400/30"></div>
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[200px] h-[200px] rounded-full border border-lime-400/40"></div>

                    {{-- Centre : plante qui pousse --}}
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20">
                        <div class="relative">
                            <div class="absolute inset-0 bg-lime-400/40 rounded-full blur-3xl"></div>
                            <div class="relative w-28 h-28 rounded-3xl bg-gradient-to-br from-lime-400 to-green-500 flex items-center justify-center shadow-2xl float-anim">
                                <x-icon name="sprout" size="14" class="text-green-900" />
                            </div>
                        </div>
                    </div>

                    {{-- Points connectés (réseau) --}}
                    @php
                        $points = [
                            ['top' => '10%', 'left' => '20%', 'icon' => 'sprout', 'delay' => '0s', 'color' => 'from-green-500 to-green-600'],
                            ['top' => '15%', 'left' => '75%', 'icon' => 'factory', 'delay' => '0.5s', 'color' => 'from-amber-400 to-amber-500'],
                            ['top' => '75%', 'left' => '10%', 'icon' => 'shopping-basket', 'delay' => '1s', 'color' => 'from-blue-400 to-blue-500'],
                            ['top' => '80%', 'left' => '70%', 'icon' => 'warehouse', 'delay' => '1.5s', 'color' => 'from-purple-400 to-purple-500'],
                            ['top' => '45%', 'left' => '5%', 'icon' => 'package', 'delay' => '2s', 'color' => 'from-pink-400 to-pink-500'],
                            ['top' => '50%', 'left' => '85%', 'icon' => 'truck', 'delay' => '2.5s', 'color' => 'from-cyan-400 to-cyan-500'],
                        ];
                    @endphp

                    @foreach($points as $point)
                        <div class="absolute z-10 float-anim" style="top: {{ $point['top'] }}; left: {{ $point['left'] }}; animation-delay: {{ $point['delay'] }};">
                            <div class="relative">
                                <div class="absolute inset-0 rounded-2xl bg-white/40" style="animation: ping-slow 2.5s ease-out infinite; animation-delay: {{ $point['delay'] }};"></div>
                                <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br {{ $point['color'] }} flex items-center justify-center shadow-xl border-2 border-white/20">
                                    <x-icon :name="$point['icon']" size="6" class="text-white" />
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Lignes de connexion (SVG) --}}
                    <svg class="absolute inset-0 w-full h-full pointer-events-none z-0" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="line-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#84cc16;stop-opacity:0.6" />
                                <stop offset="100%" style="stop-color:#84cc16;stop-opacity:0" />
                            </linearGradient>
                        </defs>

                        {{-- Lignes vers le centre --}}
                        <line x1="20%" y1="15%" x2="50%" y2="50%" stroke="url(#line-gradient)" stroke-width="2" stroke-dasharray="4 4">
                            <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
                        </line>
                        <line x1="80%" y1="20%" x2="50%" y2="50%" stroke="url(#line-gradient)" stroke-width="2" stroke-dasharray="4 4">
                            <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
                        </line>
                        <line x1="15%" y1="80%" x2="50%" y2="50%" stroke="url(#line-gradient)" stroke-width="2" stroke-dasharray="4 4">
                            <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
                        </line>
                        <line x1="75%" y1="85%" x2="50%" y2="50%" stroke="url(#line-gradient)" stroke-width="2" stroke-dasharray="4 4">
                            <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
                        </line>
                        <line x1="5%" y1="50%" x2="50%" y2="50%" stroke="url(#line-gradient)" stroke-width="2" stroke-dasharray="4 4">
                            <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
                        </line>
                        <line x1="90%" y1="55%" x2="50%" y2="50%" stroke="url(#line-gradient)" stroke-width="2" stroke-dasharray="4 4">
                            <animate attributeName="stroke-dashoffset" from="0" to="8" dur="1s" repeatCount="indefinite"/>
                        </line>
                    </svg>

                    {{-- Badge flottant bas --}}
                    <div class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-white/10 backdrop-blur-xl border border-white/20 rounded-2xl px-5 py-3 shadow-2xl flex items-center gap-3">
                        <div class="flex -space-x-2">
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-amber-400 to-amber-500 border-2 border-white flex items-center justify-center text-[10px] font-black text-green-900">F</div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-green-500 to-green-600 border-2 border-white flex items-center justify-center text-[10px] font-black text-white">U</div>
                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-blue-500 border-2 border-white flex items-center justify-center text-[10px] font-black text-white">A</div>
                        </div>
                        <div class="text-left">
                            <p class="text-[10px] text-white/70 font-bold uppercase tracking-wider">Communauté active</p>
                            <p class="text-xs font-black text-white">Producteurs · Unités · Acheteurs</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- CHIFFRES --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="relative -mt-12 z-10 px-4">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-3xl shadow-xl border border-stone-100 p-6 sm:p-8 grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="text-center">
                    <p class="text-4xl sm:text-5xl font-black text-green-800 mb-1">6</p>
                    <p class="text-xs font-bold text-stone-500 uppercase tracking-wider">Communes</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl sm:text-5xl font-black text-green-800 mb-1">5</p>
                    <p class="text-xs font-bold text-stone-500 uppercase tracking-wider">Filières</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl sm:text-5xl font-black text-green-800 mb-1">100%</p>
                    <p class="text-xs font-bold text-stone-500 uppercase tracking-wider">Local</p>
                </div>
                <div class="text-center">
                    <p class="text-4xl sm:text-5xl font-black text-green-800 mb-1">2 min</p>
                    <p class="text-xs font-bold text-stone-500 uppercase tracking-wider">Inscription</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- PROBLÈME --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 sm:py-28 px-4">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-sm font-black text-green-700 uppercase tracking-widest mb-3">Le constat</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-stone-900 mb-5 leading-tight">
                    Un lien informel,<br>
                    <span class="gradient-text">peu fiable dans la durée</span>
                </h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto">
                    Aujourd'hui, la mise en relation repose sur le bouche-à-oreille. Résultat : des récoltes perdues, des ruptures d'approvisionnement, zéro traçabilité.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-3xl p-6 border-2 border-stone-100 shadow-sm hover:shadow-md transition">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-600 flex items-center justify-center mb-5">
                        <x-icon name="package-x" size="7" />
                    </div>
                    <h3 class="font-black text-stone-900 text-lg mb-3">Récoltes perdues</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">
                        Les producteurs ne savent pas qui a besoin de leurs produits. Faute d'acheteur, la production se perd.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-6 border-2 border-stone-100 shadow-sm hover:shadow-md transition">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center mb-5">
                        <x-icon name="alert-triangle" size="7" />
                    </div>
                    <h3 class="font-black text-stone-900 text-lg mb-3">Ruptures d'approvisionnement</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">
                        Les unités peinent à sécuriser des volumes réguliers. Elles ne savent jamais combien elles recevront.
                    </p>
                </div>

                <div class="bg-white rounded-3xl p-6 border-2 border-stone-100 shadow-sm hover:shadow-md transition">
                    <div class="w-14 h-14 rounded-2xl bg-stone-100 text-stone-600 flex items-center justify-center mb-5">
                        <x-icon name="eye-off" size="7" />
                    </div>
                    <h3 class="font-black text-stone-900 text-lg mb-3">Zéro traçabilité</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">
                        Aucun historique fiable des transactions. Impossible de construire une relation de confiance durable.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- SOLUTION --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="solution" class="py-20 sm:py-28 px-4 bg-gradient-to-b from-transparent to-green-50/50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-sm font-black text-green-700 uppercase tracking-widest mb-3">La solution</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-stone-900 mb-5 leading-tight">
                    Une plateforme complète,<br>
                    <span class="gradient-text">pensée pour le terrain</span>
                </h2>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto">
                    Simple, accessible, et adaptée aux réalités des Collines : connexion faible, écrans basiques, producteurs isolés.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach([
                    ['clipboard-list', 'Publier un besoin', 'Les unités publient leurs besoins : produit, quantité, délai, zone de collecte.'],
                    ['warehouse', 'Gérer son stock', 'Les producteurs et unités enregistrent leurs produits, leurs unités et leurs prix.'],
                    ['send', 'Proposer son produit', 'Les producteurs répondent aux besoins avec leur stock. Réservation automatique.'],
                    ['shopping-basket', 'Marché en direct', 'Vendez à la population et aux commerçants. Panier, commande, livraison ou retrait.'],
                    ['bell', 'Alertes intelligentes', 'Notifications aux seuils et alerte de pénurie anticipée à J-3.'],
                    ['shield-check', 'Traçabilité', 'Chaque commande et chaque livraison sont tracées. Historique complet disponible.'],
                ] as [$icone, $titre, $desc])
                    <div class="group bg-white rounded-3xl p-6 border-2 border-stone-100 hover:border-green-300 shadow-sm hover:shadow-lg transition-all hover:-translate-y-1">
                        <div class="w-14 h-14 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center mb-5 group-hover:scale-110 transition">
                            <x-icon :name="$icone" size="7" />
                        </div>
                        <h3 class="font-black text-stone-900 text-lg mb-2">{{ $titre }}</h3>
                        <p class="text-sm text-stone-500 leading-relaxed">{{ $desc }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- COMMENT ÇA MARCHE --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section id="how" class="py-20 sm:py-28 px-4 bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-sm font-black text-green-700 uppercase tracking-widest mb-3">Comment ça marche</p>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-stone-900 mb-5 leading-tight">
                    4 étapes du besoin<br>
                    <span class="gradient-text">à la livraison</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    ['1', 'Publier', 'L\'unité indique le produit, la quantité, le délai et la zone.', 'clipboard-list'],
                    ['2', 'Découvrir', 'Les producteurs voient les besoins proches correspondant à leur stock.', 'search'],
                    ['3', 'Proposer', 'Ils proposent une quantité, le stock est automatiquement réservé.', 'send'],
                    ['4', 'Livrer', 'L\'unité confirme la réception, le stock est définitivement déduit.', 'package-check'],
                ] as [$num, $titre, $desc, $icone])
                    <div class="relative">
                        <div class="absolute -top-2 -left-2 text-7xl font-black text-green-100 select-none">{{ $num }}</div>
                        <div class="relative bg-stone-50 rounded-3xl p-6 pt-10 border-2 border-stone-100">
                            <div class="w-12 h-12 rounded-2xl bg-green-800 text-white flex items-center justify-center mb-4 shadow-md">
                                <x-icon :name="$icone" size="6" />
                            </div>
                            <h3 class="font-black text-stone-900 text-lg mb-2">{{ $titre }}</h3>
                            <p class="text-sm text-stone-500 leading-relaxed">{{ $desc }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- TÉMOIGNAGES — CARROUSEL DÉFILANT --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 sm:py-24 px-4 bg-gradient-to-b from-white to-green-50/50 overflow-hidden">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-12">
                <p class="text-sm font-black text-green-700 uppercase tracking-widest mb-3">Témoignages</p>
                <h2 class="text-3xl sm:text-4xl font-black text-stone-900 mb-5 leading-tight">
                    Ils utilisent <span class="gradient-text">Transform'Collines</span>
                </h2>
                <p class="text-stone-500 max-w-2xl mx-auto">
                    Producteurs, unités de transformation, acheteurs : découvrez comment chacun en profite.
                </p>
            </div>
        </div>

        {{-- Carrousel infini --}}
        <div class="relative">
            {{-- Dégradés latéraux --}}
            <div class="absolute left-0 top-0 bottom-0 w-32 bg-gradient-to-r from-[#fafaf9] to-transparent z-10 pointer-events-none"></div>
            <div class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-[#f0fdf4] to-transparent z-10 pointer-events-none"></div>

            <div class="carousel-track gap-6 py-4">
                @php
                    $temoignages = [
                        [
                            'role' => 'Producteur',
                            'icone_role' => 'sprout',
                            'couleur_badge' => 'bg-green-100 text-green-800',
                            'initial' => 'F',
                            'couleur_avatar' => 'from-amber-400 to-amber-500',
                            'nom' => 'Fifamè AGBODJAN',
                            'titre' => 'Coopérative de manioc · Savalou',
                            'texte' => 'Avant, je perdais mes récoltes faute d\'acheteur. Maintenant, je vois les besoins des unités proches et je réponds en 2 minutes. Mon stock est réservé automatiquement.',
                            'fond' => 'bg-white border-stone-100',
                            'texte_couleur' => 'text-stone-700',
                        ],
                        [
                            'role' => 'Unité',
                            'icone_role' => 'factory',
                            'couleur_badge' => 'bg-amber-100 text-amber-800',
                            'initial' => 'U',
                            'couleur_avatar' => 'from-green-600 to-green-700',
                            'nom' => 'Unité de Paouignan',
                            'titre' => 'Unité de transformation · Dassa-Zoumé',
                            'texte' => 'Je dois sécuriser 20 tonnes de manioc dans les 10 jours. Aujourd\'hui, je vois mon taux de couverture en temps réel et je reçois des alertes si le besoin n\'est pas couvert.',
                            'fond' => 'bg-white border-stone-100',
                            'texte_couleur' => 'text-stone-700',
                        ],
                        [
                            'role' => 'Acheteur',
                            'icone_role' => 'shopping-basket',
                            'couleur_badge' => 'bg-blue-100 text-blue-800',
                            'initial' => 'A',
                            'couleur_avatar' => 'from-blue-500 to-blue-600',
                            'nom' => 'Aïcha BIO',
                            'titre' => 'Acheteuse · Dassa-Zoumé',
                            'texte' => 'J\'achète directement auprès des producteurs locaux. Produits frais, prix transparents, commande en ligne. Je vois exactement d\'où viennent mes produits.',
                            'fond' => 'bg-white border-stone-100',
                            'texte_couleur' => 'text-stone-700',
                        ],
                        [
                            'role' => 'Producteur',
                            'icone_role' => 'sprout',
                            'couleur_badge' => 'bg-green-100 text-green-800',
                            'initial' => 'J',
                            'couleur_avatar' => 'from-lime-500 to-lime-600',
                            'nom' => 'Jean HOUNKPATIN',
                            'titre' => 'Producteur de soja · Paouignan',
                            'texte' => 'Je peux enfin vendre ma production à un juste prix. Plus besoin de chercher des acheteurs au marché : je publie mon stock et les commandes arrivent.',
                            'fond' => 'bg-white border-stone-100',
                            'texte_couleur' => 'text-stone-700',
                        ],
                    ];
                @endphp

                {{-- On duplique les témoignages pour créer l'effet infini --}}
                @for($i = 0; $i < 2; $i++)
                    @foreach($temoignages as $t)
                        <div class="flex-shrink-0 w-[320px] {{ $t['fond'] }} border-2 rounded-3xl p-6 shadow-sm hover:shadow-lg transition">
                            {{-- Badge rôle --}}
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 {{ $t['couleur_badge'] }} rounded-full text-[10px] font-black uppercase tracking-wider mb-4">
                                <x-icon :name="$t['icone_role']" size="3" />
                                {{ $t['role'] }}
                            </div>

                            {{-- Texte --}}
                            <p class="text-sm {{ $t['texte_couleur'] }} leading-relaxed italic mb-5 min-h-[100px]">
                                « {{ $t['texte'] }} »
                            </p>

                            {{-- Auteur --}}
                            <div class="flex items-center gap-3 pt-4 border-t border-stone-100">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $t['couleur_avatar'] }} text-white flex items-center justify-center font-black shadow-md flex-shrink-0">
                                    {{ $t['initial'] }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-black text-stone-900 text-sm truncate">{{ $t['nom'] }}</p>
                                    <p class="text-[11px] text-stone-500 truncate">{{ $t['titre'] }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endfor
            </div>
        </div>

        {{-- Indication hover --}}
        <div class="text-center mt-6">
            <p class="text-xs text-stone-400 flex items-center justify-center gap-1.5">
                <x-icon name="pause" size="3" />
                Survolez pour mettre en pause
            </p>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- CTA FINAL --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 sm:py-28 px-4">
        <div class="max-w-4xl mx-auto">
            <div class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 rounded-3xl p-10 sm:p-14 text-center text-white relative overflow-hidden shadow-2xl">
                <div class="absolute inset-0 grain opacity-40"></div>
                <div class="absolute -top-32 -right-32 w-96 h-96 bg-lime-400/30 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-32 -left-32 w-96 h-96 bg-green-400/30 rounded-full blur-3xl"></div>

                <div class="relative">
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black mb-4 leading-tight">
                        Prêt à rejoindre<br>
                        <span class="text-lime-300">la communauté ?</span>
                    </h2>
                    <p class="text-lg text-green-100/90 mb-8 max-w-xl mx-auto">
                        Inscription gratuite en 2 minutes. Pas de carte bancaire, pas d'engagement.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-green-800 rounded-2xl font-black shadow-lg hover:bg-green-50 transition">
                            Créer mon compte gratuit
                            <x-icon name="arrow-right" size="5" />
                        </a>
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white/10 backdrop-blur border-2 border-white/30 text-white rounded-2xl font-black hover:bg-white/20 transition">
                            J'ai déjà un compte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- FOOTER --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    <footer class="bg-stone-900 text-stone-400 py-12 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">

                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-700 to-green-500 flex items-center justify-center">
                            <x-icon name="sprout" size="6" class="text-white" />
                        </div>
                        <span class="font-black text-white text-lg">Transform'Collines</span>
                    </div>
                    <p class="text-sm leading-relaxed max-w-md">
                        Plateforme de mise en relation entre producteurs et unités de transformation agricole du département des Collines, Bénin.
                    </p>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4 text-sm uppercase tracking-wider">Plateforme</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('marketplace.index') }}" class="hover:text-white transition">Marché</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Connexion</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Inscription</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4 text-sm uppercase tracking-wider">Contact</h4>
                    <ul class="space-y-2 text-sm">
                        <li class="flex items-center gap-2">
                            <x-icon name="map-pin" size="3.5" />
                            Dassa-Zoumé, Bénin
                        </li>
                        <li class="flex items-center gap-2">
                            <x-icon name="mail" size="3.5" />
                            contact@transformcollines.bj
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-stone-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <p>© 2026 Transform'Collines · Développé par Ulrich KASSIN et Priscille KINDOHO</p>
                
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>