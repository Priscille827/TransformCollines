<x-app-layout title="Ma vente">
    <div class="max-w-4xl mx-auto">

        <a href="{{ route('offres.index') }}" class="inline-flex items-center gap-1.5 text-sm text-stone-500 hover:text-stone-800 mb-6 transition">
            <x-icon name="arrow-left" size="4" />
            Retour à mes ventes
        </a>

        {{-- Header --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                        <x-icon name="tag" size="7" />
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-black text-stone-900">{{ $offre->titre }}</h1>
                        <p class="text-sm text-stone-500">
                            {{ $offre->produit->nom }}
                            @if($offre->categorie) · {{ $offre->categorie->nom }} @endif
                        </p>
                    </div>
                </div>

                @php
                    $badge = match($offre->statut) {
                        'active'    => ['bg-green-100 text-green-800', 'En vente'],
                        'brouillon' => ['bg-stone-100 text-stone-600', 'Brouillon'],
                        'epuisee'   => ['bg-amber-100 text-amber-800', 'Épuisée'],
                        'archivee'  => ['bg-stone-100 text-stone-500', 'Archivée'],
                        default     => ['bg-stone-100 text-stone-700', $offre->statut],
                    };
                @endphp
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold {{ $badge[0] }}">
                    {{ $badge[1] }}
                </span>
            </div>

            {{-- Infos rapides --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-5 border-t border-stone-100">
                <div>
                    <p class="text-[10px] text-stone-500 font-bold uppercase tracking-wider mb-1">Quantité</p>
                    <p class="font-bold text-stone-900">{{ number_format($offre->quantite_disponible, 0, ',', ' ') }} {{ $offre->uniteMesure->nom }}{{ $offre->quantite_disponible > 1 ? 's' : '' }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-stone-500 font-bold uppercase tracking-wider mb-1">Prix détail</p>
                    <p class="font-bold text-green-700">{{ number_format($offre->prix_detail, 0, ',', ' ') }} F</p>
                </div>
                @if($offre->prix_gros)
                    <div>
                        <p class="text-[10px] text-stone-500 font-bold uppercase tracking-wider mb-1">Prix gros</p>
                        <p class="font-bold text-amber-700">{{ number_format($offre->prix_gros, 0, ',', ' ') }} F</p>
                    </div>
                @endif
                <div>
                    <p class="text-[10px] text-stone-500 font-bold uppercase tracking-wider mb-1">Vues</p>
                    <p class="font-bold text-stone-900">{{ $offre->vues }}</p>
                </div>
            </div>
        </div>

        {{-- Photos --}}
        <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-bold text-stone-900 flex items-center gap-2">
                    <x-icon name="image" size="5" class="text-green-700" />
                    Photos ({{ $offre->photos->count() }})
                </h2>
                <p class="text-xs text-stone-500">Max 3 Mo · JPG, PNG, WebP</p>
            </div>

            {{-- Bandeau info si pas de photo --}}
            @if($offre->photos->isEmpty())
                <div class="bg-amber-50 border-l-4 border-amber-500 rounded-r-2xl p-4 mb-5 flex items-start gap-3">
                    <x-icon name="alert-triangle" size="5" class="text-amber-600 flex-shrink-0 mt-0.5" />
                    <p class="text-sm text-amber-900">
                        <strong>Ajoutez au moins une photo</strong> pour que votre annonce soit visible et attractive sur le marché.
                    </p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-5">
                    @foreach($offre->photos as $photo)
                        <div class="relative group aspect-square rounded-2xl overflow-hidden border-2 {{ $offre->photo_principale === $photo->chemin ? 'border-green-500' : 'border-stone-100' }}">
                            <img src="{{ Storage::url($photo->chemin) }}" alt="Photo" class="w-full h-full object-cover">

                            {{-- Badge principale --}}
                            @if($offre->photo_principale === $photo->chemin)
                                <span class="absolute top-2 left-2 px-2 py-0.5 bg-green-600 text-white text-[10px] rounded-full font-bold flex items-center gap-1">
                                    <x-icon name="star" size="3" />
                                    PRINCIPALE
                                </span>
                            @endif

                            {{-- Actions --}}
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                @if($offre->photo_principale !== $photo->chemin)
                                    <form method="POST" action="{{ route('offres.photos.principale', $photo) }}">
                                        @csrf
                                        <button type="submit" class="p-2 bg-white rounded-full hover:bg-amber-50 transition" title="Définir comme principale">
                                            <x-icon name="star" size="4" class="text-amber-500" />
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('offres.photos.destroy', $photo) }}"
                                      onsubmit="return confirm('Supprimer cette photo ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 bg-white rounded-full hover:bg-red-50 transition" title="Supprimer">
                                        <x-icon name="trash-2" size="4" class="text-red-500" />
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Upload --}}
            <form method="POST" action="{{ route('offres.photos.store', $offre) }}"
                  enctype="multipart/form-data"
                  x-data="{ fichier: null }">
                @csrf

                <label class="block cursor-pointer">
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required
                           class="sr-only"
                           @change="fichier = $event.target.files[0]?.name || null">

                    <div class="border-2 border-dashed border-stone-300 rounded-2xl p-6 text-center hover:border-green-500 hover:bg-green-50/30 transition"
                         :class="fichier ? 'border-green-500 bg-green-50/50' : ''">
                        <div x-show="!fichier">
                            <div class="w-12 h-12 mx-auto mb-3 rounded-2xl bg-stone-100 text-stone-500 flex items-center justify-center">
                                <x-icon name="upload" size="6" />
                            </div>
                            <p class="font-bold text-stone-700 mb-1">Cliquez pour ajouter une photo</p>
                            <p class="text-xs text-stone-500">ou glissez-déposez votre image ici</p>
                        </div>
                        <div x-show="fichier" class="flex items-center justify-center gap-3">
                            <x-icon name="check-circle-2" size="6" class="text-green-600" />
                            <span class="font-bold text-green-700" x-text="fichier"></span>
                        </div>
                    </div>
                </label>

                <button type="submit" x-show="fichier" x-transition
                        class="mt-4 w-full bg-green-800 hover:bg-green-900 text-white font-bold py-3 rounded-2xl transition flex items-center justify-center gap-2">
                    <x-icon name="upload" size="5" />
                    Envoyer la photo
                </button>
            </form>
        </div>

        {{-- Description --}}
        @if($offre->description)
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
                <h2 class="font-bold text-stone-900 mb-3 flex items-center gap-2">
                    <x-icon name="file-text" size="5" class="text-stone-500" />
                    Description
                </h2>
                <p class="text-sm text-stone-600 leading-relaxed whitespace-pre-line">{{ $offre->description }}</p>
            </div>
        @endif

        {{-- Actions --}}
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <a href="{{ route('marketplace.show', $offre) }}"
               class="inline-flex items-center gap-2 px-5 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-2xl font-bold text-sm transition">
                <x-icon name="eye" size="4" />
                Voir sur le marché
            </a>

            <div class="flex items-center gap-3">
                <a href="{{ route('offres.edit', $offre) }}"
                   class="inline-flex items-center gap-2 px-5 py-3 bg-stone-900 hover:bg-stone-800 text-white rounded-2xl font-bold text-sm transition">
                    <x-icon name="pencil" size="4" />
                    Modifier
                </a>
            </div>
        </div>
    </div>
</x-app-layout>