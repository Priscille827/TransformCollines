<x-app-layout title="Catalogue produits">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Catalogue produits</h1>
                <p class="text-stone-500">Gérez l'ensemble des produits de la plateforme</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.propositions') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    <x-icon name="package-plus" size="4" />
                    Propositions
                    @php $nbProp = \App\Models\PropositionProduit::where('statut', 'a_verifier')->count(); @endphp
                    @if($nbProp > 0)
                        <span class="ml-1 px-1.5 py-0.5 bg-white/30 rounded-full text-xs">{{ $nbProp }}</span>
                    @endif
                </a>
                <button onclick="document.getElementById('modal-nouveau').showModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-xl text-sm font-bold shadow-sm transition">
                    <x-icon name="plus" size="4" />
                    Nouveau produit
                </button>
            </div>
        </div>

        {{-- Stats cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4">
                <p class="text-xs text-stone-500 font-semibold uppercase tracking-wider">Total</p>
                <p class="text-2xl font-black text-stone-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-4">
                <p class="text-xs text-green-700 font-semibold uppercase tracking-wider">Actifs</p>
                <p class="text-2xl font-black text-green-700 mt-1">{{ $stats['actifs'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4">
                <p class="text-xs text-stone-500 font-semibold uppercase tracking-wider">Inactifs</p>
               <p class="text-2xl font-black text-stone-500 mt-1">{{ $stats['total'] - $stats['actifs'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-lime-200 p-4">
                <p class="text-xs text-lime-700 font-semibold uppercase tracking-wider">Bruts</p>
                <p class="text-2xl font-black text-lime-700 mt-1">{{ $stats['bruts'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-4">
                <p class="text-xs text-amber-700 font-semibold uppercase tracking-wider">Transformés</p>
                <p class="text-2xl font-black text-amber-700 mt-1">{{ $stats['transformes'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-blue-200 p-4">
                <p class="text-xs text-blue-700 font-semibold uppercase tracking-wider">Intrants</p>
                <p class="text-2xl font-black text-blue-700 mt-1">{{ $stats['intrants'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-stone-200 p-4">
                <p class="text-xs text-stone-700 font-semibold uppercase tracking-wider">Matériel</p>
                <p class="text-2xl font-black text-stone-700 mt-1">{{ $stats['materiels'] }}</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher un produit..."
                       class="border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">

                <select name="categorie_id" class="border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Toutes catégories</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(request('categorie_id') == $c->id)>{{ $c->nom }}</option>
                    @endforeach
                </select>

                <select name="type_produit" class="border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Tous types</option>
                    <option value="brut" @selected(request('type_produit') === 'brut')>Brut</option>
                    <option value="transforme" @selected(request('type_produit') === 'transforme')>Transformé</option>
                    <option value="intrant" @selected(request('type_produit') === 'intrant')>Intrant</option>
                    <option value="materiel" @selected(request('type_produit') === 'materiel')>Matériel</option>
                </select>

                <select name="actif" class="border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Tous statuts</option>
                    <option value="1" @selected(request('actif') === '1')>Actifs</option>
                    <option value="0" @selected(request('actif') === '0')>Inactifs</option>
                </select>

                <button type="submit" class="bg-stone-900 hover:bg-stone-800 text-white text-sm font-bold px-4 py-2 rounded-xl transition flex items-center justify-center gap-2">
                    <x-icon name="search" size="4" />
                    Filtrer
                </button>
            </div>
        </form>

        {{-- Table --}}
        @if($produits->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-700 mb-1">Aucun produit</p>
                <p class="text-sm text-stone-500">Ajustez les filtres ou créez un nouveau produit.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-stone-50 border-b border-stone-100">
                            <tr>
                                <th class="text-left px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Produit</th>
                                <th class="text-left px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Catégorie</th>
                                <th class="text-left px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Type</th>
                                <th class="text-center px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Usage</th>
                                <th class="text-left px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Créateur</th>
                                <th class="text-left px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Date</th>
                                <th class="text-center px-4 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Statut</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach($produits as $p)
                                @php
                                    $badgeCategorie = match($p->categorie->code ?? '') {
                                        'brut'       => 'bg-lime-100 text-lime-800',
                                        'transforme' => 'bg-amber-100 text-amber-800',
                                        'intrant'    => 'bg-blue-100 text-blue-800',
                                        'materiel'   => 'bg-stone-100 text-stone-700',
                                        default      => 'bg-stone-100 text-stone-700',
                                    };
                                    $badgeType = match($p->type_produit) {
                                        'brut'       => 'bg-green-100 text-green-800',
                                        'transforme' => 'bg-purple-100 text-purple-800',
                                        'intrant'    => 'bg-cyan-100 text-cyan-800',
                                        'materiel'   => 'bg-orange-100 text-orange-800',
                                        default      => 'bg-stone-100 text-stone-700',
                                    };
                                @endphp
                                <tr class="hover:bg-stone-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-100 to-lime-100 text-green-700 flex items-center justify-center flex-shrink-0">
                                                <x-icon name="leaf" size="4" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-stone-900">{{ $p->nom }}</p>
                                                @if($p->description)
                                                    <p class="text-xs text-stone-500 truncate max-w-xs">{{ Str::limit($p->description, 40) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($p->categorie)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeCategorie }}">
                                                {{ $p->categorie->nom }}
                                            </span>
                                        @else
                                            <span class="text-xs text-stone-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $badgeType }}">
                                            {{ ucfirst($p->type_produit) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center gap-3 text-xs">
                                            @if($p->producteurs_count > 0)
                                                <span class="inline-flex items-center gap-1 text-green-700" title="Producteurs">
                                                    <x-icon name="sprout" size="3.5" />
                                                    <span class="font-bold">{{ $p->producteurs_count }}</span>
                                                </span>
                                            @endif
                                            @if($p->unites_count > 0)
                                                <span class="inline-flex items-center gap-1 text-amber-700" title="Unités">
                                                    <x-icon name="factory" size="3.5" />
                                                    <span class="font-bold">{{ $p->unites_count }}</span>
                                                </span>
                                            @endif
                                            @if($p->offres_count > 0)
                                                <span class="inline-flex items-center gap-1 text-blue-700" title="Offres">
                                                    <x-icon name="tag" size="3.5" />
                                                    <span class="font-bold">{{ $p->offres_count }}</span>
                                                </span>
                                            @endif
                                            @if($p->producteurs_count == 0 && $p->unites_count == 0 && $p->offres_count == 0)
                                                <span class="text-stone-300">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($p->createur)
                                            <div class="flex items-center gap-2 text-xs">
                                                <div class="w-6 h-6 rounded-full bg-stone-200 text-stone-700 flex items-center justify-center font-bold text-[10px] flex-shrink-0">
                                                    {{ strtoupper(substr($p->createur->nom, 0, 1)) }}
                                                </div>
                                                <span class="text-stone-600">{{ Str::limit($p->createur->nom, 15) }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-stone-400 italic">Système / Seed</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-stone-500">
                                        {{ $p->created_at->format('d/m/Y') }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($p->actif)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                                                Actif
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-stone-100 text-stone-600">
                                                <span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span>
                                                Inactif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <form method="POST" action="{{ route('admin.produits.toggle', $p) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="p-1.5 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition"
                                                        title="{{ $p->actif ? 'Désactiver' : 'Activer' }}">
                                                    <x-icon :name="$p->actif ? 'eye-off' : 'eye'" size="4" />
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{ $produits->links() }}</div>
        @endif

        {{-- Modal nouveau produit --}}
        <dialog id="modal-nouveau" class="rounded-3xl shadow-2xl p-0 backdrop:bg-black/50 max-w-lg w-full">
            <form method="POST" action="{{ route('admin.produits.store') }}" class="bg-white rounded-3xl p-6">
                @csrf
                <h3 class="text-xl font-bold text-stone-900 mb-5">Nouveau produit</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Nom <span class="text-red-500">*</span></label>
                        <input type="text" name="nom" required maxlength="100"
                               class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Catégorie <span class="text-red-500">*</span></label>
                            <select name="categorie_id" required class="w-full border-2 border-stone-200 rounded-xl px-3 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-stone-700 mb-2">Type <span class="text-red-500">*</span></label>
                            <select name="type_produit" required class="w-full border-2 border-stone-200 rounded-xl px-3 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                                <option value="brut">Brut</option>
                                <option value="transforme">Transformé</option>
                                <option value="intrant">Intrant</option>
                                <option value="materiel">Matériel</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Description</label>
                        <textarea name="description" rows="3" maxlength="1000"
                                  class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-stone-100">
                    <button type="button" onclick="document.getElementById('modal-nouveau').close()"
                            class="px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-xl transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-green-700 hover:bg-green-800 text-white text-sm font-bold rounded-xl shadow-sm transition flex items-center gap-2">
                        <x-icon name="check" size="4" />
                        Créer
                    </button>
                </div>
            </form>
        </dialog>
    </div>
</x-app-layout>