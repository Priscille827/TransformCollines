<x-app-layout title="Mes disponibilités">
    @php $user = auth()->user(); @endphp

    <div class="mb-6 flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes disponibilités</h1>
            <p class="text-gray-500 text-sm mt-1">Vos déclarations de produits</p>
        </div>

        @if($user->isProducteur())
            <a href="{{ route('disponibilites.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm shadow-sm transition">
                <x-icon name="plus" size="4" />
                Déclarer une disponibilité
            </a>
        @endif
    </div>

    @if($dispos->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <x-icon name="package" size="12" class="mx-auto mb-3 text-gray-300" />
            <p class="font-medium text-gray-700 mb-1">Aucune disponibilité déclarée</p>
            <p class="text-sm text-gray-500 mb-5">Commencez par déclarer vos produits à vendre.</p>
            <a href="{{ route('disponibilites.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition">
                <x-icon name="plus" size="4" />
                Nouvelle disponibilité
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Produit</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Quantité</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Disponible le</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Statut</th>
                        <th class="text-left px-6 py-3 font-medium text-gray-600">Besoin lié</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dispos as $d)
                        @php
                            $badge = match($d->statut) {
                                'declaree'   => ['bg-blue-100 text-blue-800', 'Libre'],
                                'associee'   => ['bg-amber-100 text-amber-800', 'Associée'],
                                'confirmee'  => ['bg-green-100 text-green-800', 'Confirmée'],
                                'expiree'    => ['bg-gray-100 text-gray-700', 'Expirée'],
                                default      => ['bg-gray-100 text-gray-700', $d->statut],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $d->produit->nom }}</td>
                            <td class="px-6 py-4">{{ number_format($d->quantite, 0, ',', ' ') }} kg</td>
                            <td class="px-6 py-4 text-gray-600">{{ $d->date_disponibilite->format('d/m/Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-xs">
                                @if($d->besoin)
                                    {{ $d->besoin->unite->utilisateur->nom }}
                                    <span class="text-gray-400">·</span>
                                    {{ number_format($d->besoin->quantite_recherchee, 0, ',', ' ') }} kg
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($d->statut !== 'confirmee')
                                    <form method="POST" action="{{ route('disponibilites.destroy', $d) }}"
                                          onsubmit="return confirm('Supprimer cette disponibilité ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:text-red-800 p-1.5 rounded hover:bg-red-50 transition">
                                            <x-icon name="trash-2" size="4" />
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $dispos->links() }}
        </div>
    @endif
</x-app-layout>