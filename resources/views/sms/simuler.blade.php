<x-app-layout title="Simulateur SMS">
    <div class="max-w-4xl mx-auto">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Simulateur SMS / USSD</h1>
            <p class="text-gray-500 text-sm mt-1">
                Simulez un producteur qui envoie un SMS depuis un téléphone basique
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Téléphone simulé --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-icon name="smartphone" size="5" />
                    Téléphone simulé
                </h2>

                {{-- Choix producteur --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Producteur (émetteur)</label>
                    <select id="producteur" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none">
                        @foreach($producteurs as $p)
                            <option value="{{ $p->telephone }}">
                                {{ $p->nom }} — {{ $p->telephone }} ({{ $p->commune->nom }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Écran du téléphone --}}
                <div class="bg-gray-900 rounded-2xl p-4 shadow-inner">
                    <div class="bg-gray-50 rounded-xl p-4 min-h-[180px] flex flex-col">
                        <div class="text-xs text-gray-400 mb-2 flex items-center gap-1.5">
                            <x-icon name="message-square" size="3" />
                            Message à envoyer
                        </div>
                        <textarea id="message" rows="3"
                                  placeholder="MANIOC 500 SAVALOU"
                                  class="flex-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none resize-none"></textarea>
                        <button id="btn-envoyer"
                                class="mt-3 w-full bg-green-600 hover:bg-green-700 text-white text-sm font-medium py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                            <x-icon name="send" size="4" />
                            Envoyer le SMS
                        </button>
                    </div>
                </div>

                {{-- Aide format --}}
                <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg p-3 text-xs text-blue-900">
                    <p class="font-semibold mb-1">Format attendu :</p>
                    <p class="font-mono">PRODUIT QUANTITÉ COMMUNE</p>
                    <p class="mt-2 text-blue-800">Exemples valides :</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-800 font-mono text-[11px]">
                        <li>MANIOC 500 SAVALOU</li>
                        <li>SOJA 1200 SAVE</li>
                        <li>ANACARDE 300 GLAZOUE</li>
                    </ul>
                </div>
            </div>

            {{-- Réponse serveur --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <x-icon name="server" size="5" />
                    Réponse du serveur
                </h2>

                <div id="reponse-container" class="bg-gray-50 rounded-xl p-4 min-h-[200px] flex flex-col justify-center text-center">
                    <div class="text-gray-400">
                        <x-icon name="inbox" size="10" class="mx-auto mb-2" />
                        <p class="text-sm">En attente d'un SMS…</p>
                    </div>
                </div>

                {{-- Raccourcis --}}
                <div class="mt-4">
                    <p class="text-xs text-gray-500 mb-2">Raccourcis rapides :</p>
                    <div class="flex flex-wrap gap-2">
                        <button class="raccourci text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full font-mono" data-msg="MANIOC 500 SAVALOU">MANIOC 500 SAVALOU</button>
                        <button class="raccourci text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full font-mono" data-msg="SOJA 1200 SAVE">SOJA 1200 SAVE</button>
                        <button class="raccourci text-xs bg-gray-100 hover:bg-gray-200 px-3 py-1.5 rounded-full font-mono" data-msg="FORMAT INVALIDE">Test erreur</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Lien vers le journal --}}
        <div class="mt-6 text-center">
            <a href="{{ route('sms.journal') }}"
               class="inline-flex items-center gap-2 text-sm text-green-700 font-medium hover:underline">
                <x-icon name="history" size="4" />
                Voir le journal des SMS reçus
            </a>
        </div>
    </div>

    <script>
        document.getElementById('btn-envoyer').addEventListener('click', async () => {
            const telephone = document.getElementById('producteur').value;
            const message   = document.getElementById('message').value.trim();

            if (!message) {
                alert('Tapez un message');
                return;
            }

            const container = document.getElementById('reponse-container');
            container.innerHTML = '<p class="text-gray-400 text-sm">Envoi en cours…</p>';

            try {
                const res = await fetch('{{ route('sms.recevoir') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ telephone, message }),
                });

                const data = await res.json();

                const bg   = data.success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
                const icon = data.success ? 'check-circle-2' : 'alert-circle';
                const text = data.success ? 'text-green-800' : 'text-red-800';

                container.innerHTML = `
                    <div class="border ${bg} rounded-xl p-4 text-left">
                        <div class="flex items-start gap-3">
                            <div class="${data.success ? 'text-green-600' : 'text-red-600'}">
                                <i data-lucide="${icon}" class="w-6 h-6"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium ${text} mb-1">
                                    ${data.success ? 'SMS traité avec succès' : 'Erreur de format'}
                                </p>
                                <pre class="text-xs whitespace-pre-wrap font-mono ${text} leading-relaxed">${data.reponse}</pre>
                            </div>
                        </div>
                    </div>
                `;

                // Recharger les icônes Lucide
                if (window.lucide) lucide.createIcons();
            } catch (e) {
                container.innerHTML = `<p class="text-red-600 text-sm">Erreur réseau : ${e.message}</p>`;
            }
        });

        // Raccourcis
        document.querySelectorAll('.raccourci').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('message').value = btn.dataset.msg;
            });
        });
    </script>
</x-app-layout>