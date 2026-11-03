<x-app-layout title="Unités de mesure">
    <div class="max-w-7xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Unités de mesure</h1>
                <p class="text-stone-500">Gérez les mesures et les contenants disponibles</p>
            </div>
            <button onclick="document.getElementById('modal-nouvelle-unite').showModal()"
                    class="inline-flex items-center gap-2 px-5 py-3 bg-green-800 hover:bg-green-900 text-white rounded-2xl text-sm font-bold shadow-lg shadow-green-800/20 transition">
                <x-icon name="plus" size="4" />
                Nouvelle unité
            </button>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-stone-500 font-bold uppercase tracking-wider">Total</span>
                    <x-icon name="package" size="5" class="text-stone-400" />
                </div>
                <p class="text-3xl font-black text-stone-900">{{ $stats['total'] }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-green-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-green-700 font-bold uppercase tracking-wider">Actives</span>
                    <x-icon name="check-circle-2" size="5" class="text-green-600" />
                </div>
                <p class="text-3xl font-black text-green-700">{{ $stats['actives'] }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-stone-500 font-bold uppercase tracking-wider">Inactives</span>
                    <x-icon name="eye-off" size="5" class="text-stone-400" />
                </div>
                <p class="text-3xl font-black text-stone-500">{{ $stats['inactives'] }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 p-5">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-amber-700 font-bold uppercase tracking-wider">Référence</span>
                    <x-icon name="star" size="5" class="text-amber-600" />
                </div>
                <p class="text-3xl font-black text-amber-700">{{ $stats['reference'] }}</p>
            </div>
        </div>

        {{-- Info box --}}
        <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 mb-6 flex items-start gap-3">
            <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
            <div class="text-sm text-blue-900">
                <p class="font-bold mb-1">Différence entre mesure et contenant</p>
                <p><strong>Mesures</strong> (kg, tonne, litre) : unités standardisées.</p>
                <p><strong>Contenants</strong> (sac, panier, bouteille) : poids variable défini par le vendeur.</p>
            </div>
        </div>

        {{-- Onglets --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 mb-6 p-1.5 flex gap-1">
            <a href="{{ route('admin.unites-mesure', ['type' => 'mesure']) }}"
               class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition
                      {{ $type === 'mesure' ? 'bg-green-800 text-white shadow-md' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-icon name="scale" size="4" />
                Unités de mesure
                <span class="px-2 py-0.5 rounded-full text-xs {{ $type === 'mesure' ? 'bg-white/20' : 'bg-stone-100' }}">
                    {{ $stats['mesures_total'] }}
                </span>
            </a>

            <a href="{{ route('admin.unites-mesure', ['type' => 'contenant']) }}"
               class="flex-1 flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-bold text-sm transition
                      {{ $type === 'contenant' ? 'bg-amber-600 text-white shadow-md' : 'text-stone-700 hover:bg-stone-100' }}">
                <x-icon name="package" size="4" />
                Contenants
                <span class="px-2 py-0.5 rounded-full text-xs {{ $type === 'contenant' ? 'bg-white/20' : 'bg-stone-100' }}">
                    {{ $stats['contenants_total'] }}
                </span>
            </a>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="bg-white rounded-2xl shadow-sm border border-stone-100 p-4 mb-6">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher..."
                       class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">

                <select name="actif" class="border-2 border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    <option value="">Tous statuts</option>
                    <option value="1" @selected(request('actif') === '1')>Actives</option>
                    <option value="0" @selected(request('actif') === '0')>Inactives</option>
                </select>

                <button type="submit" class="bg-stone-900 hover:bg-stone-800 text-white text-sm font-bold px-4 py-2.5 rounded-xl transition flex items-center justify-center gap-2">
                    <x-icon name="search" size="4" />
                    Filtrer
                </button>
            </div>
        </form>

        {{-- Tableau --}}
        @if($unites->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-16 text-center">
                <x-icon name="inbox" size="12" class="mx-auto mb-3 text-stone-300" />
                <p class="font-bold text-stone-700 mb-1">Aucune unité</p>
                <p class="text-sm text-stone-500">Créez votre première unité.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-sm border border-stone-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 border-b border-stone-100">
                        <tr>
                            <th class="text-left px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Unité</th>
                            <th class="text-left px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Code</th>
                            <th class="text-left px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Symbole</th>
                            <th class="text-center px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Usage</th>
                            <th class="text-center px-5 py-3 font-bold text-stone-600 text-xs uppercase tracking-wider">Statut</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach($unites as $u)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl {{ $u->type === 'contenant' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }} flex items-center justify-center flex-shrink-0">
                                            @if($u->type === 'contenant')
                                                <x-icon name="package" size="5" />
                                            @else
                                                <x-icon name="scale" size="5" />
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-stone-900">{{ $u->nom }}</p>
                                            @if($u->est_reference)
                                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full mt-0.5">
                                                    <x-icon name="star" size="3" />
                                                    RÉFÉRENCE
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="font-mono text-xs bg-stone-100 px-2 py-1 rounded">{{ $u->code }}</span>
                                </td>
                                <td class="px-5 py-4 text-stone-600 font-mono">{{ $u->symbole ?? '—' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-4 text-xs">
                                        <span class="inline-flex items-center gap-1 {{ $u->stocks_count > 0 ? 'text-stone-700' : 'text-stone-300' }}">
                                            <x-icon name="warehouse" size="3.5" />
                                            {{ $u->stocks_count }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 {{ $u->offres_count > 0 ? 'text-stone-700' : 'text-stone-300' }}">
                                            <x-icon name="tag" size="3.5" />
                                            {{ $u->offres_count }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($u->actif)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-600"></span>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-stone-100 text-stone-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-stone-400"></span>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button type="button" onclick="document.getElementById('modal-modifier-{{ $u->id }}').showModal()"
                                                class="p-2 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition">
                                            <x-icon name="pencil" size="4" />
                                        </button>

                                        @if(!$u->est_reference)
                                            <form method="POST" action="{{ route('admin.unites-mesure.toggle', $u) }}">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                        class="p-2 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition">
                                                    @if($u->actif)
                                                        <x-icon name="eye-off" size="4" />
                                                    @else
                                                        <x-icon name="eye" size="4" />
                                                    @endif
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.unites-mesure.destroy', $u) }}"
                                                  onsubmit="return confirm('Supprimer cette unité ?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition">
                                                    <x-icon name="trash-2" size="4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Modal modification --}}
                            <dialog id="modal-modifier-{{ $u->id }}" class="rounded-3xl shadow-2xl p-0 backdrop:bg-black/50 max-w-md w-full">
                                <form method="POST" action="{{ route('admin.unites-mesure.update', $u) }}" class="bg-white rounded-3xl p-6">
                                    @csrf @method('PATCH')
                                    <h3 class="text-xl font-bold text-stone-900 mb-5">Modifier l'unité</h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-stone-700 mb-2">Code</label>
                                            <input type="text" value="{{ $u->code }}" disabled
                                                   class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 bg-stone-50 text-stone-500 font-mono">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-stone-700 mb-2">Nom</label>
                                            <input type="text" name="nom" value="{{ $u->nom }}" required maxlength="50"
                                                   class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-stone-700 mb-2">Symbole</label>
                                            <input type="text" name="symbole" value="{{ $u->symbole }}" maxlength="10"
                                                   class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-stone-100">
                                        <button type="button" onclick="document.getElementById('modal-modifier-{{ $u->id }}').close()"
                                                class="px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-xl transition">
                                            Annuler
                                        </button>
                                        <button type="submit"
                                                class="px-5 py-2.5 bg-green-800 hover:bg-green-900 text-white text-sm font-bold rounded-xl shadow-sm transition">
                                            Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </dialog>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $unites->links() }}</div>
        @endif

        {{-- Modal nouvelle unité --}}
        <dialog id="modal-nouvelle-unite" class="rounded-3xl shadow-2xl p-0 backdrop:bg-black/50 max-w-md w-full">
            <form method="POST" action="{{ route('admin.unites-mesure.store') }}" class="bg-white rounded-3xl p-6"
                  x-data="{ typeChoisi: '{{ $type }}' }">
                @csrf
                <h3 class="text-xl font-bold text-stone-900 mb-5">Nouvelle unité</h3>

                <div class="space-y-4">
                    {{-- Type --}}
                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Type</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                                   :class="typeChoisi === 'mesure' ? 'border-green-600 bg-green-50' : 'border-stone-200'">
                                <input type="radio" name="type" value="mesure" x-model="typeChoisi" class="sr-only">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                         :class="typeChoisi === 'mesure' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-400'">
                                        <x-icon name="scale" size="5" />
                                    </div>
                                    <span class="text-xs font-bold" :class="typeChoisi === 'mesure' ? 'text-green-800' : 'text-stone-600'">
                                        Mesure
                                    </span>
                                </div>
                            </label>
                            <label class="cursor-pointer border-2 rounded-xl p-3 text-center transition"
                                   :class="typeChoisi === 'contenant' ? 'border-amber-600 bg-amber-50' : 'border-stone-200'">
                                <input type="radio" name="type" value="contenant" x-model="typeChoisi" class="sr-only">
                                <div class="flex flex-col items-center gap-1">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center transition"
                                         :class="typeChoisi === 'contenant' ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-400'">
                                        <x-icon name="package" size="5" />
                                    </div>
                                    <span class="text-xs font-bold" :class="typeChoisi === 'contenant' ? 'text-amber-800' : 'text-stone-600'">
                                        Contenant
                                    </span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Nom</label>
                        <input type="text" name="nom" required maxlength="50"
                               placeholder="Ex. Bouteille, Caisse..."
                               class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Code</label>
                        <input type="text" name="code" required maxlength="20" pattern="[a-z0-9_]+"
                               placeholder="Ex. bouteille, caisse"
                               class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                        <p class="text-xs text-stone-500 mt-1">Lettres minuscules, chiffres et underscores uniquement.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-stone-700 mb-2">Symbole</label>
                        <input type="text" name="symbole" maxlength="10"
                               placeholder="Ex. bout., cais."
                               class="w-full border-2 border-stone-200 rounded-xl px-4 py-2.5 focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition font-mono">
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-stone-100">
                    <button type="button" onclick="document.getElementById('modal-nouvelle-unite').close()"
                            class="px-4 py-2.5 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-xl transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-green-800 hover:bg-green-900 text-white text-sm font-bold rounded-xl shadow-sm transition">
                        Créer
                    </button>
                </div>
            </form>
        </dialog>
    </div>
</x-app-layout>