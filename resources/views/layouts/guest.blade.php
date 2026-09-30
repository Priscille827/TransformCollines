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
<body class="font-sans antialiased bg-gradient-to-br from-green-50 via-white to-amber-50 min-h-screen">

    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        {{-- Logo --}}
        <a href="/" class="mb-6 flex items-center gap-2.5 group">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center shadow-md group-hover:shadow-lg transition">
                <x-icon name="sprout" size="6" class="text-white" />
            </div>
            <span class="text-xl font-bold text-green-800">Transform'Collines</span>
        </a>

        {{ $slot }}

        <p class="mt-8 text-xs text-gray-400">Hackathon 41 · Salon du Numérique des Collines</p>
    </div>

    {{-- Lucide --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>