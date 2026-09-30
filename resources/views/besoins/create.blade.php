<x-app-layout title="Publier un besoin">
    <div class="max-w-3xl mx-auto">

        {{-- Fil d'ariane --}}
        <div class="mb-6">
            <a href="{{ route('besoins.index') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5">
                <x-icon name="arrow-left" size="4" />
                Retour à mes besoins
            </a>
            <h1 class="text-2xl font-bold text-gray-900 mt-3">Publier un besoin</h1>
            <p class="text-gray-500 text-sm mt-1">Indiquez ce que vous recherchez : les producteurs proches seront notifiés.</p>
        </div>

        {{-- Erreurs --}}
        @if ($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg text-sm flex items-start gap-2">
                <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                <ul class="space-y-0.5">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('besoins.store') }}"
              class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-6"
              x-data="{ modeZone: '{{ old('mode_zone', 'communes') }}' }">
            @csrf

            {{-- Produit --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    Produit recherché <span class="text-red-500">*</span>
                </label>
                <select name="produit_id" required
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <option value="">— Sélectionnez —</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>{{ $p->nom }}</option>
                    @endforeach
                </select>
                @error('produit_id')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Quantité + Délai --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Quantité recherchée (kg) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="quantite_recherchee" step="0.01" min="1"
                           value="{{ old('quantite_recherchee') }}" required
                           placeholder="Ex. 20000"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    @error('quantite_recherchee')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Date limite <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="delai" value="{{ old('delai') }}" required
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    @error('delai')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Zone de collecte --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    Zone de collecte <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <label class="cursor-pointer border-2 rounded-lg p-3 transition"
                           :class="modeZone === 'communes' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="mode_zone" value="communes" x-model="modeZone" class="sr-only">
                        <div class="flex items-center gap-2">
                            <x-icon name="list" size="5" class="text-gray-600" />
                            <span class="font-medium text-sm">Par communes</span>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-lg p-3 transition"
                           :class="modeZone === 'rayon' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="mode_zone" value="rayon" x-model="modeZone" class="sr-only">
                        <div class="flex items-center gap-2">
                            <x-icon name="circle-dot" size="5" class="text-gray-600" />
                            <span class="font-medium text-sm">Par rayon (km)</span>
                        </div>
                    </label>
                </div>

                {{-- Mode communes --}}
                <div x-show="modeZone === 'communes'" x-transition class="bg-green-50 border border-green-100 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-3">Sélectionnez les communes ciblées :</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($communes as $c)
                            <label class="flex items-center gap-2 p-2 bg-white border border-gray-200 rounded cursor-pointer hover:bg-gray-50 transition">
                                <input type="checkbox" name="communes[]" value="{{ $c->id }}"
                                       class="rounded border-gray-300 text-green-600 focus:ring-green-500"
                                       @checked(in_array($c->id, old('communes', [])))>
                                <span class="text-sm">{{ $c->nom }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('communes')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mode rayon --}}
                <div x-show="modeZone === 'rayon'" x-transition class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Rayon de collecte (km)</label>
                    <input type="number" name="rayon_km" min="1" max="500" step="1"
                           value="{{ old('rayon_km', 50) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                    <p class="text-xs text-gray-500 mt-1.5">
                        Les producteurs situés dans un rayon de ce nombre de km autour de votre unité seront notifiés.
                    </p>
                    @error('rayon_km')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Récap info --}}
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-900">
                    <p class="font-medium">Après publication :</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-800">
                        <li>Le besoin sera visible sur la carte et dans les flux des producteurs</li>
                        <li>Une notification sera envoyée aux producteurs du produit dans la zone</li>
                        <li>Le taux de couverture se mettra à jour en temps réel</li>
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <a href="{{ route('besoins.index') }}"
                   class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg transition">
                    Annuler
                </a>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg shadow-sm transition flex items-center gap-2">
                    <x-icon name="send" size="4" />
                    Publier le besoin
                </button>
            </div>
        </form>
    </div>
</x-app-layout>