<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Transform'Collines — Sécuriser l'approvisionnement agricole</title>
    <meta name="description" content="Plateforme de mise en relation entre producteurs et unités de transformation agricole des Collines (Bénin).">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #065f46 0%, #16a34a 50%, #84cc16 100%);
        }
        .text-gradient {
            background: linear-gradient(135deg, #16a34a 0%, #84cc16 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .pattern-dots {
            background-image: radial-gradient(rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-white text-gray-900">

    {{-- NAV --}}
    <nav class="absolute top-0 left-0 right-0 z-20 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center border border-white/20">
                    <x-icon name="sprout" size="6" class="text-white" />
                </div>
                <span class="text-lg font-bold text-white">Transform'Collines</span>
            </a>

            <div class="flex items-center gap-3">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 bg-white text-green-700 rounded-lg font-medium text-sm hover:bg-green-50 transition">
                        Mon espace
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-white font-medium text-sm hover:bg-white/10 rounded-lg transition">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2 bg-white text-green-700 rounded-lg font-medium text-sm hover:bg-green-50 transition shadow-sm">
                        S'inscrire
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO --}}
    <section class="hero-gradient relative overflow-hidden pt-32 pb-24 px-6">
        <div class="absolute inset-0 pattern-dots opacity-30"></div>

        <div class="relative max-w-6xl mx-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/15 backdrop-blur border border-white/20 rounded-full text-xs font-medium text-white mb-6">
                        <span class="w-2 h-2 rounded-full bg-lime-400"></span>
                        Hackathon 41 · Salon du Numérique des Collines
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                        L'approvisionnement agricole
                        <span class="block text-lime-300">enfin sécurisé.</span>
                    </h1>

                    <p class="text-lg text-green-50 mb-8 leading-relaxed max-w-xl">
                        Transform'Collines relie directement les <strong class="text-white">producteurs</strong> et les <strong class="text-white">unités de transformation</strong> du département des Collines.
                        Fini le bouche-à-oreille, place à un approvisionnement fiable, prévisible et traçable.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-green-700 rounded-xl font-semibold shadow-lg hover:shadow-xl hover:bg-green-50 transition">
                            Commencer gratuitement
                            <x-icon name="arrow-right" size="5" />
                        </a>
                        <a href="#comment"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white/10 backdrop-blur border border-white/20 text-white rounded-xl font-semibold hover:bg-white/20 transition">
                            Comment ça marche
                        </a>
                    </div>

                    <div class="flex items-center gap-6 mt-10 text-sm text-green-50">
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle-2" size="4" class="text-lime-300" />
                            6 communes couvertes
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle-2" size="4" class="text-lime-300" />
                            SMS inclus
                        </div>
                    </div>
                </div>

                {{-- Illustration --}}
                <div class="hidden lg:block">
                    <div class="relative">
                        <div class="absolute -top-6 -right-6 w-72 h-72 bg-lime-400/30 rounded-full blur-3xl"></div>
                        <div class="absolute -bottom-6 -left-6 w-72 h-72 bg-green-600/30 rounded-full blur-3xl"></div>

                        <div class="relative bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl p-6 shadow-2xl">
                            {{-- Faux dashboard --}}
                            <div class="flex items-center justify-between mb-4">
                                <span class="text-xs font-medium text-white/80">Besoin actif</span>
                                <span class="px-2 py-0.5 bg-amber-400/20 text-amber-200 text-xs rounded-full font-medium">
                                    En cours
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <div class="flex items-baseline justify-between mb-1">
                                        <span class="text-sm font-semibold text-white">Manioc</span>
                                        <span class="text-xs text-white/70">20 T recherchées</span>
                                    </div>
                                    <div class="h-2 bg-white/20 rounded-full overflow-hidden">
                                        <div class="h-full bg-gradient-to-r from-lime-400 to-green-400 rounded-full" style="width: 60%"></div>
                                    </div>
                                    <div class="flex justify-between mt-1 text-xs text-white/70">
                                        <span>60 % couvert</span>
                                        <span>12 T sur 20 T</span>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-white/10">
                                    <div class="text-xs text-white/60 mb-2">3 producteurs mobilisés</div>
                                    <div class="flex gap-2">
                                        <div class="w-8 h-8 rounded-full bg-lime-400 text-green-900 flex items-center justify-center text-xs font-bold">F</div>
                                        <div class="w-8 h-8 rounded-full bg-amber-400 text-green-900 flex items-center justify-center text-xs font-bold">J</div>
                                        <div class="w-8 h-8 rounded-full bg-emerald-400 text-green-900 flex items-center justify-center text-xs font-bold">M</div>
                                        <div class="w-8 h-8 rounded-full bg-white/20 text-white flex items-center justify-center text-xs font-bold">+</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PROBLÈME --}}
    <section class="py-20 px-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-sm font-semibold text-green-700 uppercase tracking-wider mb-2">Le constat</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Un lien informel, peu fiable dans la durée
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Aujourd'hui, la mise en relation entre producteurs et unités repose sur le bouche-à-oreille.
                    Résultat : des récoltes perdues, des ruptures d'approvisionnement, et aucune traçabilité.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center mb-4">
                        <x-icon name="package-x" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Récoltes perdues</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Les producteurs ne savent pas qui a besoin de leurs produits. Faute d'acheteur, la production se perd.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center mb-4">
                        <x-icon name="alert-triangle" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Ruptures d'approvisionnement</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Les unités peinent à sécuriser des volumes réguliers. Elles ne savent jamais combien elles recevront.
                    </p>
                </div>

                <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center mb-4">
                        <x-icon name="eye-off" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Zéro traçabilité</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Aucun historique fiable des transactions. Impossible de construire une relation de confiance durable.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- SOLUTION --}}
    <section class="py-20 px-6 bg-white">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-sm font-semibold text-green-700 uppercase tracking-wider mb-2">La solution</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Une plateforme pensée <span class="text-gradient">pour le terrain</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Simple, accessible, et adaptée aux réalités des Collines : connexion faible, écrans basiques, producteurs isolés.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mb-4">
                        <x-icon name="clipboard-list" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Publication de besoins</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Les unités publient leurs besoins : produit, quantité, délai, zone de collecte.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mb-4">
                        <x-icon name="package" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Déclaration de disponibilités</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Les producteurs annoncent leurs produits disponibles et sont visibles par les unités proches.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mb-4">
                        <x-icon name="trending-up" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Taux de couverture en direct</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Le pourcentage de couverture se met à jour automatiquement à chaque déclaration.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mb-4">
                        <x-icon name="bell" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Alertes intelligentes</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Notifications aux seuils (25 %, 50 %, 100 %) et alerte de pénurie anticipée à J-3.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mb-4">
                        <x-icon name="shield-check" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Confiance bilatérale</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Notation qualité + paiement. Score de fiabilité calculé automatiquement.
                    </p>
                </div>

                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 hover:shadow-md transition">
                    <div class="w-12 h-12 rounded-xl bg-green-100 text-green-700 flex items-center justify-center mb-4">
                        <x-icon name="message-square" size="6" />
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Inclusion SMS / USSD</h3>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Les producteurs sans smartphone peuvent déclarer par SMS simple.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- COMMENT ÇA MARCHE --}}
    <section id="comment" class="py-20 px-6 bg-gray-50">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-14">
                <p class="text-sm font-semibold text-green-700 uppercase tracking-wider mb-2">Comment ça marche</p>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    4 étapes, du besoin à la livraison
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="relative">
                    <div class="text-6xl font-extrabold text-green-100 absolute -top-2 -left-1">1</div>
                    <div class="relative pt-8">
                        <h3 class="font-semibold text-gray-900 mb-2">Publier un besoin</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            L'unité indique le produit, la quantité, le délai et la zone de collecte.
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <div class="text-6xl font-extrabold text-green-100 absolute -top-2 -left-1">2</div>
                    <div class="relative pt-8">
                        <h3 class="font-semibold text-gray-900 mb-2">Notifier les producteurs</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Les producteurs proches du produit reçoivent une alerte (web + SMS).
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <div class="text-6xl font-extrabold text-green-100 absolute -top-2 -left-1">3</div>
                    <div class="relative pt-8">
                        <h3 class="font-semibold text-gray-900 mb-2">Déclarer sa dispo</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Le producteur annonce sa quantité. Le taux de couverture monte instantanément.
                        </p>
                    </div>
                </div>

                <div class="relative">
                    <div class="text-6xl font-extrabold text-green-100 absolute -top-2 -left-1">4</div>
                    <div class="relative pt-8">
                        <h3 class="font-semibold text-gray-900 mb-2">Collecter et noter</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            L'unité confirme la réception. Les deux parties se notent. La confiance se construit.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CHIFFRES --}}
    <section class="py-20 px-6 bg-green-700 text-white">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-lime-300 mb-2">6</div>
                    <p class="text-sm text-green-100">Communes des Collines</p>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-lime-300 mb-2">5</div>
                    <p class="text-sm text-green-100">Filières agricoles</p>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-lime-300 mb-2">+40 %</div>
                    <p class="text-sm text-green-100">Taux de couverture visé</p>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-extrabold text-lime-300 mb-2">2 min</div>
                    <p class="text-sm text-green-100">Pour s'inscrire</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA FINAL --}}
    <section class="py-20 px-6 bg-white">
        <div class="max-w-3xl mx-auto text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                Prêt à sécuriser vos approvisionnements ?
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Rejoignez la plateforme et connectez-vous aux producteurs et unités des Collines.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('register') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-green-600 text-white rounded-xl font-semibold shadow-lg hover:bg-green-700 hover:shadow-xl transition">
                    Créer un compte
                    <x-icon name="arrow-right" size="5" />
                </a>
                <a href="{{ route('login') }}"
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gray-100 text-gray-900 rounded-xl font-semibold hover:bg-gray-200 transition">
                    Se connecter
                </a>
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer class="bg-gray-900 text-gray-400 py-12 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-9 h-9 rounded-lg bg-green-600 flex items-center justify-center">
                            <x-icon name="sprout" size="5" class="text-white" />
                        </div>
                        <span class="font-bold text-white">Transform'Collines</span>
                    </div>
                    <p class="text-sm leading-relaxed">
                        Plateforme de mise en relation entre producteurs et unités de transformation agricole des Collines.
                    </p>
                </div>

                <div>
                    <h4 class="font-semibold text-white mb-4">Plateforme</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Connexion</a></li>
                        <li><a href="{{ route('register') }}" class="hover:text-white transition">Inscription</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-semibold text-white mb-4">Contexte</h4>
                    <p class="text-sm leading-relaxed">
                        Développé dans le cadre du <strong class="text-white">Hackathon 41</strong> du Salon du Numérique des Collines, à Dassa-Zoumé.
                    </p>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
                <p>© {{ date('Y') }} Transform'Collines — Tous droits réservés</p>
                <p>Fait avec <span class="text-lime-400">♥</span> au Bénin</p>
            </div>
        </div>
    </footer>

    {{-- Lucide --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>