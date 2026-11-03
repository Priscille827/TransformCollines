<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCompteController extends Controller
{
    /**
     * Liste des comptes avec filtres.
     */
    public function index(Request $request)
    {
        $query = Utilisateur::with('commune');

        // Filtre par statut de validation
        if ($request->filled('statut_validation')) {
            $query->where('statut_validation', $request->statut_validation);
        }

        // Filtre par type de compte
        if ($request->filled('type_compte')) {
            $query->where('type_compte', $request->type_compte);
        }

        // Recherche
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('nom', 'LIKE', "%{$q}%")
                    ->orWhere('telephone', 'LIKE', "%{$q}%")
                    ->orWhere('whatsapp', 'LIKE', "%{$q}%");
            });
        }

        $comptes = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'en_attente' => Utilisateur::where('statut_validation', 'en_attente')->count(),
            'valide'     => Utilisateur::where('statut_validation', 'valide')->count(),
            'refuse'     => Utilisateur::where('statut_validation', 'refuse')->count(),
        ];

        return view('admin.comptes.index', compact('comptes', 'stats'));
    }

    /**
     * Détail d'un compte.
     */
    public function show(Utilisateur $compte)
    {
        $compte->load(['commune', 'producteur', 'unite', 'validePar']);

        return view('admin.comptes.show', compact('compte'));
    }

    /**
     * Valider un compte.
     */
    public function valider(Utilisateur $compte)
    {
        if ($compte->statut_validation === 'valide') {
            return back()->with('error', 'Ce compte est déjà validé.');
        }

        $compte->update([
            'statut_validation' => 'valide',
            'valide_par'        => Auth::id(),
            'valide_le'         => now(),
            'motif_refus'       => null,
        ]);

        // Notifier l'utilisateur
        Notification::create([
            'utilisateur_id' => $compte->id,
            'code'           => 'N-04',
            'titre'          => 'Compte validé',
            'canal'          => 'web',
            'contenu'        => 'Votre compte a été validé. Vous pouvez maintenant utiliser toutes les fonctionnalités.',
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return back()->with('success', "Compte de {$compte->nom} validé.");
    }

    /**
     * Refuser un compte.
     */
    public function refuser(Request $request, Utilisateur $compte)
    {
        $data = $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $compte->update([
            'statut_validation' => 'refuse',
            'motif_refus'       => $data['motif_refus'],
            'valide_par'        => Auth::id(),
            'valide_le'         => now(),
        ]);

        // Notifier l'utilisateur
        Notification::create([
            'utilisateur_id' => $compte->id,
            'code'           => 'N-04',
            'titre'          => 'Compte refusé',
            'canal'          => 'web',
            'contenu'        => 'Votre compte a été refusé. Motif : ' . $data['motif_refus'],
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return back()->with('success', "Compte de {$compte->nom} refusé.");
    }

    /**
     * Suspendre un compte.
     */
    public function suspendre(Utilisateur $compte)
    {
        if ($compte->isAdmin()) {
            return back()->with('error', 'Impossible de suspendre un administrateur.');
        }

        $compte->update(['statut_compte' => 'suspendu']);

        return back()->with('success', "Compte de {$compte->nom} suspendu.");
    }

    /**
     * Réactiver un compte.
     */
    public function reactiver(Utilisateur $compte)
    {
        $compte->update(['statut_compte' => 'actif']);

        return back()->with('success', "Compte de {$compte->nom} réactivé.");
    }
}