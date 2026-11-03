<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? "Transform'Collines" }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link {
            position: relative;
            transition: all 0.2s ease;
        }
        .sidebar-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 60%;
            background: linear-gradient(180deg, #15803d, #84cc16);
            border-radius: 0 4px 4px 0;
        }
    </style>
</head>
<body class="font-sans antialiased bg-[#fafaf9]" x-data="{ sidebarOpen: false }">

   @php
    $user = auth()->user();
    $type = $user->type_compte;
    $initial = strtoupper(substr($user->nom, 0, 1));
    $nbChatGlobal = $user->messagesNonLusCount();
@endphp

    {{-- ═══════════════ TOP BAR ═══════════════ --}}
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-30 backdrop-blur-lg bg-white/95">
        <div class="px-4 sm:px-6 py-3 flex items-center justify-between gap-4">

            {{-- Logo + burger --}}
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 hover:bg-stone-100 rounded-xl transition">
                    <x-icon name="menu" size="5" class="text-stone-700" />
                </button>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-lime-400 rounded-xl blur opacity-40 group-hover:opacity-60 transition"></div>
                        <div class="relative w-10 h-10 rounded-xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center shadow-md">
                            <x-icon name="sprout" size="6" class="text-white" />
                        </div>
                    </div>
                    <span class="font-black text-green-900 hidden sm:inline text-lg tracking-tight">Transform'Collines</span>
                </a>
            </div>

            {{-- Actions droite --}}
            <div class="flex items-center gap-2">

                {{-- Marketplace quick link --}}
                <a href="{{ route('marketplace.index') }}"
                   class="hidden md:inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-100 rounded-xl transition">
                    <x-icon name="shopping-basket" size="4" />
                    Marché
                </a>

                {{-- Notifications --}}
                <a href="{{ route('notifications.index') }}" class="relative p-2 hover:bg-stone-100 rounded-xl transition">
    <x-icon name="bell" size="5" class="text-stone-700" />
    @php $nbNotifs = $user->notifications()->where('lu', false)->count(); @endphp
    @if($nbNotifs > 0)
        <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
            {{ $nbNotifs > 9 ? '9+' : $nbNotifs }}
        </span>
    @endif
</a>

                {{-- User --}}
                <div class="hidden sm:flex items-center gap-2 pl-3 border-l border-stone-200">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-amber-500 text-white flex items-center justify-center font-black text-sm shadow-sm">
                        {{ $initial }}
                    </div>
                    <div class="hidden md:block">
                        <div class="font-bold text-stone-900 text-sm leading-tight">{{ Str::limit($user->nom, 18) }}</div>
                        <div class="text-xs text-stone-500 leading-tight">{{ $user->commune->nom ?? '—' }}</div>
                    </div>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="p-2 hover:bg-red-50 text-stone-500 hover:text-red-600 rounded-xl transition" title="Déconnexion">
                        <x-icon name="log-out" size="5" />
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="flex">

        {{-- ═══════════════ SIDEBAR ═══════════════ --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:sticky top-0 lg:top-[65px] left-0 z-20 w-72 h-screen lg:h-[calc(100vh-65px)] bg-white border-r border-stone-200 transition-transform duration-200 overflow-y-auto">

            <nav class="p-4 space-y-1">

                {{-- ═══ SECTION : Navigation (tous) ═══ --}}
                <div class="pb-3 mb-3 border-b border-stone-100">
                    <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                        Navigation
                    </p>

                    {{-- Tableau de bord --}}
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                              {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                        <x-icon name="layout-dashboard" size="5" />
                        Tableau de bord
                    </a>

                    {{-- Marketplace --}}
                    <a href="{{ route('marketplace.index') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                              {{ request()->routeIs('marketplace.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                        <x-icon name="shopping-basket" size="5" />
                        Marketplace
                    </a>
                </div>

                {{-- ═══════════════════════════════════════════════════════ --}}
                {{-- ═══ PRODUCTEUR ═══ --}}
                {{-- ═══════════════════════════════════════════════════════ --}}
                @if($type === 'producteur')

                    {{-- Mon activité --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Mon activité
                        </p>

                        {{-- Mes produits --}}
                        <a href="{{ route('produits.choisir') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('produits.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="package" size="5" />
                            Mes produits
                        </a>

                        {{-- Mes propositions --}}
                        <a href="{{ route('disponibilites.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('disponibilites.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="send" size="5" />
                            Mes propositions
                        </a>

                        {{-- Mon stock --}}
                        <a href="{{ route('stocks.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('stocks.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="warehouse" size="5" />
                            Mon stock
                        </a>

                        {{-- Mes ventes --}}
                        <a href="{{ route('offres.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('offres.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="tag" size="5" />
                            Mes ventes
                       <a href="{{ route('suivi.index') }}"
   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
          {{ request()->routeIs('suivi.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
    <x-icon name="bar-chart-3" size="5" />
    Suivi financier
</a>
<a href="{{ route('suivi.payouts') }}"
   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
          {{ request()->routeIs('suivi.payouts*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
    <x-icon name="wallet" size="5" />
    Mes paiements
</a>
                        {{-- Besoins actifs --}}
                        <a href="{{ route('besoins.publics') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('besoins.publics') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="search" size="5" />
                            Besoins actifs
                        </a>
                    </div>

                    {{-- Ventes --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Ventes
                        </p>

                        {{-- Commandes reçues --}}
                        <a href="{{ route('commandes.recues.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('commandes.recues.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="inbox" size="5" />
                            Commandes reçues
                            @php
                                $nbEnAttente = \App\Models\Commande::where('statut', 'en_attente')
                                    ->where('producteur_id', $user->producteur->id)
                                    ->count();
                            @endphp
                            @if($nbEnAttente > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 bg-amber-500 text-white text-xs font-bold rounded-full">
                                    {{ $nbEnAttente }}
                                </span>
                            @endif
                        </a>

                        {{-- Panier --}}
                        <a href="{{ route('panier.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('panier.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shopping-cart" size="5" />
                            Mon panier
                        </a>

                        {{-- Mes commandes --}}
                        <a href="{{ route('commandes.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('commandes.index', 'commandes.show') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shopping-bag" size="5" />
                            Mes commandes
                        </a>
                    </div>

                    {{-- Découverte --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Découverte
                        </p>

                        <a href="{{ route('carte') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('carte') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="map" size="5" />
                            Carte
                        </a>
                    </div>
                @endif

                {{-- ═══════════════════════════════════════════════════════ --}}
                {{-- ═══ UNITÉ DE TRANSFORMATION ═══ --}}
                {{-- ═══════════════════════════════════════════════════════ --}}
                @if($type === 'unite_transformation')

                    {{-- Mon activité --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Mon activité
                        </p>

                        {{-- Mes produits --}}
                        <a href="{{ route('produits.choisir') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('produits.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="package" size="5" />
                            Mes produits
                        </a>

                        {{-- Mon stock --}}
                        <a href="{{ route('stocks.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('stocks.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="warehouse" size="5" />
                            Mon stock
                        </a>

                        {{-- Mes besoins --}}
                        <a href="{{ route('besoins.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('besoins.index') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="clipboard-list" size="5" />
                            Mes besoins
                        </a>

                        {{-- Besoins actifs --}}
                        <a href="{{ route('besoins.publics') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('besoins.publics') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="search" size="5" />
                            Besoins actifs
                        </a>

                        {{-- Réceptions --}}
                        <a href="{{ route('receptions.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('receptions.index', 'receptions.create', 'receptions.store') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="package-check" size="5" />
                            Réceptions
                        </a>

                        {{-- Historique réceptions --}}
                        <a href="{{ route('receptions.historique') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('receptions.historique') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="history" size="5" />
                            Historique réceptions
                        </a>

                        {{-- Mes ventes --}}
                        <a href="{{ route('offres.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('offres.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="tag" size="5" />
                            Mes ventes
                        </a>

                        <a href="{{ route('suivi.index') }}"
   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
          {{ request()->routeIs('suivi.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
    <x-icon name="bar-chart-3" size="5" />
    Suivi financier
</a>
<a href="{{ route('suivi.payouts') }}"
   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
          {{ request()->routeIs('suivi.payouts*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
    <x-icon name="wallet" size="5" />
    Mes paiements
</a>
                    </div>

                    {{-- Ventes --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Ventes
                        </p>

                        {{-- Commandes reçues --}}
                        <a href="{{ route('commandes.recues.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('commandes.recues.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="inbox" size="5" />
                            Commandes reçues
                            @php
                                $nbEnAttente = \App\Models\Commande::where('statut', 'en_attente')
                                    ->whereHas('lignes.offre', fn($s) => $s->where('unite_id', $user->unite->id))
                                    ->count();
                            @endphp
                            @if($nbEnAttente > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 bg-amber-500 text-white text-xs font-bold rounded-full">
                                    {{ $nbEnAttente }}
                                </span>
                            @endif
                        </a>

                        {{-- Panier --}}
                        <a href="{{ route('panier.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('panier.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shopping-cart" size="5" />
                            Mon panier
                        </a>

                        {{-- Mes commandes --}}
                        <a href="{{ route('commandes.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('commandes.index', 'commandes.show') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shopping-bag" size="5" />
                            Mes commandes
                        </a>
                    </div>

                    {{-- Découverte --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Découverte
                        </p>

                        <a href="{{ route('carte') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('carte') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="map" size="5" />
                            Carte
                        </a>
                    </div>
                @endif

                {{-- ═══════════════════════════════════════════════════════ --}}
                {{-- ═══ POPULATION ═══ --}}
                {{-- ═══════════════════════════════════════════════════════ --}}
                @if($type === 'population')
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Mes achats
                        </p>

                        {{-- Panier --}}
                        <a href="{{ route('panier.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('panier.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shopping-cart" size="5" />
                            Mon panier
                        </a>

                        {{-- Mes commandes --}}
                        <a href="{{ route('commandes.index') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('commandes.index', 'commandes.show') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shopping-bag" size="5" />
                            Mes commandes
                        </a>
                    </div>
                @endif

                {{-- ═══════════════════════════════════════════════════════ --}}
                {{-- ═══ ADMIN ═══ --}}
                {{-- ═══════════════════════════════════════════════════════ --}}
                @if($type === 'admin')

                    {{-- Administration --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Administration
                        </p>

                        {{-- Dashboard admin --}}
                        <a href="{{ route('admin.dashboard') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('admin.dashboard') ? 'bg-amber-50 text-amber-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="shield-check" size="5" />
                            Tableau de bord admin
                        </a>

                        {{-- Comptes --}}
                        <a href="{{ route('admin.comptes') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('admin.comptes*') ? 'bg-amber-50 text-amber-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="users" size="5" />
                            Comptes
                            @php $nbComptes = \App\Models\Utilisateur::where('statut_validation', 'en_attente')->count(); @endphp
                            @if($nbComptes > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 bg-amber-500 text-white text-xs font-bold rounded-full">
                                    {{ $nbComptes }}
                                </span>
                            @endif
                        </a>

                        {{-- Unités de mesure --}}
                        <a href="{{ route('admin.unites-mesure') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('admin.unites-mesure*') ? 'bg-amber-50 text-amber-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="ruler" size="5" />
                            Unités de mesure
                        </a>

                        {{-- Catalogue produits --}}
                        <a href="{{ route('admin.produits') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('admin.produits*') ? 'bg-amber-50 text-amber-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="package" size="5" />
                            Catalogue produits
                        </a>

                        {{-- Propositions --}}
                        <a href="{{ route('admin.propositions') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('admin.propositions*') ? 'bg-amber-50 text-amber-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="package-plus" size="5" />
                            Propositions
                            @php $nbPropositions = \App\Models\PropositionProduit::where('statut', 'a_verifier')->count(); @endphp
                            @if($nbPropositions > 0)
                                <span class="ml-auto inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 bg-amber-500 text-white text-xs font-bold rounded-full">
                                    {{ $nbPropositions }}
                                </span>
                            @endif
                        </a>

                        <a href="{{ route('admin.payouts') }}"
   class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
          {{ request()->routeIs('admin.payouts*') ? 'bg-amber-50 text-amber-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
    <x-icon name="wallet" size="5" />
    Payouts
</a>
                    </div>

                    {{-- Supervision --}}
                    <div class="pb-3 mb-3 border-b border-stone-100">
                        <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                            Supervision
                        </p>

                        {{-- Besoins actifs --}}
                        <a href="{{ route('besoins.publics') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('besoins.publics') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="search" size="5" />
                            Besoins actifs
                        </a>

                        {{-- Carte --}}
                        <a href="{{ route('carte') }}"
                           class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                  {{ request()->routeIs('carte') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                            <x-icon name="map" size="5" />
                            Carte
                        </a>
                    </div>
                @endif

                {{-- ═══ Mon compte ═══ --}}
                <div class="pt-2">
                    <p class="px-3 pb-2 text-[10px] font-black text-stone-400 uppercase tracking-widest">
                        Mon compte
                    </p>
                    @if((auth()->user()->isProducteur() || auth()->user()->isUnite()) && auth()->user()->isEnAttente())
                        <a href="{{ route('documents.index') }}"
                        class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                                {{ request()->routeIs('documents.*') ? 'bg-amber-50 text-amber-800 active' : 'text-amber-700 hover:bg-amber-50' }}">
                            <x-icon name="file-badge" size="5" />
                            Mon dossier
                            @if(!auth()->user()->aUnDocument())
                                <span class="ml-auto w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}"
                       class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                              {{ request()->routeIs('profile.*') ? 'bg-green-50 text-green-800 active' : 'text-stone-700 hover:bg-stone-50' }}">
                        <x-icon name="user" size="5" />
                        Mon profil
                    </a>
                </div>

            </nav>
        </aside>

        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/40 z-10 lg:hidden" x-cloak></div>

        {{-- ═══════════════ CONTENU ═══════════════ --}}
       <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8 pb-24 bg-[#fafaf9]">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3 shadow-sm">
                    <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5 text-green-600" />
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-50 border-l-4 border-red-600 text-red-800 px-4 py-3 rounded-r-xl flex items-start gap-3 shadow-sm">
                    <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5 text-red-600" />
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-red-50 border-l-4 border-red-600 text-red-800 px-4 py-3 rounded-r-xl shadow-sm">
                    <div class="flex items-start gap-3">
                        <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5 text-red-600" />
                        <div>
                            <p class="text-sm font-bold mb-1">Veuillez corriger :</p>
                            <ul class="text-sm space-y-0.5 list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Lucide --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>
    
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- ═══════════════ CHAT FLOTTANT ═══════════════ --}}
    @unless(request()->routeIs('chat.*'))
        <a href="{{ route('chat.index') }}"
           class="fixed bottom-6 right-6 z-40 group"
           title="Messages">
            @if($nbChatGlobal > 0)
                <span class="absolute inset-0 rounded-full bg-green-500 opacity-50 animate-ping"></span>
            @endif

            <div class="relative w-14 h-14 rounded-full bg-gradient-to-br from-green-700 to-green-600 hover:from-green-800 hover:to-green-700 text-white shadow-xl shadow-green-900/30 flex items-center justify-center transition-transform group-hover:scale-105">
                <x-icon name="message-circle" size="6" />

                @if($nbChatGlobal > 0)
                    <span class="absolute -top-1 -right-1 min-w-[20px] h-5 px-1.5 bg-red-600 text-white text-xs font-black rounded-full flex items-center justify-center border-2 border-white">
                        {{ $nbChatGlobal > 9 ? '9+' : $nbChatGlobal }}
                    </span>
                @endif
            </div>

            <span class="absolute bottom-full right-0 mb-2 px-3 py-1.5 bg-stone-900 text-white text-xs font-bold rounded-lg whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none">
                Messages
            </span>
        </a>
    @endunless

</body>
</html>