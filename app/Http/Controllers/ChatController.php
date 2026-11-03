<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Liste des conversations de l'utilisateur connecté.
     */
    public function index()
    {
        $user = Auth::user();

        $conversations = Conversation::with(['user1', 'user2', 'dernierMessage'])
            ->where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->orderByDesc('dernier_message_le')
            ->orderByDesc('created_at')
            ->paginate(20);

        // Enrichir avec l'interlocuteur + nb non lus
        $conversations->getCollection()->transform(function ($conv) use ($user) {
            $conv->interlocuteur = $conv->autreUtilisateur($user->id);
            $conv->non_lus = $conv->nonLusPour($user->id);
            return $conv;
        });

        return view('chat.index', compact('conversations'));
    }

    /**
     * Ouvre (ou crée) une conversation avec un utilisateur précis.
     */
    public function demarrer(Request $request, Utilisateur $utilisateur)
    {
        $me = Auth::user();

        if ($me->id === $utilisateur->id) {
            return back()->with('error', 'Vous ne pouvez pas discuter avec vous-même.');
        }

        $conversation = Conversation::entre($me->id, $utilisateur->id);

        return redirect()->route('chat.show', $conversation);
    }

    /**
     * Affiche une conversation + ses messages.
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->user1_id, $conversation->user2_id])) {
            abort(403);
        }

        // Marquer les messages reçus comme lus
        $conversation->messages()
            ->where('expediteur_id', '!=', $user->id)
            ->whereNull('lu_le')
            ->update(['lu_le' => now()]);

        $messages = $conversation->messages()
            ->with('expediteur')
            ->orderBy('created_at')
            ->get();

        $interlocuteur = $conversation->autreUtilisateur($user->id);

        return view('chat.show', compact('conversation', 'messages', 'interlocuteur'));
    }

    /**
     * Envoie un message.
     */
    public function envoyer(Request $request, Conversation $conversation)
    {
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->user1_id, $conversation->user2_id])) {
            abort(403);
        }

        $data = $request->validate([
            'contenu' => 'required|string|max:2000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'expediteur_id'   => $user->id,
            'contenu'         => $data['contenu'],
        ]);

        $conversation->update(['dernier_message_le' => now()]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => [
                    'id'         => $message->id,
                    'contenu'    => $message->contenu,
                    'expediteur' => $user->nom,
                    'created_at' => $message->created_at->toIso8601String(),
                ],
            ]);
        }

        return back();
    }

    /**
     * Récupère les nouveaux messages (polling).
     */
    public function nouveaux(Conversation $conversation, Request $request)
    {
        $user = Auth::user();

        if (!in_array($user->id, [$conversation->user1_id, $conversation->user2_id])) {
            abort(403);
        }

        $apresId = (int) $request->query('apres', 0);

        $messages = $conversation->messages()
            ->where('id', '>', $apresId)
            ->with('expediteur')
            ->orderBy('created_at')
            ->get()
            ->map(fn($m) => [
                'id'         => $m->id,
                'contenu'    => $m->contenu,
                'expediteur' => $m->expediteur->nom,
                'expediteur_id' => $m->expediteur_id,
                'created_at' => $m->created_at->toIso8601String(),
            ]);

        // Marquer comme lus ceux qui viennent de l'autre
        $conversation->messages()
            ->where('id', '>', $apresId)
            ->where('expediteur_id', '!=', $user->id)
            ->whereNull('lu_le')
            ->update(['lu_le' => now()]);

        return response()->json(['messages' => $messages]);
    }

    /**
     * Recherche d'un utilisateur pour démarrer une conversation.
     */
    public function rechercher(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['utilisateurs' => []]);
        }

        $me = Auth::user();

        $utilisateurs = Utilisateur::where('id', '!=', $me->id)
            ->where('statut_validation', 'valide')
            ->where(function ($query) use ($q) {
                $query->where('nom', 'like', "%{$q}%")
                      ->orWhere('telephone', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'nom', 'type_compte', 'telephone']);

        return response()->json(['utilisateurs' => $utilisateurs]);
    }
}