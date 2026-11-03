<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\PropositionProduit;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PropositionProduitController extends Controller
{
    /**
     * Créer une proposition (appelée depuis le formulaire d'offre ou de stock).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_saisi' => 'required|string|max:100',
        ], [
            'nom_saisi.required' => 'Indiquez le nom du produit.',
        ]);

        $user = Auth::user();

        // Éviter les doublons non traités par le même utilisateur
        $existe = PropositionProduit::where('propose_par', $user->id)
            ->where('statut', 'a_verifier')
            ->whereRaw('LOWER(nom_saisi) = ?', [strtolower($data['nom_saisi'])])
            ->exists();

        if ($existe) {
            return back()->with('error', 'Vous avez déjà proposé ce produit.');
        }

        $proposition = PropositionProduit::create([
            'nom_saisi'  => $data['nom_saisi'],
            'propose_par' => $user->id,
            'statut'     => 'a_verifier',
        ]);

        // Notifier tous les admins
        $admins = Utilisateur::where('type_compte', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'utilisateur_id' => $admin->id,
                'code'           => 'N-01',
                'titre'          => 'Nouveau produit proposé',
                'canal'          => 'web',
                'contenu'        => "{$user->nom} propose le produit « {$data['nom_saisi']} »",
                'url'            => route('admin.propositions'),
                'lu'             => false,
                'created_at'     => now(),
            ]);
        }

        return back()->with('success', 'Proposition envoyée. Un administrateur va la vérifier.');
    }
}