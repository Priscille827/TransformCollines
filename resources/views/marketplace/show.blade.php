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
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen">

    {{-- NAV --}}
    <nav class="bg-white/95 backdrop-blur-lg border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('marketplace.index') }}" class="flex items-center gap-2 text-sm font-bold text-stone-700 hover:text-green-800 transition">
                <x-icon name="arrow-left" size="4" />
                <span>Retour au marché</span>
            </a>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('panier.index') }}" class="relative p-2.5 hover:bg-stone-100 rounded-xl transition">
                        <x-icon name="shopping-cart" size="5" class="text-stone-700" />
                        @php $nbPanier = \App\Models\PanierItem::where('utilisateur_id', auth()->id())->count(); @endphp
                        @if($nbPanier > 0)
                            <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-green-600 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                                {{ $nbPanier }}
                            </span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-xl transition">
                        Connexion
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-12">

            {{-- Photos --}}
            <div class="bg-white rounded-3xl shadow-lg border border-stone-100 overflow-hidden sticky top-24 self-start"
                 x-data="{ active: '{{ $offre->photo_principale ? Storage::url($offre->photo_principale) : '' }}' }">

                @if($offre->photos->count() > 0)
                    <div class="aspect-square bg-stone-50">
                        <img :src="active" alt="{{ $offre->titre }}" class="w-full h-full object-cover transition-opacity duration-300">
                    </div>

                    @if($offre->photos->count() > 1)
                        <div class="flex gap-2 p-3 overflow-x-auto bg-stone-50/50">
                            @foreach($offre->photos as $photo)
                                <button type="button"
                                        @click="active = '{{ Storage::url($photo->chemin) }}'"
                                        class="w-16 h-16 rounded-xl overflow-hidden border-2 transition flex-shrink-0"
                                        :class="active === '{{ Storage::url($photo->chemin) }}' ? 'border-green-600 shadow-md' : 'border-stone-200 hover:border-stone-300'">
                                    <img src="{{ Storage::url($photo->chemin) }}" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="aspect-square flex items-center justify-center bg-gradient-to-br from-green-50 to-lime-50">
                        <x-icon name="package" size="20" class="text-green-300" />
                    </div>
                @endif
            </div>

            {{-- Infos produit --}}
            <div>
                {{-- Catégorie + badges --}}
                <div class="flex items-center flex-wrap gap-2 mb-4">
                    @if($offre->categorie)
                        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs font-black uppercase tracking-wider rounded-full">
                            {{ $offre->categorie->nom }}
                        </span>
                    @endif

                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-stone-100 text-stone-700 text-xs font-bold rounded-full">
                        <x-icon :name="$offre->estPublieeParUnite() ? 'factory' : 'sprout'" size="3.5" />
                        {{ $offre->estPublieeParUnite() ? 'Unité de transformation' : 'Producteur' }}
                    </span>
                </div>

                {{-- Titre --}}
                <h1 class="text-3xl sm:text-4xl font-black text-stone-900 mb-4 leading-tight">
                    {{ $offre->titre }}
                </h1>

                {{-- Vendeur --}}
                <a href="{{ route('marketplace.vendeur', [$offre->estPublieeParUnite() ? 'unite' : 'producteur', $offre->vendeur()->id]) }}"
                   class="inline-flex items-center gap-3 mb-6 group">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center font-black shadow-md">
                        {{ strtoupper(substr($offre->nomVendeur(), 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-bold text-stone-900 group-hover:text-green-800 transition">{{ $offre->nomVendeur() }}</p>
                        <p class="text-xs text-stone-500 flex items-center gap-1">
                            <x-icon name="map-pin" size="3" />
                            {{ $offre->vendeur()?->utilisateur?->commune?->nom ?? '—' }}
                        </p>
                    </div>
                    <x-icon name="chevron-right" size="4" class="text-stone-400 group-hover:text-green-700 group-hover:translate-x-1 transition ml-auto" />
                </a>

                {{-- Prix --}}
                <div class="bg-gradient-to-br from-green-50 to-lime-50 rounded-3xl p-6 mb-6 border-2 border-green-100">
                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-4xl font-black text-green-800">
                            {{ number_format($offre->prix_detail, 0, ',', ' ') }}
                        </span>
                        <span class="text-lg font-bold text-green-700">FCFA</span>
                        <span class="text-sm text-stone-600">/ {{ $offre->uniteMesure->nom }}</span>
                    </div>

                    @if($offre->prix_gros && $offre->seuil_gros)
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-100 text-amber-800 rounded-full text-xs font-bold mt-2">
                            <x-icon name="trending-down" size="3.5" />
                            Prix de gros : {{ number_format($offre->prix_gros, 0, ',', ' ') }} F dès {{ number_format($offre->seuil_gros, 0, ',', ' ') }} {{ $offre->uniteMesure->nom }}s
                        </div>
                    @endif
                </div>

                {{-- Infos pratiques --}}
                <div class="space-y-3 mb-6">
                    <div class="flex items-center justify-between py-3 border-b border-stone-100">
                        <span class="text-sm text-stone-500 flex items-center gap-2">
                            <x-icon name="package" size="4" />
                            Disponible
                        </span>
                        <span class="font-bold text-stone-900">
                            {{ number_format($offre->quantite_disponible, 0, ',', ' ') }}
                            {{ $offre->uniteMesure->nom }}{{ $offre->quantite_disponible > 1 ? 's' : '' }}
                            @if($offre->quantite_kg_equivalent)
                                <span class="text-xs font-normal text-stone-500">· {{ number_format($offre->quantite_kg_equivalent, 0, ',', ' ') }} kg</span>
                            @endif
                        </span>
                    </div>

                    @if($offre->localisation)
                        <div class="flex items-center justify-between py-3 border-b border-stone-100">
                            <span class="text-sm text-stone-500 flex items-center gap-2">
                                <x-icon name="map-pin" size="4" />
                                Localisation
                            </span>
                            <span class="font-bold text-stone-900 text-sm text-right">{{ $offre->localisation }}</span>
                        </div>
                    @endif

                    @if($offre->conditions_retrait)
                        <div class="py-3 border-b border-stone-100">
                            <span class="text-sm text-stone-500 flex items-center gap-2 mb-2">
                                <x-icon name="info" size="4" />
                                Conditions
                            </span>
                            <p class="text-sm text-stone-700 leading-relaxed">{{ $offre->conditions_retrait }}</p>
                        </div>
                    @endif
                </div>

                {{-- Ajout au panier --}}
                @auth
                    @if(auth()->user()->isAdmin() || (auth()->user()->producteur?->id !== $offre->producteur_id))
                        <form method="POST" action="{{ route('panier.ajouter', $offre) }}"
                              class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6"
                              x-data="{ quantite: 1 }">
                            @csrf

                            <label class="block text-sm font-bold text-stone-800 mb-3">
                                Quantité à commander
                            </label>

                            <div class="flex items-center gap-3 mb-4">
                                <button type="button" @click="quantite = Math.max(0.01, quantite - 1)"
                                        class="w-12 h-12 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition font-bold">
                                    <x-icon name="minus" size="5" />
                                </button>

                                <div class="flex-1 relative">
                                    <input type="number" name="quantite" step="0.01" min="0.01" max="{{ $offre->quantite_disponible }}"
                                           x-model="quantite"
                                           class="w-full text-center rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-xl font-black">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-stone-500 font-bold pointer-events-none">
                                        {{ $offre->uniteMesure->nom }}{{ $offre->quantite_disponible > 1 ? 's' : '' }}
                                    </span>
                                </div>

                                <button type="button" @click="quantite = Math.min({{ $offre->quantite_disponible }}, quantite + 1)"
                                        class="w-12 h-12 rounded-2xl bg-stone-100 hover:bg-stone-200 text-stone-700 flex items-center justify-center transition font-bold">
                                    <x-icon name="plus" size="5" />
                                </button>
                            </div>

                            {{-- Total estimé --}}
                            <div class="bg-green-50 rounded-2xl p-4 mb-4 flex items-center justify-between">
                                <span class="text-sm text-green-800 font-bold">Total estimé</span>
                                <span class="text-xl font-black text-green-800">
                                    <span x-text="Math.round(quantite * {{ $offre->prixPourQuantite(1) }}).toLocaleString('fr-FR')"></span> F
                                </span>
                            </div>

                            <button type="submit"
                                    class="w-full bg-green-800 hover:bg-green-900 text-white font-black py-4 rounded-2xl transition shadow-lg shadow-green-800/20 flex items-center justify-center gap-2">
                                <x-icon name="shopping-cart" size="5" />
                                Ajouter au panier
                            </button>

                            <p class="text-[10px] text-stone-500 text-center mt-3">
                                Le paiement sera convenu directement avec le vendeur
                            </p>
                        </form>
                    @else
                        <div class="bg-amber-50 border border-amber-200 rounded-3xl p-5 flex items-start gap-3">
                            <x-icon name="alert-circle" size="5" class="text-amber-600 flex-shrink-0 mt-0.5" />
                            <p class="text-sm text-amber-900">C'est votre propre produit.</p>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="block w-full bg-green-800 hover:bg-green-900 text-white font-black py-4 rounded-2xl shadow-lg shadow-green-800/20 transition text-center flex items-center justify-center gap-2">
                        <x-icon name="log-in" size="5" />
                        Connectez-vous pour commander
                    </a>
                    <p class="text-xs text-stone-500 text-center mt-3">
                        Pas encore de compte ?
                        <a href="{{ route('register') }}" class="text-green-700 font-bold hover:underline">Inscrivez-vous gratuitement</a>
                    </p>
                @endauth
            </div>
        </div>

        {{-- Description --}}
        @if($offre->description)
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 sm:p-8 mb-12">
                <h2 class="text-xl font-black text-stone-900 mb-4 flex items-center gap-2">
                    <x-icon name="file-text" size="5" class="text-green-700" />
                    Description du produit
                </h2>
                <p class="text-stone-600 leading-relaxed whitespace-pre-line">{{ $offre->description }}</p>
            </div>
        @endif

        {{-- Autres offres du vendeur --}}
        @if($autresOffres->isNotEmpty())
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-black text-stone-900">Autres produits de ce vendeur</h2>
                    <a href="{{ route('marketplace.vendeur', [$offre->estPublieeParUnite() ? 'unite' : 'producteur', $offre->vendeur()->id]) }}"
                       class="text-sm font-bold text-green-800 hover:text-green-900 flex items-center gap-1">
                        Tout voir
                        <x-icon name="arrow-right" size="4" />
                    </a>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($autresOffres as $autre)
                        <a href="{{ route('marketplace.show', $autre) }}"
                           class="group bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all">
                            <div class="aspect-square bg-gradient-to-br from-green-50 to-lime-50 flex items-center justify-center overflow-hidden">
                                @if($autre->photo_principale)
                                    <img src="{{ Storage::url($autre->photo_principale) }}"
                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                @else
                                    <x-icon name="package" size="10" class="text-green-300" />
                                @endif
                            </div>
                            <div class="p-3">
                                <p class="font-bold text-xs text-stone-900 line-clamp-1 mb-1">{{ $autre->titre }}</p>
                                <p class="text-sm font-black text-green-800">
                                    {{ number_format($autre->prix_detail, 0, ',', ' ') }} F
                                </p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </main>

    <footer class="bg-stone-900 text-stone-400 py-8 px-4 mt-16">
        <div class="max-w-7xl mx-auto text-center text-xs">
            <p>© 2026 Transform'Collines · Développé par Ulrich KASSIN et Priscille KINDOHO</p>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>