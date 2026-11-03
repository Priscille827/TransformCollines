<x-app-layout title="Notifications">
    <div class="max-w-3xl mx-auto">

        {{-- Header --}}
        <div class="mb-8 flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h1 class="text-3xl font-black text-stone-900 mb-2">Notifications</h1>
                <p class="text-stone-500">Toutes vos alertes récentes</p>
            </div>

            @if($stats['non_lues'] > 0)
                <form method="POST" action="{{ route('notifications.tout-lu') }}">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-stone-50 text-stone-700 border-2 border-stone-200 rounded-2xl text-sm font-bold transition">
                        <x-icon name="check-check" size="4" />
                        Tout marquer comme lu
                    </button>
                </form>
            @endif
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-600 text-green-800 px-4 py-3 rounded-r-xl flex items-start gap-3">
                <x-icon name="check-circle-2" size="5" class="flex-shrink-0 mt-0.5" />
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <a href="{{ route('notifications.index') }}"
               class="bg-white rounded-2xl shadow-sm border-2 {{ !request('statut') ? 'border-green-600' : 'border-stone-100' }} p-4 hover:shadow-md transition">
                <p class="text-xs text-stone-500 font-black uppercase tracking-wider">Total</p>
                <p class="text-2xl font-black text-stone-900 mt-1">{{ $stats['total'] }}</p>
            </a>

            <a href="{{ route('notifications.index', ['statut' => 'non_lues']) }}"
               class="bg-white rounded-2xl shadow-sm border-2 {{ request('statut') === 'non_lues' ? 'border-amber-500' : 'border-stone-100' }} p-4 hover:shadow-md transition">
                <p class="text-xs text-amber-700 font-black uppercase tracking-wider">Non lues</p>
                <p class="text-2xl font-black text-amber-700 mt-1">{{ $stats['non_lues'] }}</p>
            </a>

            <a href="{{ route('notifications.index', ['statut' => 'lues']) }}"
               class="bg-white rounded-2xl shadow-sm border-2 {{ request('statut') === 'lues' ? 'border-green-600' : 'border-stone-100' }} p-4 hover:shadow-md transition">
                <p class="text-xs text-green-700 font-black uppercase tracking-wider">Lues</p>
                <p class="text-2xl font-black text-green-700 mt-1">{{ $stats['lues'] }}</p>
            </a>
        </div>

        {{-- Filtres --}}
        <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-3 mb-6 flex gap-2 overflow-x-auto">
            <a href="{{ route('notifications.index', request()->except('code')) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition
                      {{ !request('code') ? 'bg-green-800 text-white' : 'text-stone-700 hover:bg-stone-100' }}">
                Toutes
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->all(), ['code' => 'N-01'])) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition
                      {{ request('code') === 'N-01' ? 'bg-green-800 text-white' : 'text-stone-700 hover:bg-stone-100' }}">
                Nouveaux besoins
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->all(), ['code' => 'N-02'])) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition
                      {{ request('code') === 'N-02' ? 'bg-green-800 text-white' : 'text-stone-700 hover:bg-stone-100' }}">
                Seuils
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->all(), ['code' => 'N-03'])) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition
                      {{ request('code') === 'N-03' ? 'bg-green-800 text-white' : 'text-stone-700 hover:bg-stone-100' }}">
                Urgences
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->all(), ['code' => 'N-04'])) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition
                      {{ request('code') === 'N-04' ? 'bg-green-800 text-white' : 'text-stone-700 hover:bg-stone-100' }}">
                Validations
            </a>
            <a href="{{ route('notifications.index', array_merge(request()->all(), ['code' => 'N-05'])) }}"
               class="px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap transition
                      {{ request('code') === 'N-05' ? 'bg-green-800 text-white' : 'text-stone-700 hover:bg-stone-100' }}">
                Commandes
            </a>
        </div>

        {{-- Liste --}}
        @if($notifications->isEmpty())
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 p-16 text-center">
                <div class="w-20 h-20 mx-auto mb-4 rounded-3xl bg-stone-100 text-stone-400 flex items-center justify-center">
                    <x-icon name="bell-off" size="10" />
                </div>
                <p class="font-bold text-stone-900 text-lg mb-2">Aucune notification</p>
                <p class="text-sm text-stone-500">
                    @if(request('statut') === 'non_lues')
                        Toutes vos notifications sont lues.
                    @elseif(request('code'))
                        Aucune notification de ce type.
                    @else
                        Vous recevrez des alertes ici dès qu'il y aura du nouveau.
                    @endif
                </p>
            </div>
        @else
            <div class="bg-white rounded-3xl shadow-sm border border-stone-100 overflow-hidden divide-y divide-stone-100">
                @foreach($notifications as $notif)
                    @php
                        $config = match($notif->code) {
                            'N-01' => ['megaphone', 'bg-blue-100 text-blue-700', 'Nouveau'],
                            'N-02' => ['trending-up', 'bg-amber-100 text-amber-700', 'Seuil'],
                            'N-03' => ['alert-triangle', 'bg-red-100 text-red-700', 'Urgent'],
                            'N-04' => ['check-circle-2', 'bg-green-100 text-green-700', 'Validation'],
                            'N-05' => ['shopping-bag', 'bg-purple-100 text-purple-700', 'Commande'],
                            default => ['bell', 'bg-stone-100 text-stone-700', 'Info'],
                        };
                    @endphp

                    <div class="p-5 flex items-start gap-4 transition {{ $notif->lu ? 'bg-white hover:bg-stone-50' : 'bg-amber-50/40 hover:bg-amber-50' }}">
                        {{-- Icône --}}
                        <div class="w-11 h-11 rounded-2xl {{ $config[1] }} flex items-center justify-center flex-shrink-0">
                            <x-icon :name="$config[0]" size="5" />
                        </div>

                        {{-- Contenu --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-3 mb-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    @if(!$notif->lu)
                                        <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
                                    @endif
                                    @if($notif->titre)
                                        <p class="font-bold text-stone-900 text-sm">{{ $notif->titre }}</p>
                                    @else
                                        <p class="font-bold text-stone-900 text-sm">Notification</p>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $config[1] }}">
                                        {{ $config[2] }}
                                    </span>
                                </div>

                                <span class="text-xs text-stone-400 whitespace-nowrap flex-shrink-0">
                                    {{ $notif->created_at ? $notif->created_at->diffForHumans() : '—' }}
                                </span>
                            </div>

                            <p class="text-sm text-stone-600 leading-relaxed">{{ $notif->contenu }}</p>

                            {{-- Actions --}}
                            <div class="mt-3 flex items-center gap-3 flex-wrap">
                                @if($notif->url)
                                    <form method="POST" action="{{ route('notifications.lu', $notif) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-green-700 hover:text-green-800 transition">
                                            <x-icon name="external-link" size="3.5" />
                                            Voir le détail
                                        </button>
                                    </form>
                                @elseif(!$notif->lu)
                                    <form method="POST" action="{{ route('notifications.lu', $notif) }}">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-600 hover:text-stone-800 transition">
                                            <x-icon name="check" size="3.5" />
                                            Marquer comme lu
                                        </button>
                                    </form>
                                @endif

                                <form method="POST" action="{{ route('notifications.destroy', $notif) }}"
                                      onsubmit="return confirm('Supprimer cette notification ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 text-xs font-bold text-red-500 hover:text-red-700 transition">
                                        <x-icon name="trash-2" size="3.5" />
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>