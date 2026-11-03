<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marché des Collines — Transform'Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .grain {
            background-image: radial-gradient(circle at 1px 1px, rgba(15,81,50,0.04) 1px, transparent 0);
            background-size: 24px 24px;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen">

    {{-- NAV PUBLIQUE --}}
    <nav class="bg-white/95 backdrop-blur-lg border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">

            <a href="/" class="flex items-center gap-2.5 group flex-shrink-0">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-lime-400 rounded-xl blur opacity-40 group-hover:opacity-60 transition"></div>
                    <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center shadow-md">
                        <x-icon name="sprout" size="6" class="text-white" />
                    </div>
                </div>
                <span class="font-black text-green-900 hidden sm:inline text-lg tracking-tight">Transform'Collines</span>
            </a>

            {{-- Recherche rapide --}}
            <form method="GET" action="{{ route('marketplace.index') }}" class="hidden md:flex flex-1 max-w-md">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <x-icon name="search" size="4" class="text-stone-400" />
                    </div>
                    <input type="text" name="q" value="{{ request('q') }}"
                           placeholder="Rechercher un produit..."
                           class="w-full pl-11 pr-4 py-2.5 rounded-2xl border-2 border-stone-200 bg-white focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition text-sm">
                </div>
            </form>

            <div class="flex items-center gap-2 flex-shrink-0">
                @auth
                    <a href="{{ route('panier.index') }}"
                       class="relative p-2.5 hover:bg-stone-100 rounded-xl transition">
                        <x-icon name="shopping-cart" size="5" class="text-stone-700" />
                        @php
                            $nbPanier = \App\Models\PanierItem::where('utilisateur_id', auth()->id())->count();
                        @endphp
                        @if($nbPanier > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-green-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                {{ $nbPanier }}
                            </span>
                        @endif
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-sm transition">
                        <x-icon name="layout-dashboard" size="4" />
                        <span class="hidden sm:inline">Mon espace</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-4 py-2.5 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-sm transition">
                        S'inscrire
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- HERO MARCHÉ --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-green-900 via-green-800 to-green-700 text-white">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-lime-400 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-green-400 rounded-full blur-3xl"></div>
        </div>
        <div class="absolute inset-0 grain opacity-30"></div>

        <div class="relative max-w-7xl mx-auto px-4 py-12 sm:py-16">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/15 backdrop-blur border border-white/20 rounded-full text-xs font-bold uppercase tracking-wider mb-5">
                    <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>
                    Marché en direct
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black mb-4 leading-[1.05] tracking-tight">
                    Les produits frais<br>
                    <span class="text-lime-300">des Collines.</span>
                </h1>

                <p class="text-lg text-green-50/90 mb-8 leading-relaxed max-w-xl">
                    Achetez directement auprès des producteurs et unités de transformation de votre région. Frais, local, sans intermédiaire.
                </p>

                {{-- Recherche mobile --}}
                <form method="GET" action="{{ route('marketplace.index') }}" class="md:hidden mb-6">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <x-icon name="search" size="5" class="text-stone-400" />
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Rechercher un produit..."
                               class="w-full pl-12 pr-4 py-4 rounded-2xl border-2 border-transparent bg-white text-stone-900 focus:ring-4 focus:ring-lime-400/30 outline-none transition">
                    </div>
                </form>

                {{-- Stats --}}
                <div class="flex flex-wrap items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <x-icon name="package" size="4" class="text-lime-300" />
                        <span><strong>{{ $offres->total() }}</strong> produits</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="users" size="4" class="text-lime-300" />
                        <span>Vendeurs locaux</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-icon name="truck" size="4" class="text-lime-300" />
                        <span>Retrait ou livraison</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CATÉGORIES --}}
    @if($categories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 -mt-8 relative z-10 mb-10">
            <div class="bg-white rounded-3xl shadow-xl border border-stone-100 p-4 overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max">
                    <a href="{{ route('marketplace.index', request()->except('categorie_id', 'page')) }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl font-bold text-sm transition whitespace-nowrap
                              {{ !request('categorie_id') ? 'bg-green-800 text-white shadow-md' : 'bg-stone-50 text-stone-700 hover:bg-stone-100' }}">
                        <x-icon name="layout-grid" size="4" />
                        Tout voir
                    </a>

                    @foreach($categories as $cat)
                        <a href="{{ route('marketplace.index', array_merge(request()->except('categorie_id', 'page'), ['categorie_id' => $cat->id])) }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl font-bold text-sm transition whitespace-nowrap
                                  {{ request('categorie_id') == $cat->id ? 'bg-green-800 text-white shadow-md' : 'bg-stone-50 text-stone-700 hover:bg-stone-100' }}">
                            <x-icon :name="$cat->icone ?? 'package'" size="4" />
                            {{ $cat->nom }}
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- CONTENU --}}
    <main class="max-w-7xl mx-auto px-4 pb-16">

        {{-- Filtres + tri --}}
        <div class="flex items-center justify-between flex-wrap gap-4 mb-6">
            <div class="flex items-center gap-3 flex-wrap">
                <p class="text-sm text-stone-500">
                    <strong class="text-stone-900">{{ $offres->total() }}</strong> produit{{ $offres->total() > 1 ? 's' : '' }} trouvé{{ $offres->total() > 1 ? 's' : '' }}
                </p>

                @if(request('q'))
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">
                        « {{ request('q') }} »
                        <a href="{{ route('marketplace.index', request()->except('q', 'page')) }}" class="hover:text-green-900">
                            <x-icon name="x" size="3" />
                        </a>
                    </span>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <span class="text-xs text-stone-500 font-bold uppercase tracking-wider hidden sm:inline">Trier par</span>
                <select onchange="window.location.href=this.value"
                        class="text-sm border-2 border-stone-200 rounded-2xl px-4 py-2.5 font-bold text-stone-700 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="{{ request()->fullUrlWithQuery(['tri' => 'recent']) }}" @selected(request('tri', 'recent') === 'recent')>Plus récents</option>
                    <option value="{{ request()->fullUrlWithQuery(['tri' => 'prix_asc']) }}" @selected(request('tri') === 'prix_asc')>Prix croissant</option>
                    <option value="{{ request()->fullUrlWithQuery(['tri' => 'prix_desc']) }}" @selected(request('tri') === 'prix_desc')>Prix décroissant</option>
                    <option value="{{ request()->fullUrlWithQuery(['tri' => 'populaire']) }}" @selected(request('tri') === 'populaire')>Plus populaires</option>
                </select>
            </div>
        </div>

        {{-- Grille --}}
        @if($offres->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-stone-100 text-stone-400 flex items-center justify-center">
                    <x-icon name="package-search" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucun produit trouvé</p>
                <p class="text-sm text-stone-500 mb-6 max-w-md mx-auto">
                    Essayez d'autres filtres ou revenez plus tard pour découvrir de nouveaux produits.
                </p>
                <a href="{{ route('marketplace.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold transition">
                    <x-icon name="refresh-cw" size="4" />
                    Voir tous les produits
                </a>
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                @foreach($offres as $offre)
                    <a href="{{ route('marketplace.show', $offre) }}"
                       class="group bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">

                        {{-- Image --}}
                        <div class="aspect-square bg-gradient-to-br from-green-50 to-lime-50 flex items-center justify-center relative overflow-hidden">
                            @if($offre->photo_principale)
                                <img src="{{ Storage::url($offre->photo_principale) }}"
                                     alt="{{ $offre->titre }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <x-icon name="package" size="16" class="text-green-300" />
                            @endif

                            {{-- Badge catégorie --}}
                            @if($offre->categorie)
                                <span class="absolute top-3 left-3 px-3 py-1 bg-white/95 backdrop-blur text-[10px] font-black text-green-800 uppercase tracking-wider rounded-full shadow-sm">
                                    {{ $offre->categorie->nom }}
                                </span>
                            @endif

                            {{-- Badge vendeur type --}}
                            <span class="absolute top-3 right-3 w-8 h-8 rounded-full {{ $offre->estPublieeParUnite() ? 'bg-amber-500' : 'bg-green-600' }} text-white flex items-center justify-center shadow-md">
                                <x-icon :name="$offre->estPublieeParUnite() ? 'factory' : 'sprout'" size="4" />
                            </span>

                            {{-- Badge gros --}}
                            @if($offre->prix_gros && $offre->seuil_gros)
                                <span class="absolute bottom-3 left-3 px-2.5 py-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider rounded-full shadow-md">
                                    Prix de gros
                                </span>
                            @endif
                        </div>

                        {{-- Contenu --}}
                        <div class="p-4">
                            <h3 class="font-bold text-stone-900 text-sm leading-tight mb-1 line-clamp-2 min-h-[2.5rem]">
                                {{ $offre->titre }}
                            </h3>

                            <p class="text-xs text-stone-500 flex items-center gap-1 mb-3">
                                <x-icon name="map-pin" size="3" />
                                <span class="truncate">{{ $offre->nomVendeur() }}</span>
                            </p>

                            <div class="flex items-baseline justify-between gap-2">
                                <div>
                                    <p class="text-xl font-black text-green-800 leading-none">
                                        {{ number_format($offre->prix_detail, 0, ',', ' ') }}
                                        <span class="text-xs font-bold text-stone-400">F</span>
                                    </p>
                                    <p class="text-[10px] text-stone-500 mt-0.5">
                                        par {{ $offre->uniteMesure->nom }}
                                    </p>
                                </div>

                                @if($offre->quantite_disponible > 0)
                                    <span class="text-[10px] font-bold text-stone-500 text-right">
                                        {{ number_format($offre->quantite_disponible, 0, ',', ' ') }}
                                        {{ $offre->uniteMesure->symbole ?? $offre->uniteMesure->nom }} dispo
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">{{ $offres->links() }}</div>
        @endif
    </main>

    {{-- FOOTER --}}
    <footer class="bg-stone-900 text-stone-400 py-10 px-4">
        <div class="max-w-7xl mx-auto text-center">
            <div class="flex items-center justify-center gap-2.5 mb-4">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-700 to-green-500 flex items-center justify-center">
                    <x-icon name="sprout" size="5" class="text-white" />
                </div>
                <span class="font-black text-white text-lg">Transform'Collines</span>
            </div>
            <p class="text-xs mb-4">Le marché des produits locaux des Collines</p>
            <p class="text-[10px] text-stone-500">
                © 2026 Transform'Collines · Développé par Ulrich KASSIN et Priscille KINDOHO
            </p>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>