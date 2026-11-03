<x-app-layout title="Chat avec {{ $interlocuteur->nom }}">
    <div class="max-w-3xl mx-auto">

        {{-- ═══════════════ EN-TÊTE CONVERSATION ═══════════════ --}}
        <div class="bg-white rounded-t-3xl shadow-sm border border-stone-100 px-4 py-3 flex items-center gap-3 sticky top-[65px] z-10 backdrop-blur-lg bg-white/95">

            {{-- Bouton retour --}}
            <a href="{{ route('chat.index') }}"
               class="p-2 -ml-1 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition flex-shrink-0">
                <x-icon name="chevron-left" size="5" />
            </a>

            {{-- Avatar + nom --}}
            @php
                $palettes = [
                    'from-green-600 to-emerald-500',
                    'from-blue-600 to-cyan-500',
                    'from-purple-600 to-fuchsia-500',
                    'from-amber-500 to-orange-500',
                    'from-pink-600 to-rose-500',
                    'from-indigo-600 to-blue-500',
                    'from-teal-600 to-green-500',
                    'from-red-600 to-orange-500',
                ];
                $gradient = $palettes[($interlocuteur->id ?? 0) % count($palettes)];
                $initial = strtoupper(mb_substr($interlocuteur->nom, 0, 1));
                $typeLabel = match($interlocuteur->type_compte) {
                    'producteur'          => 'Producteur',
                    'unite_transformation'=> 'Unité de transformation',
                    'population'          => 'Acheteur',
                    'admin'               => 'Administrateur',
                    default               => '',
                };
            @endphp

            <div class="relative flex-shrink-0">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $gradient }} text-white flex items-center justify-center font-black shadow-md">
                    {{ $initial }}
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <p class="font-black text-stone-900 truncate">{{ $interlocuteur->nom }}</p>
                <p class="text-xs text-stone-500 truncate">
                    {{ $typeLabel }}
                    @if($interlocuteur->commune)
                        · {{ $interlocuteur->commune->nom }}
                    @endif
                </p>
            </div>

            {{-- Actions (placeholder pour futur : appel, infos) --}}
            <div class="flex items-center gap-1 flex-shrink-0">
                <a href="{{ route('chat.index') }}"
                   class="p-2 text-stone-500 hover:text-stone-900 hover:bg-stone-100 rounded-xl transition"
                   title="Retour à la liste">
                    <x-icon name="list" size="5" />
                </a>
            </div>
        </div>

        {{-- ═══════════════ FIL DE MESSAGES ═══════════════ --}}
        <div id="messages-container"
             class="bg-gradient-to-b from-stone-50 via-stone-50 to-green-50/30 border-x border-stone-100 px-4 py-6 space-y-1 overflow-y-auto"
             style="height: 60vh;">

            @if($messages->isEmpty())
                <div class="flex flex-col items-center justify-center h-full text-center py-12">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-green-100 to-lime-100 mx-auto mb-4 flex items-center justify-center">
                        <x-icon name="message-circle" size="10" class="text-green-700" />
                    </div>
                    <p class="font-black text-stone-900 text-lg mb-1">Démarrez la conversation</p>
                    <p class="text-sm text-stone-500 max-w-xs">
                        Envoyez votre premier message à {{ $interlocuteur->nom }}
                    </p>
                </div>
            @else
                @php $derniereDate = null; @endphp

                @foreach($messages as $msg)
                    @php
                        $mine = $msg->expediteur_id === Auth::id();
                        $dateMsg = $msg->created_at->toDateString();
                    @endphp

                    {{-- Séparateur de date --}}
                    @if($dateMsg !== $derniereDate)
                        @php
                            $labelDate = $msg->created_at->isToday()
                                ? "Aujourd'hui"
                                : ($msg->created_at->isYesterday()
                                    ? 'Hier'
                                    : $msg->created_at->translatedFormat('l d F Y'));
                            $derniereDate = $dateMsg;
                        @endphp
                        <div class="flex items-center justify-center my-4">
                            <span class="px-3 py-1 bg-white border border-stone-200 rounded-full text-[11px] font-bold text-stone-500 shadow-sm">
                                {{ $labelDate }}
                            </span>
                        </div>
                    @endif

                    {{-- Bulle de message --}}
                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }} mb-2"
                         data-id="{{ $msg->id }}">
                        <div class="max-w-[78%] {{ $mine
                                ? 'bg-gradient-to-br from-green-700 to-green-600 text-white rounded-2xl rounded-br-md shadow-md shadow-green-700/20'
                                : 'bg-white text-stone-900 border border-stone-100 rounded-2xl rounded-bl-md shadow-sm' }}
                                px-4 py-2.5">

                            @unless($mine)
                                <p class="text-xs font-black text-green-700 mb-1">
                                    {{ $msg->expediteur->nom }}
                                </p>
                            @endunless

                            <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">{{ $msg->contenu }}</p>

                            <div class="flex items-center justify-end gap-1 mt-1">
                                <p class="text-[10px] {{ $mine ? 'text-green-200' : 'text-stone-400' }}">
                                    {{ $msg->created_at->format('H:i') }}
                                </p>
                                @if($mine)
                                    <x-icon name="check-check" size="3" class="{{ $msg->lu_le ? 'text-lime-300' : 'text-green-300' }}" />
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Indicateur "en train d'écrire" (caché par défaut) --}}
            <div id="typing-indicator" class="hidden flex justify-start mb-2">
                <div class="bg-white border border-stone-100 rounded-2xl rounded-bl-md shadow-sm px-4 py-3">
                    <div class="flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="w-2 h-2 rounded-full bg-stone-400 animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════ FORMULAIRE ═══════════════ --}}
        <form id="chat-form"
              class="bg-white rounded-b-3xl shadow-sm border border-stone-100 px-3 py-3 flex items-end gap-2">
            @csrf

            <div class="flex-1 relative">
                <textarea id="chat-input"
                          name="contenu"
                          rows="1"
                          required
                          maxlength="2000"
                          placeholder="Écrivez votre message…"
                          class="w-full border-2 border-stone-200 rounded-2xl px-4 py-2.5 pr-12 text-sm focus:ring-4 focus:ring-green-500/20 focus:border-green-600 outline-none resize-none max-h-32 transition"></textarea>
                <span id="char-count"
                      class="absolute bottom-2.5 right-3 text-[10px] font-bold text-stone-400 pointer-events-none"></span>
            </div>

            <button type="submit"
                    id="send-button"
                    class="w-11 h-11 rounded-2xl bg-gradient-to-br from-green-700 to-green-600 hover:from-green-800 hover:to-green-700 text-white flex items-center justify-center flex-shrink-0 shadow-md shadow-green-700/30 transition-all hover:scale-105 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                <x-icon name="send" size="5" />
            </button>
        </form>
    </div>

    <script>
        const conversationId = {{ $conversation->id }};
        const currentUserId  = {{ Auth::id() }};
        const container      = document.getElementById('messages-container');
        const input          = document.getElementById('chat-input');
        const form           = document.getElementById('chat-form');
        const sendBtn        = document.getElementById('send-button');
        const charCount      = document.getElementById('char-count');

        let dernierId   = {{ $messages->last()->id ?? 0 }};
        let isScrolled  = true;

        // ─── Scroll initial en bas ───────────────────────
        container.scrollTop = container.scrollHeight;

        // ─── Détection scroll manuel (pour arrêter l'auto-scroll) ───
        container.addEventListener('scroll', () => {
            const distanceBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
            isScrolled = distanceBottom < 100;
        });

        // ─── Auto-resize + compteur ──────────────────────
        input.addEventListener('input', () => {
            input.style.height = 'auto';
            input.style.height = Math.min(input.scrollHeight, 128) + 'px';

            const len = input.value.length;
            if (len > 0) {
                charCount.textContent = `${len}/2000`;
                charCount.classList.toggle('text-red-500', len > 1900);
                charCount.classList.toggle('text-stone-400', len <= 1900);
            } else {
                charCount.textContent = '';
            }
        });

        // ─── Entrée = envoyer (Shift+Entrée = saut de ligne) ───
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.requestSubmit();
            }
        });

        // ─── Envoi AJAX ──────────────────────────────────
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const contenu = input.value.trim();
            if (!contenu) return;

            input.value = '';
            input.style.height = 'auto';
            charCount.textContent = '';
            sendBtn.disabled = true;

            try {
                const res = await fetch(`/chat/${conversationId}/envoyer`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ contenu }),
                });

                if (res.ok) {
                    const data = await res.json();
                    ajouterMessage(data.message);
                    dernierId = data.message.id;
                    container.scrollTop = container.scrollHeight;
                }
            } catch (err) {
                console.error(err);
                input.value = contenu; // Restaure en cas d'erreur
            } finally {
                sendBtn.disabled = false;
                input.focus();
            }
        });

        // ─── Polling toutes les 4s ───────────────────────
        setInterval(async () => {
            try {
                const res = await fetch(`/chat/${conversationId}/nouveaux?apres=${dernierId}`, {
                    headers: { 'Accept': 'application/json' },
                });
                if (!res.ok) return;
                const data = await res.json();

                if (data.messages.length > 0) {
                    // Affiche l'indicateur "typing" pendant 300ms avant d'ajouter (effet UX)
                    const typing = document.getElementById('typing-indicator');
                    typing.classList.remove('hidden');

                    setTimeout(() => {
                        typing.classList.add('hidden');
                        data.messages.forEach(m => {
                            ajouterMessage(m);
                            dernierId = Math.max(dernierId, m.id);
                        });
                        if (isScrolled) {
                            container.scrollTop = container.scrollHeight;
                        }
                    }, 300);
                }
            } catch (err) { /* ignore */ }
        }, 4000);

        // ─── Ajout d'une bulle ──────────────────────────
        function ajouterMessage(m) {
            const mine = m.expediteur_id === currentUserId;
            const time = new Date(m.created_at).toLocaleTimeString('fr-FR', {
                hour: '2-digit',
                minute: '2-digit',
            });

            const wrapper = document.createElement('div');
            wrapper.className = `flex ${mine ? 'justify-end' : 'justify-start'} mb-2`;
            wrapper.dataset.id = m.id;

            const bulle = document.createElement('div');
            bulle.className = `max-w-[78%] ${mine
                ? 'bg-gradient-to-br from-green-700 to-green-600 text-white rounded-2xl rounded-br-md shadow-md shadow-green-700/20'
                : 'bg-white text-stone-900 border border-stone-100 rounded-2xl rounded-bl-md shadow-sm'} px-4 py-2.5`;

            bulle.innerHTML = `
                ${!mine ? `<p class="text-xs font-black text-green-700 mb-1">${escapeHtml(m.expediteur)}</p>` : ''}
                <p class="text-sm whitespace-pre-wrap break-words leading-relaxed">${escapeHtml(m.contenu)}</p>
                <div class="flex items-center justify-end gap-1 mt-1">
                    <p class="text-[10px] ${mine ? 'text-green-200' : 'text-stone-400'}">${time}</p>
                    ${mine ? `<svg class="w-3 h-3 text-green-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>` : ''}
                </div>
            `;

            wrapper.appendChild(bulle);
            container.appendChild(wrapper);
        }

        // ─── Échappe le HTML ───────────────────────────
        function escapeHtml(s) {
            return s.replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
            }[c]));
        }
    </script>
</x-app-layout>