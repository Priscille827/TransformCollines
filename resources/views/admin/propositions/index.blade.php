<x-app-layout title="Propositions de produits">
    <div class="max-w-5xl mx-auto">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-black text-stone-900 mb-2">Propositions de produits</h1>
            <p class="text-stone-500">Validez les nouveaux produits proposés par les utilisateurs</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-4">
                <p class="text-xs text-amber-700 font-black uppercase tracking-wider">À vérifier</p>
                <p class="text-2xl font-black text-amber-700 mt-1">{{ $stats['a_verifier'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-4">
                <p class="text-xs text-green-700 font-black uppercase tracking-wider">Validées</p>
                <p class="text-2xl font-black text-green-700 mt-1">{{ $stats['validees'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 p-4">
                <p class="text-xs text-red-700 font-black uppercase tracking-wider">Refusées</p>
                <p class="text-2xl font-black text-red-700 mt-1">{{ $stats['refusees'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4">
                <p class="text-xs text-stone-600 font-black uppercase tracking-wider">Producteurs</p>
                <p class="text-2xl font-black text-stone-700 mt-1">{{ $stats['producteur'] }}</p>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4">
                <p class="text-xs text-stone-600 font-black uppercase tracking-wider">Unités</p>
                <p class="text-2xl font-black text-stone-700 mt-1">{{ $stats['unite'] }}</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <select name="statut" class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Toutes les propositions</option>
                    <option value="a_verifier" @selected(request('statut') === 'a_verifier')>À vérifier</option>
                    <option value="valide" @selected(request('statut') === 'valide')>Validées</option>
                    <option value="refuse" @selected(request('statut') === 'refuse')>Refusées</option>
                </select>

                <select name="role" class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Tous les rôles</option>
                    <option value="producteur" @selected(request('role') === 'producteur')>Producteurs</option>
                    <option value="unite_transformation" @selected(request('role') === 'unite_transformation')>Unités</option>
                </select>

                <button type="submit" class="bg-stone-900 hover:bg-stone-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                    <x-icon name="filter" size="4" />
                    Filtrer
                </button>
            </div>
        </form>

        {{-- Liste --}}
        @if($propositions->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-green-100 text-green-700 flex items-center justify-center">
                    <x-icon name="check-circle-2" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune proposition</p>
                <p class="text-sm text-stone-500">Toutes les propositions ont été traitées.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($propositions as $prop)
                    @php
                        $badgeType = match($prop->type_produit) {
                            'brut'       => ['bg-green-100 text-green-800', 'Brut'],
                            'transforme' => ['bg-amber-100 text-amber-800', 'Transformé'],
                            'intrant'    => ['bg-blue-100 text-blue-800', 'Intrant'],
                            'materiel'   => ['bg-purple-100 text-purple-800', 'Matériel'],
                            default      => ['bg-stone-100 text-stone-700', ucfirst($prop->type_produit)],
                        };

                        $badgeRole = $prop->propose_par_role === 'producteur'
                            ? ['bg-green-50 text-green-700', 'Producteur']
                            : ['bg-amber-50 text-amber-700', 'Unité'];
                    @endphp

                    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                        {{-- Header --}}
                        <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl {{ $badgeType[0] }} flex items-center justify-center flex-shrink-0">
                                    <x-icon name="package-plus" size="6" />
                                </div>
                                <div>
                                    <h3 class="text-lg font-black text-stone-900">« {{ $prop->nom_saisi }} »</h3>
                                    <p class="text-xs text-stone-500 flex items-center gap-2">
                                        <span>Par <strong>{{ $prop->proposeur->nom }}</strong></span>
                                        @if($prop->proposeur->commune)
                                            · {{ $prop->proposeur->commune->nom }}
                                        @endif
                                        · {{ $prop->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $badgeRole[0] }}">
                                    {{ $badgeRole[1] }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $badgeType[0] }}">
                                    {{ $badgeType[1] }}
                                </span>
                            </div>
                        </div>

                        {{-- Formulaire validation --}}
                        @if($prop->statut === 'a_verifier')
                            <form method="POST" action="{{ route('admin.propositions.valider', $prop) }}"
                                  class="bg-gradient-to-br from-green-50 to-lime-50 border-2 border-green-100 rounded-2xl p-4 mb-3 space-y-3">
                                @csrf

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-bold text-stone-700 mb-1.5">
                                            Nom corrigé <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="nom_corrige" value="{{ $prop->nom_saisi }}" required
                                               class="w-full border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-stone-700 mb-1.5">
                                            Catégorie <span class="text-red-500">*</span>
                                        </label>
                                        <select name="categorie_id" required
                                                class="w-full border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                                            @foreach($categories as $c)
                                                <option value="{{ $c->id }}">{{ $c->nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-bold text-stone-700 mb-1.5">
                                            Type <span class="text-red-500">*</span>
                                        </label>
                                        <select name="type_produit" required
                                                class="w-full border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                                            <option value="brut" @selected($prop->type_produit === 'brut')>Brut</option>
                                            <option value="transforme" @selected($prop->type_produit === 'transforme')>Transformé</option>
                                            <option value="intrant" @selected($prop->type_produit === 'intrant')>Intrant</option>
                                            <option value="materiel" @selected($prop->type_produit === 'materiel')>Matériel</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit"
                                        class="w-full bg-green-700 hover:bg-green-800 text-white text-sm font-bold py-3 rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                                    <x-icon name="check-circle-2" size="4" />
                                    Valider et ajouter au catalogue
                                </button>
                            </form>

                            {{-- Refuser --}}
                            <form method="POST" action="{{ route('admin.propositions.refuser', $prop) }}"
                                  x-data="{ open: false }">
                                @csrf
                                <button type="button" @click="open = !open"
                                        class="text-sm text-red-600 hover:text-red-700 font-bold flex items-center gap-1.5">
                                    <x-icon name="x-circle" size="4" />
                                    Refuser cette proposition
                                </button>

                                <div x-show="open" x-transition class="mt-3 space-y-2">
                                    <textarea name="motif_refus" rows="2" required
                                              placeholder="Motif du refus (visible par l'utilisateur)"
                                              class="w-full border-2 border-stone-200 rounded-xl px-3 py-2 text-sm focus:ring-4 focus:ring-red-500/20 focus:border-red-500 outline-none transition"></textarea>
                                    <button type="submit"
                                            class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition">
                                        Confirmer le refus
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $propositions->links() }}</div>
        @endif

        {{-- Historique récent --}}
        @if($traitees->isNotEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden mt-8">
                <div class="px-6 py-4 border-b border-stone-100">
                    <h2 class="font-bold text-stone-900 flex items-center gap-2">
                        <x-icon name="history" size="5" class="text-stone-500" />
                        Traitées récemment
                    </h2>
                </div>
                <div class="divide-y divide-stone-100">
                    @foreach($traitees as $t)
                        <div class="px-6 py-3 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="font-bold text-stone-900 text-sm">{{ $t->nom_saisi }}</p>
                                <p class="text-xs text-stone-500">
                                    Par {{ $t->proposeur->nom }}
                                    · traité par {{ $t->traitePar?->nom ?? '—' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                @if($t->statut === 'valide')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        <x-icon name="check" size="3" />
                                        Validé
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">
                                        <x-icon name="x" size="3" />
                                        Refusé
                                    </span>
                                @endif
                                <span class="text-xs text-stone-400">
                                    {{ $t->traite_le?->format('d/m/Y') ?? '—' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-app-layout>