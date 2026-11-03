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
<body class="font-sans antialiased bg-gray-50 min-h-screen">

    {{-- Nav publique --}}
    <nav class="bg-white border-b border-gray-100 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center">
                    <x-icon name="sprout" size="5" class="text-white" />
                </div>
                <span class="font-bold text-green-800">Transform'Collines</span>
            </a>

            <div class="flex items-center gap-2">
                <a href="{{ route('marketplace.index') }}"
                   class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition flex items-center gap-1.5">
                    <x-icon name="shopping-basket" size="4" />
                    <span class="hidden sm:inline">Marché</span>
                </a>

                @auth
                    <a href="{{ route('panier.index') }}"
                       class="relative px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        <x-icon name="shopping-cart" size="5" />
                    </a>
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                        Mon espace
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}"
                       class="px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition">
                        S'inscrire
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    {{ $slot }}

    <footer class="bg-gray-900 text-gray-400 py-8 px-4 mt-16">
        <div class="max-w-7xl mx-auto text-center text-xs">
            <p>© 2026 Transform'Collines — Développé par Ulrich KASSIN et Priscille KINDOHO</p>
        </div>
    </footer>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>