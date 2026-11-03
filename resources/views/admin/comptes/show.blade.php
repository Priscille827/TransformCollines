<x-app-layout title="Compte {{ $compte->nom }}">
    <div class="max-w-3xl mx-auto">
        <a href="{{ route('admin.comptes') }}" class="text-sm text-gray-500 hover:text-gray-700 flex items-center gap-1.5 mb-4">
            <x-icon name="arrow-left" size="4" />
            Retour aux comptes
        </a>

        {{-- Header --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-600 to-green-700 flex items-center justify-center text-white font-bold text-2xl">
                    {{ strtoupper(substr($compte->nom, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <h1 class="text-xl font-bold text-gray-900">{{ $compte->nom }}</h1>
                    <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $compte->type_compte)) }}</p>
                    @php
                        $badge = match($compte->statut_validation) {
                            'valide'     => ['bg-green-100 text-green-800', 'Validé'],
                            'en_attente' => ['bg-amber-100 text-amber-800', 'En attente'],
                            'refuse'     => ['bg-red-100 text-red-800', 'Refusé'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }} mt-2">
                        {{ $badge[1] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Infos --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-semibold text-gray-900 mb-4">Informations</h2>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500">Téléphone</dt>
                    <dd class="font-mono text-gray-900">{{ $compte->telephone }}</dd>
                </div>
                @if($compte->whatsapp)
                    <div>
                        <dt class="text-gray-500">WhatsApp</dt>
                        <dd class="font-mono text-gray-900">{{ $compte->whatsapp }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500">Commune</dt>
                    <dd class="text-gray-900">{{ $compte->commune->nom ?? '—' }}</dd>
                </div>
                @if($compte->village)
                    <div>
                        <dt class="text-gray-500">Village</dt>
                        <dd class="text-gray-900">{{ $compte->village }}</dd>
                    </div>
                @endif
                @if($compte->npi)
                    <div>
                        <dt class="text-gray-500">NPI</dt>
                        <dd class="font-mono text-gray-900">{{ $compte->npi }}</dd>
                    </div>
                @endif
                @if($compte->rccm)
                    <div>
                        <dt class="text-gray-500">RCCM</dt>
                        <dd class="font-mono text-gray-900">{{ $compte->rccm }}</dd>
                    </div>
                @endif
                @if($compte->ifu)
                    <div>
                        <dt class="text-gray-500">IFU</dt>
                        <dd class="font-mono text-gray-900">{{ $compte->ifu }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-gray-500">Inscrit le</dt>
                    <dd class="text-gray-900">{{ $compte->created_at->format('d/m/Y à H:i') }}</dd>
                </div>
            </dl>
        </div>
{{-- Document d'identification --}}
@if($compte->aUnDocument())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
            <x-icon name="file-badge" size="5" class="text-amber-600" />
            Document d'identification
            @if($compte->document_verifie)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">
                    <x-icon name="check-circle-2" size="3" />
                    Vérifié
                </span>
            @else
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                    <x-icon name="clock" size="3" />
                    À vérifier
                </span>
            @endif
        </h2>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 mb-4">
            @php
                $ext = pathinfo($compte->document_chemin, PATHINFO_EXTENSION);
                $isImage = in_array(strtolower($ext), ['jpg', 'jpeg', 'png']);
            @endphp

            <p class="text-xs text-gray-500 mb-2 font-medium">
                Type : <strong>{{ strtoupper($compte->document_type) }}</strong>
            </p>

            @if($isImage)
                <img src="{{ Storage::url($compte->document_chemin) }}"
                     alt="Document"
                     class="w-full max-h-96 object-contain rounded-lg border border-gray-200">
            @else
                <a href="{{ Storage::url($compte->document_chemin) }}" target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium hover:bg-gray-50">
                    <x-icon name="file-text" size="4" />
                    Ouvrir le PDF
                </a>
            @endif
        </div>

        @if(!$compte->document_verifie)
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('admin.comptes.verifier-document', $compte) }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold transition">
                        <x-icon name="check-circle-2" size="4" />
                        Vérifier le document
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.comptes.rejeter-document', $compte) }}"
                      x-data="{ open: false }">
                    @csrf
                    <button type="button" @click="open = !open"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-sm font-bold transition border border-red-200">
                        <x-icon name="x-circle" size="4" />
                        Rejeter
                    </button>

                    <div x-show="open" x-transition class="mt-3 space-y-2">
                        <textarea name="motif_refus" rows="2" required
                                  placeholder="Motif du rejet (visible par l'utilisateur)"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                        <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2 rounded-lg">
                            Confirmer le rejet
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
@endif
        {{-- Motif refus --}}
        @if($compte->motif_refus)
            <div class="bg-red-50 border border-red-200 rounded-xl p-5 mb-6">
                <h3 class="font-semibold text-red-900 mb-2 flex items-center gap-2">
                    <x-icon name="alert-circle" size="5" />
                    Motif du refus
                </h3>
                <p class="text-sm text-red-800">{{ $compte->motif_refus }}</p>
            </div>
        @endif

        {{-- Actions --}}
        @if($compte->statut_validation === 'en_attente')
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <h2 class="font-semibold text-gray-900">Actions</h2>

                {{-- Valider --}}
                <form method="POST" action="{{ route('admin.comptes.valider', $compte) }}">
                    @csrf
                    <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 rounded-lg transition flex items-center justify-center gap-2">
                        <x-icon name="check-circle-2" size="5" />
                        Valider ce compte
                    </button>
                </form>

                {{-- Refuser --}}
                <form method="POST" action="{{ route('admin.comptes.refuser', $compte) }}"
                      x-data="{ open: false }">
                    @csrf
                    <button type="button" @click="open = !open"
                            class="w-full bg-red-50 hover:bg-red-100 text-red-700 font-medium py-3 rounded-lg transition flex items-center justify-center gap-2 border border-red-200">
                        <x-icon name="x-circle" size="5" />
                        Refuser ce compte
                    </button>

                    <div x-show="open" x-transition class="mt-3 space-y-2">
                        <textarea name="motif_refus" rows="3" required
                                  placeholder="Motif du refus (visible par l'utilisateur)"
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none"></textarea>
                        <button type="submit"
                                class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-2 rounded-lg transition">
                            Confirmer le refus
                        </button>
                    </div>
                </form>
            </div>
        @endif

        @if($compte->statut_validation === 'valide')
            <form method="POST" action="{{ route('admin.comptes.suspendre', $compte) }}"
                  onsubmit="return confirm('Suspendre ce compte ?')">
                @csrf
                <button type="submit"
                        class="w-full bg-amber-50 hover:bg-amber-100 text-amber-700 font-medium py-3 rounded-lg transition border border-amber-200 flex items-center justify-center gap-2">
                    <x-icon name="pause-circle" size="5" />
                    Suspendre le compte
                </button>
            </form>
        @endif
    </div>
</x-app-layout>