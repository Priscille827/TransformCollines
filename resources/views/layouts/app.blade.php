<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? "Transform'Collines" }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">

    {{-- NAV TOP --}}
    <nav class="bg-green-700 text-white sticky top-0 z-30 shadow-md">
        <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-1.5 hover:bg-green-600 rounded-lg transition">
                    <x-icon name="menu" size="6" />
                </button>
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-lg bg-white text-green-700 flex items-center justify-center">
                        <x-icon name="sprout" size="5" />
                    </div>
                    <span class="font-bold hidden sm:inline">Transform'Collines</span>
                </a>
            </div>

            <div class="flex items-center gap-2">
                {{-- Notifications --}}
                <a href="{{ route('notifications.index') }}" class="relative p-2 hover:bg-green-600 rounded-lg transition block">
    <x-icon name="bell" size="5" />
    @php $nbNotifs = auth()->user()->notifications()->where('lu', false)->count(); @endphp
    @if($nbNotifs > 0)
        <span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-amber-500 text-[10px] font-bold rounded-full flex items-center justify-center">
            {{ $nbNotifs > 9 ? '9+' : $nbNotifs }}
        </span>
    @endif
</a>

                {{-- User --}}
                <div class="flex items-center gap-2 pl-3 border-l border-green-600">
                    <div class="w-8 h-8 rounded-full bg-amber-400 text-green-900 flex items-center justify-center font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}
                    </div>
                    <div class="hidden sm:block text-sm">
                        <div class="font-medium leading-tight">{{ Str::limit(auth()->user()->nom, 20) }}</div>
                        <div class="text-xs text-green-200 leading-tight">{{ auth()->user()->commune->nom ?? '—' }}</div>
                    </div>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="p-2 hover:bg-green-600 rounded-lg transition" title="Déconnexion">
                        <x-icon name="log-out" size="5" />
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="flex">
        {{-- SIDEBAR --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:sticky top-0 lg:top-[61px] left-0 z-20 w-64 h-screen lg:h-[calc(100vh-61px)] bg-white border-r border-gray-200 transition-transform duration-200 overflow-y-auto">

            <nav class="p-4 space-y-1">
                @php $type = auth()->user()->type_compte; @endphp

                {{-- Tableau de bord --}}
                <a href="{{ route('dashboard') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('dashboard') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <x-icon name="layout-dashboard" size="5" />
                    Tableau de bord
                </a>

                {{-- Mes besoins (unité) --}}
                @if($type === 'unite_transformation' || $type === 'admin')
                    <a href="{{ route('besoins.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('besoins.index') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                        <x-icon name="clipboard-list" size="5" />
                        Mes besoins
                    </a>
                @endif
                @if($type === 'unite_transformation' || $type === 'admin')
    <a href="{{ route('receptions.index') }}"
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('receptions.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
        <x-icon name="package-check" size="5" />
        Réceptions
    </a>
@endif

<a href="{{ route('notations.index') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('notations.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
    <x-icon name="star" size="5" />
    Notations
</a>

                {{-- Mes disponibilités (producteur) --}}
                @if($type === 'producteur' || $type === 'admin')
                    <a href="{{ route('disponibilites.index') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('disponibilites.index') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                        <x-icon name="package" size="5" />
                        Mes disponibilités
                    </a>
                @endif

                {{-- Besoins actifs (tous) --}}
                <a href="{{ route('besoins.publics') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('besoins.publics') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <x-icon name="search" size="5" />
                    Besoins actifs
                </a>
                <a href="{{ route('cooperative.index') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('cooperative.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
    <x-icon name="users" size="5" />
    Coopérative virtuelle
</a>
<a href="{{ route('cooperative.mes') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('cooperative.mes') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
    <x-icon name="user-check" size="5" />
    Mes coopératives
</a>
                {{-- Carte --}}
                <a href="{{ route('carte') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('carte') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                    <x-icon name="map" size="5" />
                    Carte
                </a>

                {{-- Institutions --}}
                @if($type === 'institution' || $type === 'admin')
                    <a href="{{ route('institution.dashboard') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('institution.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
                        <x-icon name="bar-chart-3" size="5" />
                        Données institutionnelles
                    </a>
                @endif

<a href="{{ route('sms.simuler') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('sms.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
    <x-icon name="message-square" size="5" />
    Simulateur SMS
</a>
<a href="{{ route('ivr.simuler') }}"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('ivr.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
    <x-icon name="phone-call" size="5" />
    Assistant vocal IVR
</a>

@if($type === 'producteur' || $type === 'unite_transformation' || $type === 'admin')
    <a href="{{ route('fiabilite.moi') }}"
       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm transition {{ request()->routeIs('fiabilite.*') ? 'bg-green-50 text-green-700 font-medium' : 'text-gray-700 hover:bg-gray-50' }}">
        <x-icon name="shield-check" size="5" />
        Ma fiabilité
    </a>
@endif
                {{-- Profil --}}
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50 transition">
                        <x-icon name="user" size="5" />
                        Mon profil
                    </a>
                </div>
            </nav>
        </aside>

        {{-- Overlay mobile --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/40 z-10 lg:hidden" x-cloak></div>

        {{-- CONTENU --}}
        <main class="flex-1 min-w-0 p-4 sm:p-6 lg:p-8">
            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-start gap-2">
                    <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                    <span class="text-sm">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-start gap-2">
                    <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                    <span class="text-sm">{{ session('error') }}</span>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Lucide --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        // Initialise les icônes à chaque rendu (utile avec Livewire)
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
        document.addEventListener('livewire:navigated', () => lucide.createIcons());
    </script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>