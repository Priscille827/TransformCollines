<x-app-layout title="Journal SMS">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Journal des SMS</h1>
                <p class="text-gray-500 text-sm mt-1">Historique des messages reçus</p>
            </div>
            <a href="{{ route('sms.simuler') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm shadow-sm transition">
                <x-icon name="plus" size="4" />
                Simuler un SMS
            </a>
        </div>

        @if($sms->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <x-icon name="message-square" size="12" class="mx-auto mb-3 text-gray-300" />
                <p class="text-sm text-gray-500">Aucun SMS reçu pour le moment</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Date</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Téléphone</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Message</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Interprétation</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($sms as $s)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-500 text-xs">
                                    {{ $s->created_at ? $s->created_at->format('d/m H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3 font-mono text-xs">{{ $s->telephone }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-gray-800">{{ $s->message_brut }}</td>
                                <td class="px-4 py-3 text-xs">
                                    @if($s->produit)
                                        <span class="inline-flex items-center gap-1">
                                            <strong>{{ $s->produit->nom }}</strong>
                                            · {{ number_format($s->quantite, 0, ',', ' ') }} kg
                                            · {{ $s->commune->nom ?? '—' }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($s->statut_traitement === 'traite')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Traité
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Erreur format
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $sms->links() }}</div>
        @endif
    </div>
</x-app-layout>