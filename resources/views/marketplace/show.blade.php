<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $offre->titre }} — Marché des Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .line-clamp-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .grain {
            background-image: radial-gradient(circle at 1px 1px, rgba(20,83,45,0.05) 1px, transparent 0);
            background-size: 28px 28px;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="font-sans antialiased bg-[#faf9f6] min-h-screen">

    {{-- ═══════════════ NAV ═══════════════ --}}
    <nav class="bg-white/90 backdrop-blur-xl border-b border-stone-200/80 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('marketplace.index') }}"
               class="flex items-center gap-2 text-sm font-bold text-stone-700 hover:text-green-900 transition group">
                <x-icon name="arrow-left" size="4" class="group-hover:-translate-x-0.5 transition-transform" />
                <span>Retour au marché</span>
            </a>

            <div class="flex items-center gap-1.5">
                @auth
                    <a href="{{ route('panier.index') }}" class="relative p-2.5 hover:bg-stone-100 rounded-xl transition">
                        <x-icon name="shopping-cart" size="5" class="text-stone-700" />
                        @php $nbPanier = \App\Models\PanierItem::where('utilisateur_id', auth()->id())->count(); @endphp
                        @if($nbPanier > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-green-700 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                {{ $nbPanier }}
                            </span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-2 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-xl transition">
                        Connexion
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-14">

            {{-- ═══════════════ PHOTOS ═══════════════ --}}
            <div class="bg-white rounded-2xl border border-stone-100 overflow-hidden lg:sticky lg:top-24 self-start"
                 x-data="{ active: '{{ $offre->photo_principale ? Storage::url($offre->photo_principale) : '' }}' }">

                @if($offre->photos->count() > 0)
                    <div class="aspect-square bg-stone-50 overflow-hidden">
                        <img :src="active" alt="{{ $offre->titre }}"
                             class="w-full h-full object-cover transition-opacity duration-300">
                    </div>

                    @if($offre->photos->count() > 1)
                        <div class="flex gap-2 p-3 overflow-x-auto bg-white border-t border-stone-100 scrollbar-hide">
                            @foreach($offre->photos as $photo)
                                <button type="button"
                                        @click="active = '{{ Storage::url($photo->chemin) }}'"
                                        class="w-16 h-16 rounded-lg overflow-hidden border-2 transition flex-shrink-0"
                                        :class="active === '{{ Storage::url($photo->chemin) }}' ? 'border-green-700 shadow-sm' : 'border-stone-200 hover:border-stone-300'">
                                    <img src="{{ Storage::url($photo->chemin) }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="aspect-square flex items-center justify-center bg-gradient-to-br from-stone-50 to-green-50/40">
                        <x-icon name="package" size="18" class="text-green-200" />
                    </div>
                @endif
            </div>

            {{-- ═══════════════ INFOS PRODUIT ═══════════════ --}}
            <div>

                {{-- Badges --}}
                <div class="flex items-center flex-wrap gap-2 mb-4">
                    @if($offre->categorie)
                        <span class="inline-flex items-center px-2.5 py-1 bg-stone-100 text-stone-800 text-[10px] font-bold uppercase tracking-widest rounded-md">
                            {{ $offre->categorie->nom }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-stone-100 text-stone-700 text-[10px] font-bold uppercase tracking-widest rounded-md">
                        <x-icon :name="$offre->estPublieeParUnite() ? 'factory' : 'sprout'" size="3" />
                        {{ $offre->estPublieeParUnite() ? 'Unité' : 'Producteur' }}
                    </span>
                </div>

                {{-- Titre --}}
                <h1 class="text-3xl sm:text-4xl font-black text-stone-900 mb-5 leading-[1.1] tracking-tight">
                    {{ $offre->titre }}
                </h1>

                {{-- Vendeur --}}
                <a href="{{ route('marketplace.vendeur', [$offre->estPublieeParUnite() ? 'unite' : 'producteur', $offre->vendeur()->id]) }}"
                   class="inline-flex items-center gap-3 mb-6 group w-full">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-900 to-green-700 text-white flex items-center justify-center font-black shadow-sm">
                        {{ strtoupper(substr($offre->nomVendeur(), 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-stone-900 text-sm group-hover:text-green-900 transition truncate">{{ $offre->nomVendeur() }}</p>
                        <p class="text-xs text-stone-500 flex items-center gap-1 mt-0.5">
                            <x-icon name="map-pin" size="3" />
                            {{ $offre->vendeur()?->utilisateur?->commune?->nom ?? '—' }}
                        </p>
                    </div>
                    <x-icon name="chevron-right" size="4" class="text-stone-400 group-hover:text-green-800 group-hover:translate-x-0.5 transition flex-shrink-0" />
                </a>

                {{-- Prix --}}
                <div class="bg-stone-50 rounded-2xl p-5 mb-6 border border-stone-100">
                    <div class="flex items-baseline gap-2 flex-wrap">
                        <span class="text-4xl font-black text-green-900 leading-none tracking-tight">
                            {{ number_format($offre->prix_detail, 0, ',', ' ') }}
                        </span>
                        <span class="text-sm font-bold text-stone-500 uppercase tracking-wider">FCFA</span>
                        <span class="text-sm text-stone-500">/ {{ $offre->uniteMesure->nom }}</span>
                    </div>

                    @if($offre->prix_gros && $offre->seuil_gros)
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-100 text-amber-800 rounded-md text-[11px] font-bold mt-3">
                            <x-icon name="trending-down" size="3" />
                            Prix de gros : {{ number_format($offre->prix_gros, 0, ',', ' ') }} F dès {{ number_format($offre->seuil_gros, 0, ',', ' ') }} {{ $offre->uniteMesure->nom }}s
                        </div>
                    @endif
                </div>

                {{-- Infos pratiques --}}
                <div class="divide-y divide-stone-100 mb-6">
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-stone-500 flex items-center gap-2">
                            <x-icon name="package" size="4" />
                            Disponible
                        </span>
                        <span class="font-bold text-stone-900 text-sm text-right">
                            {{ number_format($offre->quantite_disponible, 0, ',', ' ') }}
                            {{ $offre->uniteMesure->nom }}{{ $offre->quantite_disponible > 1 ? 's' : '' }}
                            @if($offre->quantite_kg_equivalent)
                                <span class="text-xs font-normal text-stone-500">· {{ number_format($offre->quantite_kg_equivalent, 0, ',', ' ') }} kg</span>
                            @endif
                        </span>
                    </div>

                    @if($offre->localisation)
                        <div class="flex items-center justify-between py-3">
                            <span class="text-sm text-stone-500 flex items-center gap-2">
                                <x-icon name="map-pin" size="4" />
                                Localisation
                            </span>
                            <span class="font-bold text-stone-900 text-sm text-right">{{ $offre->localisation }}</span>
                        </div>
                    @endif

                    @if($offre->conditions_retrait)
                        <div class="py-3">
                            <span class="text-sm text-stone-500 flex items-center gap-2 mb-2">
                                <x-icon name="info" size="4" />
                                Conditions
                            </span>
                            <p class="text-sm text-stone-700 leading-relaxed">{{ $offre->conditions_retrait }}</p>
                        </div>
                    @endif
                </div>

                {{-- ═══════════════ AJOUT AU PANIER ═══════════════ --}}
                @auth
                    @php
                        $user = auth()->user();
                        $estProprietaire = false;

                        if ($user->isProducteur() && $offre->producteur_id) {
                            $estProprietaire = $user->producteur?->id === $offre->producteur_id;
                        } elseif ($user->isUnite() && $offre->unite_id) {
                            $estProprietaire = $user->unite?->id === $offre->unite_id;
                        }
                    @endphp

                    @if(!$estProprietaire || $user->isAdmin())
                        <form method="POST" action="{{ route('panier.ajouter', $offre) }}"
                              class="bg-white rounded-2xl border border-stone-200 p-6"
                              x-data="{ quantite: 1 }">
                            @csrf

                            <label class="block text-sm font-bold text-stone-900 mb-3">
                                Quantité à commander
                            </label>

                            <div class="flex items-center gap-2 mb-4">
                                <button type="button" @click="quantite = Math.max(0.01, quantite - 1)"
                                        class="w-11 h-11 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition flex-shrink-0">
                                    <x-icon name="minus" size="4" />
                                </button>

                                <div class="flex-1 relative">
                                    <input type="number" name="quantite" step="0.01" min="0.01" max="{{ $offre->quantite_disponible }}"
                                           x-model="quantite"
                                           class="w-full text-center rounded-xl border border-stone-200 bg-white px-4 py-3 pr-20 focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition font-mono text-lg font-black">
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-stone-500 font-bold pointer-events-none">
                                        {{ $offre->uniteMesure->nom }}{{ $offre->quantite_disponible > 1 ? 's' : '' }}
                                    </span>
                                </div>

                                <button type="button" @click="quantite = Math.min({{ $offre->quantite_disponible }}, quantite + 1)"
                                        class="w-11 h-11 rounded-xl bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition flex-shrink-0">
                                    <x-icon name="plus" size="4" />
                                </button>
                            </div>

                            {{-- Total --}}
                            <div class="bg-green-50 rounded-xl p-3.5 mb-4 flex items-center justify-between border border-green-100">
                                <span class="text-sm text-green-800 font-bold">Total estimé</span>
                                <span class="text-lg font-black text-green-900">
                                    <span x-text="Math.round(quantite * {{ $offre->prixPourQuantite(1) }}).toLocaleString('fr-FR')"></span> F
                                </span>
                            </div>

                            <button type="submit"
                                    class="w-full bg-green-900 hover:bg-green-950 text-white font-bold py-3.5 rounded-xl transition shadow-lg shadow-green-900/20 flex items-center justify-center gap-2">
                                <x-icon name="shopping-cart" size="4" />
                                Ajouter au panier
                            </button>
                        </form>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-3">
                            <x-icon name="alert-circle" size="5" class="text-amber-600 flex-shrink-0 mt-0.5" />
                            <p class="text-sm text-amber-900">C'est votre propre produit.</p>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="block w-full bg-green-900 hover:bg-green-950 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-green-900/20 transition text-center flex items-center justify-center gap-2">
                        <x-icon name="log-in" size="4" />
                        Connectez-vous pour commander
                    </a>
                    <p class="text-xs text-stone-500 text-center mt-3">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}" class="text-green-800 font-bold hover:underline">Inscrivez-vous gratuitement</a>
                    </p>
                @endauth
            </div>
        </div>

        {{-- ═══════════════ DESCRIPTION ═══════════════ --}}
        @if($offre->description)
            <div class="bg-white rounded-2xl border border-stone-100 p-6 sm:p-8 mb-10">
                <h2 class="text-xl font-black text-stone-900 mb-4 flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-green-50 text-green-800 flex items-center justify-center">
                        <x-icon name="file-text" size="4" />
                    </div>
                    Description du produit
                </h2>
                <p class="text-stone-600 leading-relaxed whitespace-pre-line">{{ $offre->description }}</p>
            </div>
        @endif

        {{-- ═══════════════ AUTRES OFFRES ═══════════════ --}}
        @if($autresOffres->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-black text-stone-900">Autres produits de ce vendeur</h2>
                    <a href="{{ route('marketplace.vendeur', [$offre->estPublieeParUnite() ? 'unite' : 'producteur', $offre->vendeur()->id]) }}"
                       class="text-sm font-bold text-green-800 hover:text-green-900 flex items-center gap-1 group">
                        Tout voir
                        <x-icon name="arrow-right" size="4" class="group-hover:translate-x-0.5 transition-transform" />
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($autresOffres as $autre)
                        <a href="{{ route('marketplace.show', $autre) }}"
                           class="group bg-white rounded-2xl border border-stone-100 overflow-hidden hover:border-stone-200 hover:shadow-xl hover:shadow-stone-200/60 transition-all duration-300">
                            <div class="aspect-[4/5] bg-gradient-to-br from-stone-50 to-green-50/50 flex items-center justify-center overflow-hidden">
                                @if($autre->photo_principale)
                                    <img src="{{ Storage::url($autre->photo_principale) }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <x-icon name="package" size="10" class="text-green-200" />
                                @endif
                            </div>
                            <div class="p-3.5">
                                <p class="font-bold text-sm text-stone-900 line-clamp-1 mb-1 group-hover:text-green-900 transition">{{ $autre->titre }}</p>
                                <p class="text-base font-black text-green-900">
                                    {{ number_format($autre->prix_detail, 0, ',', ' ') }} F
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    {{-- ═══════════════ FOOTER ═══════════════ --}}
    <footer class="bg-green-950 text-stone-400 py-10 px-4 mt-16">
        <div class="max-w-7xl mx-auto text-center">
            <div class="flex items-center justify-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-700 to-lime-600 flex items-center justify-center">
                    <x-icon name="sprout" size="5" class="text-white" />
                </div>
                <span class="font-black text-white text-base tracking-tight">Transform'Collines</span>
            </div>
            <p class="text-[10px] text-stone-500 mt-4">
                © 2026 Transform'Collines · Ulrich KASSIN et Priscille KINDOHO
            </p>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>