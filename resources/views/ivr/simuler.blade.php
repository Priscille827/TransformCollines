<x-app-layout title="Assistant vocal IVR">
    <div class="max-w-4xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <x-icon name="phone-call" size="6" class="text-green-700" />
                Assistant vocal IVR
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Simulez un producteur qui appelle un numéro gratuit et déclare par la voix ou les touches
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Téléphone --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4">Téléphone simulé</h2>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Producteur qui appelle</label>
                    <select id="producteur" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        @foreach($producteurs as $p)
                            <option value="{{ $p->telephone }}">
                                {{ $p->nom }} — {{ $p->telephone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Écran --}}
                <div class="bg-gray-900 rounded-2xl p-4 shadow-inner">
                    <div class="bg-gray-50 rounded-xl p-4 min-h-[220px] flex flex-col">
                        <div class="text-xs text-gray-400 mb-2 flex items-center gap-1.5">
                            <x-icon name="phone" size="3" />
                            Appel en cours…
                        </div>

                        <div id="ecran" class="flex-1 flex flex-col items-center justify-center text-center p-4">
                            <div class="text-gray-400">
                                <x-icon name="phone-call" size="10" class="mx-auto mb-2 animate-pulse text-green-600" />
                                <p class="text-sm">Cliquez sur <strong>Appeler</strong> pour démarrer</p>
                            </div>
                        </div>

                        <button id="btn-appeler"
                                class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                            <x-icon name="phone" size="4" />
                            Appeler le service
                        </button>
                    </div>

                    {{-- Clavier numérique --}}
                    <div class="grid grid-cols-3 gap-1.5 mt-3">
                        @foreach(['1','2','3','4','5','6','7','8','9','*','0','#'] as $touche)
                            <button class="touche bg-gray-800 hover:bg-gray-700 text-white py-2.5 rounded text-sm font-medium transition"
                                    data-touche="{{ $touche }}">
                                {{ $touche }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Transcription vocale --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-icon name="volume-2" size="5" />
                    Message vocal
                </h2>

                <div id="transcription" class="bg-gray-50 rounded-xl p-5 min-h-[280px] flex flex-col justify-center">
                    <div class="text-gray-400 text-center">
                        <x-icon name="mic" size="10" class="mx-auto mb-2" />
                        <p class="text-sm">En attente d'un appel…</p>
                    </div>
                </div>

                <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-900">
                    <p class="font-medium mb-1">Principe :</p>
                    <p>Dans une vraie implémentation, ce message serait diffusé vocalement en <strong>fongbé ou yoruba</strong> via un serveur IVR (Africa's Talking, Twilio, etc.).</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let etat = { telephone: null, besoin_id: null, quantite: '' };

        const ecran = document.getElementById('ecran');
        const transcription = document.getElementById('transcription');

        function afficherMessage(texte, options = []) {
            let html = `<p class="text-sm font-medium text-gray-900 mb-3">${texte}</p>`;

            if (options.length > 0) {
                html += '<div class="space-y-2 text-left w-full">';
                options.forEach(o => {
                    html += `
                        <div class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-xs flex items-center gap-2">
                            <span class="w-6 h-6 rounded-full bg-green-600 text-white flex items-center justify-center text-[11px] font-bold">${o.touche}</span>
                            <span class="text-gray-700">${o.label}</span>
                        </div>
                    `;
                });
                html += '</div>';
            }

            transcription.innerHTML = html;
        }

        document.getElementById('btn-appeler').addEventListener('click', async () => {
            etat.telephone = document.getElementById('producteur').value;

            const res = await fetch('{{ route('ivr.menu') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ telephone: etat.telephone }),
            });
            const data = await res.json();

            if (data.etape === 'erreur') {
                afficherMessage(data.message);
                return;
            }

            afficherMessage(data.message, data.options);

            // Stocker les options pour les touches
            etat.options = data.options;
        });

        document.querySelectorAll('.touche').forEach(btn => {
            btn.addEventListener('click', async () => {
                const t = btn.dataset.touche;

                // Étape : choix du besoin
                if (etat.options && !etat.besoin_id) {
                    const option = etat.options.find(o => o.touche == t);
                    if (!option) return;

                    const res = await fetch('{{ route('ivr.choisir') }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ besoin_id: option.id }),
                    });
                    const data = await res.json();
                    afficherMessage(data.message);
                    etat.besoin_id = option.id;
                    etat.quantite = '';
                    return;
                }

                // Étape : saisie quantité
                if (etat.besoin_id) {
                    if (t === '#') {
                        if (!etat.quantite) return;

                        const res = await fetch('{{ route('ivr.enregistrer') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({
                                telephone: etat.telephone,
                                besoin_id: etat.besoin_id,
                                quantite: parseInt(etat.quantite),
                            }),
                        });
                        const data = await res.json();
                        afficherMessage(data.message);

                        // Réinitialiser
                        etat.besoin_id = null;
                        etat.quantite = '';
                        etat.options = null;
                        return;
                    }

                    if (/\d/.test(t)) {
                        etat.quantite += t;
                        afficherMessage(`Quantité saisie : ${etat.quantite} kg — appuyez sur # pour valider`);
                    }
                }
            });
        });
    </script>
</x-app-layout>