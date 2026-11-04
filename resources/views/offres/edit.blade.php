<x-app-layout title="Modifier l'offre">
    <div class="max-w-3xl mx-auto py-8">
        
        {{-- En-tête --}}
        <div class="mb-8 flex items-center justify-between gap-4">
            <div>
                <a href="{{ route('offres.index') }}" class="text-sm font-bold text-stone-500 hover:text-stone-900 flex items-center gap-1.5 mb-2 transition">
                    <x-icon name="arrow-left" size="4" />
                    Retour à mes ventes
                </a>
                <h1 class="text-3xl font-black text-stone-900 flex items-center gap-2">
                    <x-icon name="pencil" size="7" class="text-green-700" />
                    Modifier l'offre
                </h1>
                <p class="text-stone-500 text-sm mt-1">{{ $offre->titre }}</p>
            </div>
        </div>

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('offres.update', $offre) }}" class="bg-white rounded-2xl border border-stone-100 p-6 sm:p-8 shadow-sm">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                {{-- Titre --}}
                <div>
                    <label class="block text-sm font-bold text-stone-900 mb-2">Titre de l'offre <span class="text-red-500">*</span></label>
                    <input type="text" name="titre" value="{{ old('titre', $offre->titre) }}" required
                           class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white">
                    @error('titre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-bold text-stone-900 mb-2">Description</label>
                    <textarea name="description" rows="4"
                              class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white resize-none">{{ old('description', $offre->description) }}</textarea>
                    @error('description') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Prix --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-stone-900 mb-2">Prix détail (FCFA) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" name="prix_detail" value="{{ old('prix_detail', $offre->prix_detail) }}" required min="0" step="1"
                                   class="w-full border border-stone-200 rounded-xl pl-4 pr-10 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-400">F</span>
                        </div>
                        @error('prix_detail') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-900 mb-2">Prix gros (FCFA)</label>
                        <div class="relative">
                            <input type="number" name="prix_gros" value="{{ old('prix_gros', $offre->prix_gros) }}" min="0" step="1"
                                   class="w-full border border-stone-200 rounded-xl pl-4 pr-10 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white">
                            <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-stone-400">F</span>
                        </div>
                        @error('prix_gros') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Seuil de gros --}}
                <div>
                    <label class="block text-sm font-bold text-stone-900 mb-2">Quantité minimum pour le prix de gros</label>
                    <input type="number" name="seuil_gros" value="{{ old('seuil_gros', $offre->seuil_gros) }}" min="0" step="1"
                           class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white">
                    <p class="text-xs text-stone-500 mt-1.5">Laissez vide si vous ne proposez pas de prix de gros.</p>
                    @error('seuil_gros') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                {{-- Infos optionnelles --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-stone-900 mb-2">Localisation</label>
                        <input type="text" name="localisation" value="{{ old('localisation', $offre->localisation) }}"
                               class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-stone-900 mb-2">Statut</label>
                        <select name="statut" class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white cursor-pointer">
                            <option value="active" @selected($offre->statut === 'active')>Active (Visible sur le marché)</option>
                            <option value="brouillon" @selected($offre->statut === 'brouillon')>Brouillon</option>
                            <option value="epuisee" @selected($offre->statut === 'epuisee')>Épuisée</option>
                            <option value="archivee" @selected($offre->statut === 'archivee')>Archivée</option>
                        </select>
                        @error('statut') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                {{-- Conditions de retrait --}}
                <div>
                    <label class="block text-sm font-bold text-stone-900 mb-2">Conditions de retrait</label>
                    <textarea name="conditions_retrait" rows="3"
                              class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-green-600/20 focus:border-green-600 outline-none transition bg-stone-50 focus:bg-white resize-none">{{ old('conditions_retrait', $offre->conditions_retrait) }}</textarea>
                    @error('conditions_retrait') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 mt-8 pt-6 border-t border-stone-100">
                <a href="{{ route('offres.index') }}" 
                   class="px-5 py-2.5 text-sm font-bold text-stone-600 hover:bg-stone-100 rounded-xl transition">
                    Annuler
                </a>
                <button type="submit" 
                        class="inline-flex items-center gap-2 bg-green-900 hover:bg-green-950 text-white font-bold px-6 py-2.5 rounded-xl shadow-lg shadow-green-900/20 transition">
                    <x-icon name="check" size="4" />
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</x-app-layout>