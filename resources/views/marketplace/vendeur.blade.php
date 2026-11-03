<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $vendeur->utilisateur->nom }} — Marché des Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen">

    <nav class="bg-white/95 backdrop-blur-lg border-b border-stone-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <a href="{{ route('marketplace.index') }}" class="flex items-center gap-2 text-sm font-bold text-stone-700 hover:text-green-800 transition">
                <x-icon name="arrow-left" size="4" />
                <span>Retour au marché</span>
            </a>

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
            @endauth
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 py-8">

        {{-- Carte vendeur --}}
        <div class="bg-gradient-to-br from-green-900 via-green-800 to-green-700 rounded-3xl shadow-xl p-8 sm:p-10 mb-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-lime-400/20 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-green-400/20 rounded-full blur-3xl"></div>

            <div class="relative flex items-center gap-5 flex-wrap">
                <div class="w-20 h-20 rounded-3xl bg-white/15 backdrop-blur border-2 border-white/30 flex items-center justify-center text-white shadow-lg">
                    <x-icon :name="$type === 'unite' ? 'factory' : 'sprout'" size="10" />
                </div>

                <div class="text-white flex-1 min-w-0">
                    <span class="inline-block px-3 py-1 bg-white/15 backdrop-blur border border-white/20 rounded-full text-[10px] font-black uppercase tracking-widest mb-2">
                        {{ $type === 'unite' ? 'Unité de transformation' : 'Producteur local' }}
                    </span>
                    <h1 class="text-3xl sm:text-4xl font-black mb-1">{{ $vendeur->utilisateur->nom }}</h1>
                    <p class="text-green-100 flex items-center gap-2">
                        <x-icon name="map-pin" size="4" />
                        {{ $vendeur->utilisateur->commune->nom }}
                        @if($vendeur->utilisateur->village)
                            · {{ $vendeur->utilisateur->village }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Stats --}}
            <div class="relative grid grid-cols-3 gap-4 mt-8 pt-6 border-t border-white/20">
                <div>
                    <p class="text-3xl font-black text-lime-300">{{ $offres->total() }}</p>
                    <p class="text-xs text-green-100 uppercase tracking-wider font-bold mt-1">Produits</p>
                </div>
                <div>
                    <p class="text-3xl font-black text-lime-300">100%</p>
                    <p class="text-xs text-green-100 uppercase tracking-wider font-bold mt-1">Local</p>
                </div>
                <div>
                    <p class="text-3xl font-black text-lime-300">✓</p>
                    <p class="text-xs text-green-100 uppercase tracking-wider font-bold mt-1">Vérifié</p>
                </div>
            </div>
        </div>

        {{-- Produits --}}
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-black text-stone-900">
                Ses produits
                <span class="text-stone-400 text-lg font-bold ml-2">({{ $offres->total() }})</span>
            </h2>
        </div>

        @if($offres->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="package" size="16" class="mx-auto mb-4 text-stone-300" />
                <p class="font-bold text-stone-900 mb-2">Aucun produit en vente</p>
                <p class="text-sm text-stone-500">Ce vendeur n'a pas encore publié de produits.</p>
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">
                @foreach($offres as $offre)
                    <a href="{{ route('marketplace.show', $offre) }}"
                       class="group bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">

                        <div class="aspect-square bg-gradient-to-br from-green-50 to-lime-50 flex items-center justify-center overflow-hidden relative">
                            @if($offre->photo_principale)
                                <img src="{{ Storage::url($offre->photo_principale) }}"
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            @else
                                <x-icon name="package" size="16" class="text-green-300" />
                            @endif

                            @if($offre->prix_gros && $offre->seuil_gros)
                                <span class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-white text-[10px] font-black uppercase tracking-wider rounded-full shadow-md">
                                    Prix de gros
                                </span>
                            @endif
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-stone-900 text-sm leading-tight mb-2 line-clamp-2 min-h-[2.5rem]">
                                {{ $offre->titre }}
                            </h3>
                            <p class="text-xl font-black text-green-800">
                                {{ number_format($offre->prix_detail, 0, ',', ' ') }}
                                <span class="text-xs font-bold text-stone-400">F / {{ $offre->uniteMesure->nom }}</span>
                            </p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">{{ $offres->links() }}</div>
        @endif
    </main>

    <footer class="bg-stone-900 text-stone-400 py-8 px-4 mt-16">
        <div class="max-w-7xl mx-auto text-center text-xs">
            <p>© 2026 Transform'Collines · Développé par Ulrich KASSIN et Priscille KINDOHO</p>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>