<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Besoin;
use App\Models\Commande;
use App\Models\Disponibilite;
use App\Models\Offre;
use App\Models\PropositionProduit;
use App\Models\Utilisateur;

class AdminController extends Controller
{
    public function dashboard()
    {
        // Compteurs principaux
        $stats = [
            'comptes_en_attente' => Utilisateur::where('statut_validation', 'en_attente')->count(),
            'comptes_total'      => Utilisateur::count(),
            'producteurs'        => Utilisateur::where('type_compte', 'producteur')->count(),
            'unites'             => Utilisateur::where('type_compte', 'unite_transformation')->count(),
            'population'         => Utilisateur::where('type_compte', 'population')->count(),

            'besoins_actifs'     => Besoin::whereIn('statut', ['actif', 'partiellement_couvert'])->count(),
            'offres_actives'     => Offre::where('statut', 'active')->count(),
            'commandes_total'    => Commande::count(),
            'commandes_attente'  => Commande::where('statut', 'en_attente')->count(),
            'propositions'       => PropositionProduit::where('statut', 'a_verifier')->count(),
        ];

        // Derniers comptes en attente
        $derniersComptes = Utilisateur::where('statut_validation', 'en_attente')
            ->with('commune')
            ->latest()
            ->take(5)
            ->get();

        // Dernières propositions
        $dernieresPropositions = PropositionProduit::with(['proposeur', 'produit'])
            ->where('statut', 'a_verifier')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'derniersComptes', 'dernieresPropositions'));
    }
}