<x-app-layout title="Mon dossier">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-3xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center shadow-xl shadow-amber-500/20">
                <x-icon name="file-badge" size="8" />
            </div>
            <h1 class="text-3xl font-black text-stone-900 mb-2">Mon dossier d'identification</h1>
            <p class="text-stone-500">
                @if($typeDocument === 'cip')
                    Fournissez une photo de votre <strong>CIP</strong> (Carte d'Identité Personnelle) ou de votre <strong>passeport</strong>.
                @else
                    Fournissez une photo de votre <strong>IFU</strong> (Identifiant Fiscal Unique).
                @endif
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-2xl text-sm">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Statut actuel --}}
        @if($user->aUnDocument())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-6 mb-6">
                <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
                    <div>
                        <p class="text-xs text-stone-500 font-black uppercase tracking-wider mb-1">Document fourni</p>
                        <p class="font-bold text-stone-900">
                            @if($user->document_type === 'cip')
                                CIP / Carte d'identité
                            @elseif($user->document_type === 'passeport')
                                Passeport
                            @elseif($user->document_type === 'ifu')
                                IFU (Identifiant Fiscal Unique)
                            @else
                                Document
                            @endif
                        </p>
                    </div>

                    @if($user->document_verifie)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                            <x-icon name="check-circle-2" size="3.5" />
                            Vérifié
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                            <x-icon name="clock" size="3.5" />
                            En attente de vérification
                        </span>
                    @endif
                </div>

                @if($user->document_chemin)
                    @php
                        $extension = pathinfo($user->document_chemin, PATHINFO_EXTENSION);
                        $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png']);
                    @endphp

                    <div class="bg-stone-50 rounded-2xl p-4 border-2 border-stone-100">
                        @if($isImage)
                            <img src="{{ Storage::url($user->document_chemin) }}"
                                 alt="Document"
                                 class="w-full max-h-96 object-contain rounded-xl">
                        @else
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                                    <x-icon name="file-text" size="6" />
                                </div>
                                <div>
                                    <p class="font-bold text-stone-900">Document PDF</p>
                                    <a href="{{ Storage::url($user->document_chemin) }}" target="_blank"
                                       class="text-xs text-green-700 font-bold hover:underline">
                                        Ouvrir dans un nouvel onglet
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Motif de refus --}}
                @if($user->motif_refus && !$user->document_verifie)
                    <div class="mt-4 bg-red-50 border-l-4 border-red-500 rounded-r-2xl p-4">
                        <p class="text-xs font-black text-red-700 uppercase tracking-wider mb-1">Motif du refus</p>
                        <p class="text-sm text-red-800">{{ $user->motif_refus }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Formulaire --}}
        <form method="POST" action="{{ route('documents.store') }}"
              enctype="multipart/form-data"
              class="bg-white rounded-3xl shadow-lg border border-stone-100 p-6 sm:p-8 space-y-6"
              x-data="{ fichier: null, typeDoc: '{{ $user->document_type ?? $typeDocument }}' }">

            @csrf

            {{-- Type de document --}}
            @if($user->isProducteur())
                <div>
                    <label class="block text-sm font-bold text-stone-800 mb-3">
                        Type de document <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                               :class="typeDoc === 'cip' ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" name="document_type" value="cip" x-model="typeDoc" class="sr-only">
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition"
                                     :class="typeDoc === 'cip' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                                    <x-icon name="id-card" size="6" />
                                </div>
                                <p class="font-bold text-sm" :class="typeDoc === 'cip' ? 'text-green-800' : 'text-stone-700'">CIP</p>
                                <p class="text-[10px] text-stone-400 mt-1">Carte d'identité</p>
                            </div>
                        </label>

                        <label class="cursor-pointer border-2 rounded-2xl p-4 transition"
                               :class="typeDoc === 'passeport' ? 'border-green-600 bg-green-50 shadow-md shadow-green-600/10' : 'border-stone-200 hover:border-stone-300'">
                            <input type="radio" name="document_type" value="passeport" x-model="typeDoc" class="sr-only">
                            <div class="text-center">
                                <div class="w-12 h-12 mx-auto mb-2 rounded-xl flex items-center justify-center transition"
                                     :class="typeDoc === 'passeport' ? 'bg-green-600 text-white' : 'bg-stone-100 text-stone-500'">
                                    <x-icon name="book" size="6" />
                                </div>
                                <p class="font-bold text-sm" :class="typeDoc === 'passeport' ? 'text-green-800' : 'text-stone-700'">Passeport</p>
                                <p class="text-[10px] text-stone-400 mt-1">Passeport</p>
                            </div>
                        </label>
                    </div>
                </div>
            @else
                <input type="hidden" name="document_type" value="ifu">
            @endif

            {{-- Upload --}}
            <div>
                <label class="block text-sm font-bold text-stone-800 mb-2">
                    Fichier <span class="text-red-500">*</span>
                </label>

                <label class="block cursor-pointer">
                    <input type="file" name="document" accept="image/jpeg,image/png,application/pdf" required
                           class="sr-only"
                           @change="fichier = $event.target.files[0]?.name || null">

                    <div class="border-2 border-dashed border-stone-300 rounded-3xl p-8 text-center hover:border-green-500 hover:bg-green-50/30 transition"
                         :class="fichier ? 'border-green-500 bg-green-50/50' : ''">

                        <div x-show="!fichier">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-stone-100 text-stone-500 flex items-center justify-center">
                                <x-icon name="upload" size="8" />
                            </div>
                            <p class="font-bold text-stone-700 mb-1">Cliquez pour sélectionner un fichier</p>
                            <p class="text-xs text-stone-500 mb-1">JPG, PNG ou PDF · Max 5 Mo</p>
                        </div>

                        <div x-show="fichier" class="flex items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center">
                                <x-icon name="check-circle-2" size="6" />
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-green-800" x-text="fichier"></p>
                                <p class="text-xs text-green-600">Fichier prêt à être envoyé</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Info --}}
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-2xl p-4 flex items-start gap-3">
                <x-icon name="info" size="5" class="text-blue-600 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-900">
                    <p class="font-bold mb-1">Important</p>
                    <p>Votre document sera <strong>examiné par un administrateur</strong>. Assurez-vous qu'il soit lisible et non flou. Vos données sont confidentielles et protégées.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-stone-100">
                <a href="{{ route('dashboard') }}"
                   class="px-5 py-3 text-sm font-bold text-stone-700 hover:bg-stone-100 rounded-2xl transition">
                    Plus tard
                </a>
                <button type="submit" :disabled="!fichier"
                        class="px-6 py-3 bg-green-800 hover:bg-green-900 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-bold rounded-2xl shadow-lg shadow-green-800/20 transition flex items-center gap-2">
                    <x-icon name="upload" size="5" />
                    Envoyer mon document
                </button>
            </div>
        </form>
    </div>
</x-app-layout>