<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Mes produits — Transform'Collines</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .grain {
            background-image: radial-gradient(circle at 1px 1px, rgba(15,81,50,0.04) 1px, transparent 0);
            background-size: 24px 24px;
        }
        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }
        @keyframes pop-in {
            0% { transform: scale(0.8); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
        .pop-in { animation: pop-in 0.2s ease-out; }
    </style>
</head>
<body class="font-sans antialiased bg-[#fef9f3] min-h-screen">

    {{-- Décor --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-gradient-to-br from-green-200/40 to-lime-100/30 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-60 -left-40 w-[600px] h-[600px] bg-gradient-to-tr from-amber-100/40 to-green-100/30 rounded-full blur-3xl"></div>
        <div class="absolute inset-0 grain opacity-40"></div>
    </div>

    @php
        $user = auth()->user();
        $isProducteur = $user->isProducteur();
        $isEnAttente = $user->isEnAttente();
    @endphp

    <div class="relative min-h-screen">

        {{-- ═══ HEADER ═══ --}}
        <header class="bg-white/80 backdrop-blur-lg border-b border-stone-200 sticky top-0 z-40">
            <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between gap-4">

                {{-- Bouton retour dashboard --}}
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-2xl text-sm font-bold transition">
                    <x-icon name="arrow-left" size="4" />
                    <span class="hidden sm:inline">Tableau de bord</span>
                    <span class="sm:hidden">Retour</span>
                </a>

                {{-- Titre --}}
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-green-800 to-green-600 flex items-center justify-center">
                        <x-icon :name="$isProducteur ? 'sprout' : 'factory'" size="5" class="text-white" />
                    </div>
                    <span class="font-black text-green-900 hidden sm:inline">Mes produits</span>
                </div>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="p-2.5 text-stone-500 hover:text-red-600 hover:bg-red-50 rounded-2xl transition" title="Déconnexion">
                        <x-icon name="log-out" size="5" />
                    </button>
                </form>
            </div>
        </header>

        {{-- ═══ CONTENU ═══ --}}
        <div class="max-w-5xl mx-auto px-4 py-8" x-data="produitsSelector()" x-init="init()">

            {{-- Header étape --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-green-100/70 backdrop-blur rounded-full text-xs font-black text-green-800 uppercase tracking-widest mb-5">
                    <span class="w-2 h-2 rounded-full bg-lime-500 pulse-soft"></span>
                    {{ $isProducteur ? 'Producteur' : 'Unité de transformation' }}
                </div>

                <div class="w-20 h-20 mx-auto mb-5 rounded-3xl bg-gradient-to-br {{ $isProducteur ? 'from-green-800 to-green-600' : 'from-amber-700 to-amber-500' }} text-white flex items-center justify-center shadow-xl">
                    <x-icon :name="$isProducteur ? 'sprout' : 'factory'" size="10" />
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-black text-stone-900 mb-3 leading-tight">
                    {{ $isProducteur ? 'Que cultivez-vous ?' : 'Que produisez-vous ?' }}
                </h1>
                <p class="text-lg text-stone-500 max-w-2xl mx-auto leading-relaxed">
                    {{ $isProducteur
                        ? 'Sélectionnez les produits bruts que vous cultivez.'
                        : 'Sélectionnez les produits transformés que vous fabriquez.' }}
                </p>
            </div>

            {{-- Bandeau info --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 mb-6 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <p class="text-sm text-blue-900">
                    Vous pouvez <strong>modifier cette sélection à tout moment</strong> depuis votre espace personnel.
                </p>
            </div>

            {{-- ═══ Barre de recherche sticky avec compteur ═══ --}}
            <div class="bg-white rounded-2xl shadow-lg border-2 border-stone-100 p-4 mb-6 sticky top-24 z-30">
                <div class="flex items-center gap-3 flex-wrap">
                    <div class="flex-1 min-w-[200px] relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <x-icon name="search" size="5" class="text-stone-400" />
                        </div>
                        <input type="text" x-model="recherche"
                               placeholder="Rechercher un produit..."
                               class="w-full pl-11 pr-4 py-3 rounded-xl border-2 border-stone-200 bg-white focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none transition text-sm">
                    </div>

                    {{-- Compteur sélection --}}
                    <div class="flex items-center gap-2 px-4 py-3 bg-gradient-to-br from-green-800 to-green-700 text-white rounded-xl shadow-md">
                        <x-icon name="check-circle-2" size="5" class="text-lime-300" />
                        <span class="font-black text-sm whitespace-nowrap">
                            <span x-text="selection.length"></span> sélectionné<span x-show="selection.length > 1">s</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- ═══ Produits sélectionnés (bandeau récap) ═══ --}}
            <div x-show="selection.length > 0" x-transition x-cloak class="mb-6">
                <div class="bg-gradient-to-br from-green-50 to-lime-50 border-2 border-green-200 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <p class="font-black text-green-800 text-sm flex items-center gap-2">
                            <x-icon name="check-circle-2" size="4" />
                            Produits sélectionnés
                        </p>
                        <button type="button" @click="toutDeselectionner()"
                                class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center gap-1">
                            <x-icon name="x" size="3.5" />
                            Tout retirer
                        </button>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <template x-for="id in selection" :key="'tag-' + id">
                            <button type="button"
                                    @click="toggle(id)"
                                    class="pop-in group inline-flex items-center gap-2 px-3 py-1.5 bg-white border-2 border-green-300 text-green-800 rounded-full text-sm font-bold hover:bg-green-50 hover:border-green-500 transition"
                                    :title="'Retirer ' + getProduitNom(id)">
                                <x-icon name="check" size="3.5" />
                                <span x-text="getProduitNom(id)"></span>
                                <x-icon name="x" size="3" class="opacity-50 group-hover:opacity-100" />
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- ═══ Grille de produits ═══ --}}
            <div class="bg-white rounded-2xl shadow-sm border-2 border-stone-100 p-5 mb-6">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <p class="text-sm text-stone-600">
                        <strong x-text="produitsFiltres.length"></strong> produit<span x-show="produitsFiltres.length > 1">s</span> disponible<span x-show="produitsFiltres.length > 1">s</span>
                    </p>
                    <p class="text-xs text-stone-400">
                        Cliquez pour sélectionner
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <template x-for="produit in produitsFiltres" :key="produit.id">
                        <button type="button"
                                @click="toggle(produit.id)"
                                class="group relative text-left border-2 rounded-2xl p-4 transition-all"
                                :class="selection.includes(produit.id)
                                    ? 'border-green-600 bg-green-50 shadow-lg shadow-green-600/20'
                                    : 'border-stone-200 hover:border-green-400 hover:bg-stone-50 hover:shadow-md'">

                            {{-- Indicateur sélection --}}
                            <div x-show="selection.includes(produit.id)"
                                 x-transition
                                 class="absolute -top-2 -right-2 w-7 h-7 rounded-full bg-green-600 text-white flex items-center justify-center shadow-md pop-in">
                                <x-icon name="check" size="4" />
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition"
                                     :class="selection.includes(produit.id)
                                         ? 'bg-green-600 text-white'
                                         : 'bg-stone-100 text-stone-500 group-hover:bg-green-100 group-hover:text-green-700'">
                                    <x-icon name="leaf" size="5" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-stone-900 text-sm truncate" x-text="produit.nom"></p>
                                    <p class="text-xs text-stone-500" x-text="produit.categorie"></p>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Aucun résultat --}}
                <div x-show="produitsFiltres.length === 0" x-cloak class="py-12 text-center">
                    <x-icon name="search-x" size="12" class="mx-auto mb-3 text-stone-300" />
                    <p class="font-semibold text-stone-700 mb-1">
                        Aucun produit ne correspond à « <span x-text="recherche"></span> »
                    </p>
                    <p class="text-sm text-stone-500 mb-5">Vous pouvez proposer ce produit à un administrateur.</p>
                    <button type="button" @click="ouvrirProposition()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold transition shadow-sm">
                        <x-icon name="plus-circle" size="4" />
                        Proposer « <span x-text="recherche"></span> »
                    </button>
                </div>
            </div>

            {{-- ═══ Proposer un produit ═══ --}}
            <div class="bg-gradient-to-br from-amber-50 to-green-50 rounded-2xl border-2 border-dashed border-amber-200 p-5 mb-6">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-amber-500 text-white flex items-center justify-center flex-shrink-0">
                            <x-icon name="lightbulb" size="5" />
                        </div>
                        <div>
                            <p class="font-bold text-stone-900 text-sm">Votre produit n'est pas dans la liste ?</p>
                            <p class="text-xs text-stone-500">Proposez-le, un administrateur le vérifiera.</p>
                        </div>
                    </div>
                    <button type="button" @click="ouvrirProposition()"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold transition shadow-sm">
                        <x-icon name="plus" size="4" />
                        Proposer un produit
                    </button>
                </div>
            </div>

            {{-- ═══ Propositions en attente ═══ --}}
            @if($propositionsEnAttente->isNotEmpty())
                <div class="bg-white rounded-2xl shadow-sm border-2 border-stone-100 p-5 mb-6">
                    <h3 class="font-black text-stone-900 mb-3 flex items-center gap-2">
                        <x-icon name="clock" size="5" class="text-amber-500" />
                        Vos propositions en attente ({{ $propositionsEnAttente->count() }})
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        @foreach($propositionsEnAttente as $prop)
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-100 text-amber-900 rounded-full text-sm font-bold">
                                <x-icon name="clock" size="3.5" />
                                {{ $prop->nom_saisi }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- ═══ Actions fixes en bas ═══ --}}
            <div class="sticky bottom-4 z-30">
                <div class="bg-white rounded-2xl shadow-xl border-2 border-green-200 p-5">
                    <div class="flex items-center justify-between gap-4 flex-wrap">

                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-green-800 to-green-600 text-white flex items-center justify-center font-black shadow-md">
                                <span x-text="selection.length"></span>
                            </div>
                            <div>
                                <p class="font-black text-stone-900 text-sm">
                                    <span x-text="selection.length"></span> produit<span x-show="selection.length > 1">s</span> sélectionné<span x-show="selection.length > 1">s</span>
                                </p>
                                <p class="text-xs text-stone-500">
                                    <span x-show="selection.length === 0">Aucun produit sélectionné</span>
                                    <span x-show="selection.length > 0">Cliquez sur Enregistrer pour valider</span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <a href="{{ route('dashboard') }}"
                               class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                                Annuler
                            </a>

                            <form method="POST" action="{{ route('produits.enregistrer') }}">
                                @csrf
                                <template x-for="id in selection" :key="'input-' + id">
                                    <input type="hidden" name="produits[]" :value="id">
                                </template>

                                <button type="submit"
                                        class="px-6 py-3 bg-green-800 hover:bg-green-900 text-white text-sm font-black rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                                    <x-icon name="check-circle-2" size="5" />
                                    Enregistrer ma sélection
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal proposition --}}
    <div x-data="propositionModal()" x-cloak>
        <div x-show="open"
             x-transition.opacity
             @click.self="fermer()"
             @keydown.escape.window="fermer()"
             class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">

            <div x-show="open"
                 x-transition.scale
                 class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 relative">

                <button @click="fermer()"
                        class="absolute top-4 right-4 p-2 text-stone-400 hover:text-stone-700 hover:bg-stone-100 rounded-lg transition">
                    <x-icon name="x" size="5" />
                </button>

                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center mb-4">
                    <x-icon name="package-plus" size="7" />
                </div>

                <h3 class="text-xl font-black text-stone-900 mb-2">Proposer un nouveau produit</h3>
                <p class="text-sm text-stone-500 mb-5">
                    Un administrateur vérifiera votre proposition. Vous serez notifié dès qu'elle sera traitée.
                </p>

                <form @submit.prevent="soumettre()" class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-stone-800 mb-2">
                            Nom du produit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" x-model="nom"
                               required minlength="3" maxlength="100"
                               placeholder="Ex. Riz local, Igname..."
                               class="w-full rounded-2xl border-2 border-stone-200 bg-white px-4 py-3 focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition">
                    </div>

                    <div x-show="message"
                         x-transition
                         :class="success ? 'bg-green-50 border-green-500 text-green-800' : 'bg-red-50 border-red-500 text-red-800'"
                         class="border-l-4 rounded-r-xl p-3 text-sm">
                        <p x-text="message"></p>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="fermer()"
                                class="flex-1 px-4 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                            Annuler
                        </button>
                        <button type="submit"
                                :disabled="loading"
                                class="flex-1 px-4 py-3 bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white text-sm font-black rounded-2xl transition shadow-sm flex items-center justify-center gap-2">
                            <span x-show="!loading">Envoyer</span>
                            <span x-show="loading">Envoi...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Données --}}
    <script>
        window.__produitsData = {
            selection: @json($dejaSelectionnes),
            produits: @json($produitsJson),
            csrfToken: '{{ csrf_token() }}',
            routeProposer: '{{ route('produits.proposer') }}',
        };
    </script>

    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('produitsSelector', () => ({
                recherche: '',
                selection: window.__produitsData.selection,
                produits: window.__produitsData.produits,

                init() {
                    if (window.lucide) lucide.createIcons();
                },

                get produitsFiltres() {
                    if (!this.recherche.trim()) return this.produits;
                    const q = this.recherche.toLowerCase().trim();
                    return this.produits.filter(p => p.nom.toLowerCase().includes(q));
                },

                getProduitNom(id) {
                    const p = this.produits.find(x => x.id == id);
                    return p ? p.nom : '—';
                },

                toggle(id) {
                    const idx = this.selection.indexOf(id);
                    if (idx > -1) {
                        this.selection.splice(idx, 1);
                    } else {
                        this.selection.push(id);
                    }
                },

                toutDeselectionner() {
                    if (confirm('Désélectionner tous les produits ?')) {
                        this.selection = [];
                    }
                },

                ouvrirProposition() {
                    window.dispatchEvent(new CustomEvent('ouvrir-proposition', {
                        detail: { nom: this.recherche }
                    }));
                }
            }));

            Alpine.data('propositionModal', () => ({
                open: false,
                nom: '',
                message: '',
                success: false,
                loading: false,

                init() {
                    window.addEventListener('ouvrir-proposition', (e) => {
                        this.nom = e.detail?.nom || '';
                        this.open = true;
                        this.message = '';
                    });
                },

                fermer() {
                    this.open = false;
                    this.nom = '';
                    this.message = '';
                    this.success = false;
                    this.loading = false;
                },

                async soumettre() {
                    if (!this.nom.trim() || this.nom.length < 3) {
                        this.message = 'Le nom doit contenir au moins 3 caractères.';
                        this.success = false;
                        return;
                    }

                    this.loading = true;
                    this.message = '';

                    try {
                        const res = await fetch(window.__produitsData.routeProposer, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.__produitsData.csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ nom_saisi: this.nom }),
                        });

                        const data = await res.json();
                        this.success = data.success;
                        this.message = data.message;

                        if (data.success) {
                            setTimeout(() => {
                                this.fermer();
                                window.location.reload();
                            }, 1500);
                        }
                    } catch (e) {
                        this.success = false;
                        this.message = 'Erreur réseau. Réessayez.';
                    } finally {
                        this.loading = false;
                    }
                }
            }));
        });
    </script>
</body>
</html>