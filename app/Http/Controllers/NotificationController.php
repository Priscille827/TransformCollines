<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste des notifications.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Notification::where('utilisateur_id', $user->id);

        // Filtres
        if ($request->filled('statut')) {
            if ($request->statut === 'non_lues') {
                $query->where('lu', false);
            } elseif ($request->statut === 'lues') {
                $query->where('lu', true);
            }
        }

        if ($request->filled('code')) {
            $query->where('code', $request->code);
        }

        $notifications = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total'    => Notification::where('utilisateur_id', $user->id)->count(),
            'non_lues' => Notification::where('utilisateur_id', $user->id)->where('lu', false)->count(),
            'lues'     => Notification::where('utilisateur_id', $user->id)->where('lu', true)->count(),
        ];

        return view('notifications.index', compact('notifications', 'stats'));
    }

    /**
     * Marquer une notification comme lue.
     */
    public function marquerLu(Notification $notification)
    {
        if ($notification->utilisateur_id !== Auth::id()) {
            abort(403);
        }

        $notification->update([
            'lu'    => true,
            'lu_le' => now(),
        ]);

        // Si un lien est défini, rediriger vers la ressource
        if ($notification->url) {
            return redirect($notification->url);
        }

        return back();
    }

    /**
     * Marquer toutes les notifications comme lues.
     */
    public function toutMarquerLu()
    {
        Notification::where('utilisateur_id', Auth::id())
            ->where('lu', false)
            ->update([
                'lu'    => true,
                'lu_le' => now(),
            ]);

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Supprimer une notification.
     */
    public function destroy(Notification $notification)
    {
        if ($notification->utilisateur_id !== Auth::id()) {
            abort(403);
        }

        $notification->delete();

        return back()->with('success', 'Notification supprimée.');
    }
}