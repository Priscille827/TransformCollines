<x-app-layout title="Gestion des comptes">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Gestion des comptes</h1>
        <p class="text-gray-500 text-sm mt-1">{{ $comptes->total() }} compte(s) au total</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <a href="{{ route('admin.comptes', ['statut_validation' => 'en_attente']) }}"
           class="bg-amber-50 border border-amber-200 rounded-xl p-4 hover:bg-amber-100 transition {{ request('statut_validation') === 'en_attente' ? 'ring-2 ring-amber-500' : '' }}">
            <p class="text-xs text-amber-700 font-medium uppercase">En attente</p>
            <p class="text-2xl font-bold text-amber-900 mt-1">{{ $stats['en_attente'] }}</p>
        </a>
        <a href="{{ route('admin.comptes', ['statut_validation' => 'valide']) }}"
           class="bg-green-50 border border-green-200 rounded-xl p-4 hover:bg-green-100 transition {{ request('statut_validation') === 'valide' ? 'ring-2 ring-green-500' : '' }}">
            <p class="text-xs text-green-700 font-medium uppercase">Validés</p>
            <p class="text-2xl font-bold text-green-900 mt-1">{{ $stats['valide'] }}</p>
        </a>
        <a href="{{ route('admin.comptes', ['statut_validation' => 'refuse']) }}"
           class="bg-red-50 border border-red-200 rounded-xl p-4 hover:bg-red-100 transition {{ request('statut_validation') === 'refuse' ? 'ring-2 ring-red-500' : '' }}">
            <p class="text-xs text-red-700 font-medium uppercase">Refusés</p>
            <p class="text-2xl font-bold text-red-900 mt-1">{{ $stats['refuse'] }}</p>
        </a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Nom, téléphone..."
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">

            <select name="type_compte" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                <option value="">Tous les rôles</option>
                <option value="producteur" @selected(request('type_compte') === 'producteur')>Producteur</option>
                <option value="unite_transformation" @selected(request('type_compte') === 'unite_transformation')>Unité</option>
                <option value="population" @selected(request('type_compte') === 'population')>Population</option>
            </select>

            <select name="statut_validation" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-500 outline-none">
                <option value="">Tous les statuts</option>
                <option value="en_attente" @selected(request('statut_validation') === 'en_attente')>En attente</option>
                <option value="valide" @selected(request('statut_validation') === 'valide')>Validé</option>
                <option value="refuse" @selected(request('statut_validation') === 'refuse')>Refusé</option>
            </select>

            <button type="submit"
                    class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition flex items-center justify-center gap-2">
                <x-icon name="search" size="4" />
                Filtrer
            </button>
        </div>
    </form>

    {{-- Liste --}}
    @if($comptes->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <x-icon name="inbox" size="10" class="mx-auto mb-3 text-gray-300" />
            <p class="text-sm text-gray-500">Aucun compte trouvé</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nom</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Rôle</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Téléphone</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Commune</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Statut</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($comptes as $c)
                        @php
                            $badge = match($c->statut_validation) {
                                'valide'     => ['bg-green-100 text-green-800', 'Validé'],
                                'en_attente' => ['bg-amber-100 text-amber-800', 'En attente'],
                                'refuse'     => ['bg-red-100 text-red-800', 'Refusé'],
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $c->nom }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ ucfirst(str_replace('_', ' ', $c->type_compte)) }}</td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-xs">{{ $c->telephone }}</td>
                            <td class="px-4 py-3 text-gray-600 text-xs">{{ $c->commune->nom ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                    {{ $badge[1] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.comptes.show', $c) }}"
                                   class="text-xs font-medium text-green-700 hover:underline">
                                    Examiner
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $comptes->links() }}</div>
    @endif
</x-app-layout>