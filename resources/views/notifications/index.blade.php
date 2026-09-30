<x-app-layout title="Notifications">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Notifications</h1>
        <p class="text-gray-500 text-sm mb-6">Toutes vos alertes récentes</p>

        @if($notifs->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <x-icon name="bell-off" size="12" class="mx-auto mb-3 text-gray-300" />
                <p class="text-sm text-gray-500">Aucune notification</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
                @foreach($notifs as $n)
                    @php
                        $icone = match($n->code) {
                            'N-01' => 'megaphone',
                            'N-02' => 'trending-up',
                            'N-03' => 'alert-triangle',
                            'N-04' => 'check-circle-2',
                            'N-05' => 'star',
                            default => 'bell',
                        };
                        $couleur = match($n->code) {
                            'N-03' => 'bg-red-100 text-red-700',
                            'N-02' => 'bg-amber-100 text-amber-700',
                            'N-04' => 'bg-green-100 text-green-700',
                            default => 'bg-blue-100 text-blue-700',
                        };
                    @endphp
                    <div class="p-4 flex items-start gap-3 {{ $n->lu ? 'opacity-60' : '' }}">
                        <div class="w-9 h-9 rounded-lg {{ $couleur }} flex items-center justify-center flex-shrink-0">
                            <x-icon :name="$icone" size="5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">{{ $n->contenu }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $n->created_at ? $n->created_at->diffForHumans() : 'à l\'instant' }}
                            </p>
                        </div>
                        @if(!$n->lu)
                            <form method="POST" action="{{ route('notifications.lu', $n->id) }}">
                                @csrf
                                <button class="text-xs text-green-700 hover:underline font-medium whitespace-nowrap">
                                    Marquer lu
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-6">{{ $notifs->links() }}</div>
        @endif
    </div>
</x-app-layout>