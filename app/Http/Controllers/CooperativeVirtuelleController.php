<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Disponibilite;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CooperativeVirtuelleController extends Controller
{
    /**
     * Liste des besoins ouverts à la coopération virtuelle.
     */
   public function index()
{
    $user = Auth::user();

    if (!$user->isProducteur() && $user->type_compte !== 'admin') {
        abort(403, 'Réservé aux producteurs.');
    }

    // ─── MES PARTICIPATIONS ────────────────────────────────
    $mesParticipations = collect();
    if ($user->isProducteur()) {
        $mesParticipations = Disponibilite::with(['besoin.produit', 'besoin.unite.utilisateur.commune'])
            ->where('producteur_id', $user->producteur->id)
            ->whereNotNull('besoin_id')
            ->whereNull('quantite_livree')
            ->whereIn('statut', ['declaree', 'associee'])
            ->get()
            ->groupBy('besoin_id')
            ->map(function ($group) {
                $besoin = $group->first()->besoin;
                // Toutes les contributions sur ce besoin
                $toutes = Disponibilite::where('besoin_id', $besoin->id)
                    ->whereIn('statut', ['declaree', 'associee'])
                    ->whereNull('quantite_livree')
                    ->get();

                return [
                    'besoin'          => $besoin,
                    'ma_contribution' => $group->sum('quantite'),
                    'total_pool'      => $toutes->sum('quantite'),
                    'nb_membres'      => $toutes->count(),
                ];
            });
    }

    // ─── BESOINS OUVERTS À LA COOPÉRATION ──────────────────
    $besoins = Besoin::with(['produit', 'unite.utilisateur.commune', 'communes'])
        ->whereIn('statut', ['actif', 'partiellement_couvert'])
        ->where('taux_couverture', '<', 100)
        ->orderBy('delai')
        ->get();

    // ─── TOUS LES POOLS EN FORMATION ───────────────────────
    $pools = Disponibilite::with(['besoin.produit', 'besoin.unite.utilisateur', 'producteur.utilisateur'])
        ->where('statut', 'declaree')
        ->whereNotNull('besoin_id')
        ->whereNull('quantite_livree')
        ->get()
        ->groupBy('besoin_id')
        ->map(function ($group) {
            return [
                'besoin'         => $group->first()->besoin,
                'membres'        => $group,
                'total_quantite' => $group->sum('quantite'),
            ];
        });

    return view('cooperative.index', compact('mesParticipations', 'besoins', 'pools'));
}
    /**
     * Rejoindre un pool de coopérative virtuelle.
     */
    public function rejoindre(Request $request, Besoin $besoin)
    {
        $user = Auth::user();

        if (!$user->isProducteur()) {
            abort(403);
        }

        $data = $request->validate([
            'quantite' => ['required', 'numeric', 'min:1'],
        ], [
            'quantite.required' => 'Indiquez la quantité que vous apportez.',
            'quantite.min'      => 'La quantité doit être strictement positive.',
        ]);

        // Vérifier que le produit fait partie du profil
        $aLeProduit = $user->producteur->produits()
            ->where('produits.id', $besoin->produit_id)
            ->exists();

        if (!$aLeProduit) {
            return back()->withErrors([
                'quantite' => "Vous ne cultivez pas {$besoin->produit->nom} selon votre profil.",
            ]);
        }

        DB::beginTransaction();
        try {
            $dispo = Disponibilite::create([
                'producteur_id'      => $user->producteur->id,
                'produit_id'         => $besoin->produit_id,
                'besoin_id'          => $besoin->id,
                'quantite'           => $data['quantite'],
                'date_disponibilite' => now()->addDays(3),
                'date_expiration'    => now()->addDays(15),
                'statut'             => 'associee',
            ]);

            DB::commit();

            // Notifier l'unité du renfort
            Notification::create([
                'utilisateur_id' => $besoin->unite->utilisateur_id,
                'code'           => 'N-02',
                'canal'          => 'web',
                'contenu'        => sprintf(
                    '%s a rejoint la coopérative virtuelle pour votre besoin en %s (+%s kg).',
                    $user->nom,
                    $besoin->produit->nom,
                    number_format($data['quantite'], 0, ',', ' ')
                ),
                'lu'             => false,
                'created_at'     => now(),
            ]);

            return redirect()->route('cooperative.index')
                ->with('success', "Vous avez rejoint la coopérative virtuelle pour {$besoin->produit->nom}.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Proposer un pool complet (réponse collective).
     */
    public function proposerPool(Besoin $besoin)
    {
        $user = Auth::user();

        if (!$user->isProducteur()) {
            abort(403);
        }

        // Récupérer toutes les dispos associées à ce besoin
        $dispos = Disponibilite::where('besoin_id', $besoin->id)
            ->whereIn('statut', ['declaree', 'associee'])
            ->get();

        $total = $dispos->sum('quantite');

        if ($total < $besoin->quantite_recherchee * 0.5) {
            return back()->withErrors([
                'erreur' => 'Le pool doit couvrir au moins 50 % du besoin pour être proposé.',
            ]);
        }

        return back()->with('success', "Pool proposé à l'unité : {$dispos->count()} producteurs, " . number_format($total, 0, ',', ' ') . ' kg.');
    }

    public function mesParticipations()
{
    $user = Auth::user();

    if (!$user->isProducteur()) {
        abort(403);
    }

    $participations = Disponibilite::with(['besoin.produit', 'besoin.unite.utilisateur.commune'])
        ->where('producteur_id', $user->producteur->id)
        ->whereNotNull('besoin_id')
        ->whereNull('quantite_livree')
        ->whereIn('statut', ['declaree', 'associee'])
        ->get()
        ->groupBy('besoin_id')
        ->map(function ($group) {
            $besoin = $group->first()->besoin;
            $toutes = Disponibilite::where('besoin_id', $besoin->id)
                ->whereIn('statut', ['declaree', 'associee'])
                ->whereNull('quantite_livree')
                ->get();

            return [
                'besoin'          => $besoin,
                'ma_contribution' => $group->sum('quantite'),
                'total_pool'      => $toutes->sum('quantite'),
                'nb_membres'      => $toutes->count(),
            ];
        });

    return view('cooperative.mes-participations', compact('participations'));
}
}