<x-app-layout title="Publier un besoin">
    <div class="max-w-3xl mx-auto">

        {{-- Fil d'ariane --}}
        <a href="{{ route('besoins.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour à mes besoins
        </a>

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center shadow-xl shadow-green-500/20">
                <x-icon name="clipboard-list" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Publier un besoin</h1>
            <p class="text-stone-500">Indiquez ce que vous recherchez : les producteurs proches seront notifiés.</p>
        </div>

        {{-- Erreurs --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl mb-6 text-sm">
                <div class="flex items-start gap-2 mb-1">
                    <x-icon name="alert-circle" size="5" class="flex-shrink-0 mt-0.5" />
                    <p class="font-bold">Veuillez corriger :</p>
                </div>
                <ul class="list-disc list-inside space-y-0.5 mt-2 pl-7">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('besoins.store') }}"
              class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6 sm:p-8 space-y-6"
              x-data="{ modeZone: '{{ old('mode_zone', 'communes') }}' }">
            @csrf

            {{-- Produit --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Produit recherché <span class="text-red-500">*</span>
                </label>
                <select name="produit_id" required
                        class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                    <option value="">— Sélectionnez un produit brut —</option>
                    @foreach($produits as $p)
                        <option value="{{ $p->id }}" @selected(old('produit_id') == $p->id)>
                            {{ $p->nom }}
                        </option>
                    @endforeach
                </select>
                @error('produit_id')
                    <p class="text-xs text-red-600 mt-1.5 font-medium">{{ $message }}</p>
                @enderror
                <p class="text-xs text-stone-500 mt-1.5 flex items-center gap-1">
                    <x-icon name="info" size="3" />
                    Seuls les produits bruts (matières premières) sont disponibles
                </p>
            </div>

            {{-- Quantité + Délai --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Quantité recherchée (kg) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="quantite_recherchee" step="0.01" min="1"
                               value="{{ old('quantite_recherchee') }}" required
                               placeholder="Ex. 20000"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 pr-16 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono text-lg font-bold">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-500 font-bold">kg</span>
                    </div>
                    @error('quantite_recherchee')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-2">
                        Date limite <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="delai" value="{{ old('delai') }}" required
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-medium">
                    @error('delai')
                        <p class="text-xs text-red-600 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Zone de collecte --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-3">
                    Zone de collecte <span class="text-red-500">*</span>
                </label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                    <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                           :class="modeZone === 'communes' ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="mode_zone" value="communes" x-model="modeZone" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                 :class="modeZone === 'communes' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                                <x-icon name="list" size="5" />
                            </div>
                            <div>
                                <p class="font-bold text-sm" :class="modeZone === 'communes' ? 'text-green-800' : 'text-stone-700'">
                                    Par communes
                                </p>
                                <p class="text-xs text-stone-500">Choisir les communes ciblées</p>
                            </div>
                        </div>
                    </label>

                    <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                           :class="modeZone === 'rayon' ? 'border-amber-600 bg-amber-50 shadow-md shadow-amber-600/10' : 'border-stone-200 hover:border-stone-300'">
                        <input type="radio" name="mode_zone" value="rayon" x-model="modeZone" class="sr-only">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                 :class="modeZone === 'rayon' ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-500'">
                                <x-icon name="circle-dot" size="5" />
                            </div>
                            <div>
                                <p class="font-bold text-sm" :class="modeZone === 'rayon' ? 'text-amber-800' : 'text-stone-700'">
                                    Par rayon
                                </p>
                                <p class="text-xs text-stone-500">Rayon en km autour de vous</p>
                            </div>
                        </div>
                    </label>
                </div>

                {{-- Mode communes --}}
                <div x-show="modeZone === 'communes'" x-transition class="bg-green-50 border-2 border-green-100 rounded-2xl p-4">
                    <p class="text-sm text-stone-700 mb-3 font-medium">Sélectionnez les communes ciblées :</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                        @foreach($communes as $c)
                            <label class="flex items-center gap-2 p-3 bg-white border-2 border-stone-200 rounded-xl cursor-pointer hover:border-green-500 hover:bg-green-50/50 transition">
                                <input type="checkbox" name="communes[]" value="{{ $c->id }}"
                                       class="rounded border-stone-300 text-green-600 focus:ring-green-500"
                                       @checked(in_array($c->id, old('communes', [])))>
                                <span class="text-sm font-medium">{{ $c->nom }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('communes')
                        <p class="text-xs text-red-600 mt-2 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mode rayon --}}
                <div x-show="modeZone === 'rayon'" x-transition class="bg-amber-50 border-2 border-amber-100 rounded-2xl p-4">
                    <label class="block text-sm font-bold text-stone-800 mb-2">Rayon de collecte</label>
                    <div class="relative">
                        <input type="number" name="rayon_km" min="1" max="500" step="1"
                               value="{{ old('rayon_km', 50) }}"
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 pr-16 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono font-bold">
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-stone-500 font-bold">km</span>
                    </div>
                    <p class="text-xs text-stone-600 mt-2 flex items-center gap-1.5">
                        <x-icon name="info" size="3" />
                        Les producteurs situés dans ce rayon seront notifiés
                    </p>
                    @error('rayon_km')
                        <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Récap info --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-900">
                    <p class="font-bold mb-1">Après publication :</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-800">
                        <li>Le besoin sera visible sur la carte et dans les flux des producteurs</li>
                        <li>Une notification sera envoyée aux producteurs du produit dans la zone</li>
                        <li>Le taux de couverture se mettra à jour en temps réel</li>
                    </ul>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('besoins.index') }}"
                   class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-green-800 hover:bg-green-900 text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                    <x-icon name="send" size="5" />
                    Publier le besoin
                </button>
            </div>
        </form>
    </div>
</x-app-layout>