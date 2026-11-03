<x-app-layout title="Messages">
    <div class="max-w-3xl mx-auto"
         x-data="{
             open: false,
             q: '',
             resultats: [],
             loading: false,
             async rechercher() {
                 if (this.q.length < 2) { this.resultats = []; return; }
                 this.loading = true;
                 try {
                     const res = await fetch(`{{ route('chat.rechercher') }}?q=${encodeURIComponent(this.q)}`);
                     const data = await res.json();
                     this.resultats = data.utilisateurs || [];
                 } catch (e) { this.resultats = []; }
                 this.loading = false;
             },
             ouvrir() {
                 this.open = true;
                 this.q = '';
                 this.resultats = [];
                 this.$nextTick(() => this.$refs.searchInput?.focus());
             }
         }"
         @keydown.escape.window="open = false">

        {{-- ═══════════════ HEADER ═══════════════ --}}
        <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-1 flex items-center gap-2.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-700 to-green-600 text-white flex items-center justify-center shadow-lg shadow-green-700/30">
                        <x-icon name="message-circle" size="6" />
                    </div>
                    Messages
                </h1>
                <p class="text-stone-500 text-sm">Restez en contact avec vos partenaires</p>
            </div>
            <button type="button"
                    @click="ouvrir()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-700 to-green-600 hover:from-green-800 hover:to-green-700 text-white font-bold rounded-xl shadow-lg shadow-green-700/30 transition-all hover:scale-105 active:scale-95">
                <x-icon name="plus" size="4" />
                Nouvelle conversation
            </button>
        </div>

        {{-- ═══════════════ LISTE DES CONVERSATIONS ═══════════════ --}}
        @if($conversations->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-green-50/50 via-transparent to-lime-50/30 pointer-events-none"></div>
                <div class="relative">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-green-100 to-lime-100 mx-auto mb-4 flex items-center justify-center">
                        <x-icon name="message-circle" size="10" class="text-green-700" />
                    </div>
                    <p class="font-black text-stone-900 text-xl mb-2">Aucune conversation</p>
                    <p class="text-sm text-stone-500 mb-6 max-w-sm mx-auto">
                        Démarrez une discussion avec un producteur, une unité de transformation ou un acheteur.
                    </p>
                    <button type="button"
                            @click="ouvrir()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-700 hover:bg-green-800 text-white font-bold rounded-xl shadow-md transition">
                        <x-icon name="plus" size="4" />
                        Nouvelle conversation
                    </button>
                </div>
            </div>
        @else
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden">

                {{-- En-tête liste --}}
                <div class="px-5 py-3 border-b border-stone-100 bg-gradient-to-r from-stone-50 to-white flex items-center justify-between">
                    <p class="text-xs font-black text-stone-500 uppercase tracking-wider">
                        {{ $conversations->total() }} conversation{{ $conversations->total() > 1 ? 's' : '' }}
                    </p>
                    @php
                        $totalNonLus = $conversations->getCollection()->sum('non_lus');
                    @endphp
                    @if($totalNonLus > 0)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-green-100 text-green-800 text-xs font-bold">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-600 animate-pulse"></span>
                            {{ $totalNonLus }} non lu{{ $totalNonLus > 1 ? 's' : '' }}
                        </span>
                    @endif
                </div>

                {{-- Conversations --}}
                <div class="divide-y divide-stone-100">
                    @foreach($conversations as $conv)
                        @php
                            $inter = $conv->interlocuteur;
                            $nonLus = $conv->non_lus;
                            $dernier = $conv->dernierMessage;

                            // Hash couleur basé sur l'ID utilisateur
                            $palettes = [
                                'from-green-600 to-emerald-500',
                                'from-blue-600 to-cyan-500',
                                'from-purple-600 to-fuchsia-500',
                                'from-amber-500 to-orange-500',
                                'from-pink-600 to-rose-500',
                                'from-indigo-600 to-blue-500',
                                'from-teal-600 to-green-500',
                                'from-red-600 to-orange-500',
                            ];
                            $paletteIndex = ($inter?->id ?? 0) % count($palettes);
                            $gradient = $palettes[$paletteIndex];

                            $initial = strtoupper(mb_substr($inter?->nom ?? '?', 0, 1));
                            $typeLabel = match($inter?->type_compte) {
                                'producteur'          => 'Producteur',
                                'unite_transformation'=> 'Unité',
                                'population'          => 'Acheteur',
                                'admin'               => 'Admin',
                                default               => '',
                            };
                        @endphp

                        <a href="{{ route('chat.show', $conv) }}"
                           class="group relative flex items-center gap-4 px-5 py-4 hover:bg-stone-50/80 transition-colors
                                  {{ $nonLus > 0 ? 'bg-gradient-to-r from-green-50/60 to-transparent' : '' }}">

                            {{-- Indicateur non lu (barre verticale gauche) --}}
                            @if($nonLus > 0)
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-12 rounded-r-full bg-gradient-to-b from-green-600 to-lime-500"></span>
                            @endif

                            {{-- Avatar --}}
                            <div class="relative flex-shrink-0">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $gradient }} text-white flex items-center justify-center font-black text-lg shadow-md group-hover:scale-105 transition-transform">
                                    {{ $initial }}
                                </div>
                                {{-- Badge type --}}
                                @if($typeLabel)
                                    <span class="absolute -bottom-1 -right-1 text-[9px] font-black uppercase tracking-wider bg-white border-2 border-stone-100 rounded-md px-1.5 py-0.5 text-stone-600 shadow-sm">
                                        {{ $typeLabel }}
                                    </span>
                                @endif
                            </div>

                            {{-- Contenu --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <p class="font-bold text-stone-900 truncate {{ $nonLus > 0 ? 'text-[15px]' : 'text-sm' }}">
                                        {{ $inter?->nom ?? '—' }}
                                    </p>
                                    <span class="text-[11px] whitespace-nowrap flex-shrink-0 {{ $nonLus > 0 ? 'text-green-700 font-bold' : 'text-stone-400' }}">
                                        {{ $conv->dernier_message_le?->diffForHumans(short: true) ?? '' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between gap-2">
                                    <p class="text-sm truncate {{ $nonLus > 0 ? 'text-stone-900 font-semibold' : 'text-stone-500' }}">
                                        @if($dernier)
                                            @if($dernier->expediteur_id === auth()->id())
                                                <span class="text-stone-400">Vous :</span>
                                            @endif
                                            {{ $dernier->contenu }}
                                        @else
                                            <span class="italic text-stone-400">Démarrez la conversation…</span>
                                        @endif
                                    </p>
                                    @if($nonLus > 0)
                                        <span class="inline-flex items-center justify-center min-w-[24px] h-6 px-2 rounded-full bg-gradient-to-br from-green-600 to-green-700 text-white text-xs font-black flex-shrink-0 shadow-md shadow-green-700/30">
                                            {{ $nonLus > 99 ? '99+' : $nonLus }}
                                        </span>
                                    @else
                                        <x-icon name="chevron-right" size="4" class="text-stone-300 group-hover:text-green-700 group-hover:translate-x-0.5 transition-all flex-shrink-0" />
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="mt-6">{{ $conversations->links() }}</div>
        @endif

        {{-- ═══════════════ MODAL : NOUVELLE CONVERSATION ═══════════════ --}}
        <div x-show="open"
             x-cloak
             @click.self="open = false"
             class="fixed inset-0 z-50 bg-stone-900/60 backdrop-blur-sm flex items-center justify-center p-4">

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-6"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">

                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-green-700 to-green-600 text-white flex items-center justify-center shadow-md">
                            <x-icon name="user-plus" size="5" />
                        </div>
                        <div>
                            <h3 class="font-black text-stone-900 text-lg leading-tight">Nouvelle conversation</h3>
                            <p class="text-xs text-stone-500">Choisissez un destinataire</p>
                        </div>
                    </div>
                    <button type="button" @click="open = false"
                            class="p-1.5 text-stone-400 hover:text-stone-900 hover:bg-stone-100 rounded-lg transition">
                        <x-icon name="x" size="5" />
                    </button>
                </div>

                {{-- Recherche --}}
                <div class="relative mb-4">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <x-icon name="search" size="4" class="text-stone-400" />
                    </div>
                    <input type="text"
                           x-model="q"
                           @input.debounce.300ms="rechercher"
                           x-ref="searchInput"
                           placeholder="Nom ou téléphone…"
                           class="w-full border-2 border-stone-200 rounded-xl pl-10 pr-3 py-3 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition">
                </div>

                {{-- Résultats --}}
                <div class="max-h-80 overflow-y-auto -mx-2">
                    {{-- Chargement --}}
                    <template x-if="loading">
                        <div class="flex items-center justify-center gap-2 py-10 text-sm text-stone-500">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            Recherche…
                        </div>
                    </template>

                    {{-- Vide --}}
                    <template x-if="!loading && q.length >= 2 && resultats.length === 0">
                        <div class="text-center py-10">
                            <div class="w-14 h-14 rounded-2xl bg-stone-100 mx-auto mb-3 flex items-center justify-center">
                                <x-icon name="user-x" size="6" class="text-stone-400" />
                            </div>
                            <p class="text-sm text-stone-500 font-medium">Aucun utilisateur trouvé</p>
                            <p class="text-xs text-stone-400 mt-1">Essayez un autre nom ou téléphone</p>
                        </div>
                    </template>

                    {{-- Initial --}}
                    <template x-if="!loading && q.length < 2">
                        <div class="text-center py-10">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-green-100 to-lime-100 mx-auto mb-3 flex items-center justify-center">
                                <x-icon name="search" size="6" class="text-green-700" />
                            </div>
                            <p class="text-sm text-stone-500 font-medium">Tapez au moins 2 caractères</p>
                            <p class="text-xs text-stone-400 mt-1">Nom ou numéro de téléphone</p>
                        </div>
                    </template>

                    {{-- Résultats --}}
                    <template x-for="(u, i) in resultats" :key="u.id">
                        <form :action="'/chat/demarrer/' + u.id" method="POST" class="block">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-3 py-2.5 hover:bg-gradient-to-r hover:from-green-50 hover:to-lime-50 rounded-xl flex items-center gap-3 transition-all group">
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-green-700 to-green-500 text-white flex items-center justify-center font-black text-sm flex-shrink-0 shadow-sm group-hover:scale-105 transition-transform"
                                     x-text="u.nom.charAt(0).toUpperCase()"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-stone-900 text-sm truncate" x-text="u.nom"></p>
                                    <p class="text-xs text-stone-500 truncate"
                                       x-text="u.type_compte.replace(/_/g, ' ') + (u.telephone ? ' · ' + u.telephone : '')"></p>
                                </div>
                                <x-icon name="message-circle" size="4"
                                        class="text-stone-300 group-hover:text-green-700 transition flex-shrink-0" />
                            </button>
                        </form>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>