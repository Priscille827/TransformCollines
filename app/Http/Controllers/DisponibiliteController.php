<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDisponibiliteRequest;
use App\Models\Besoin;
use App\Models\Disponibilite;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisponibiliteController extends Controller
{
    /**
     * Liste des disponibilités du producteur connecté.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && $user->type_compte !== 'admin') {
            abort(403, 'Seuls les producteurs ont des disponibilités.');
        }

        $dispos = Disponibilite::with(['produit', 'besoin.unite.utilisateur'])
            ->where('producteur_id', $user->producteur->id)
            ->latest()
            ->paginate(15);

        return view('disponibilites.index', compact('dispos'));
    }

    /**
     * Formulaire de déclaration.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && $user->type_compte !== 'admin') {
            abort(403, 'Seuls les producteurs peuvent déclarer une disponibilité.');
        }

        $produits = $user->producteur->produits()->orderBy('nom')->get();

        if ($produits->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucun produit renseigné dans votre profil.');
        }

        // Si besoin_id passé en paramètre, on pré-sélectionne
        $besoinPre = null;
        if ($request->filled('besoin_id')) {
            $besoinPre = Besoin::with(['produit', 'unite.utilisateur'])
                ->whereIn('statut', ['actif', 'partiellement_couvert'])
                ->find($request->besoin_id);
        }

        // Besoins actifs correspondant aux produits du producteur
        $besoinsActifs = Besoin::with(['produit', 'unite.utilisateur'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->whereIn('produit_id', $produits->pluck('id'))
            ->orderBy('delai')
            ->get();

        return view('disponibilites.create', compact('produits', 'besoinsActifs', 'besoinPre'));
    }

    /**
     * Enregistrement.
     */
    public function store(StoreDisponibiliteRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        // Vérifier que le produit fait bien partie du profil
        $aLeProduit = $user->producteur->produits()
            ->where('produits.id', $data['produit_id'])
            ->exists();

        if (!$aLeProduit) {
            return back()->withErrors([
                'produit_id' => 'Vous ne cultivez pas ce produit selon votre profil.',
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Statut : "associee" si besoin, sinon "declaree"
            $statut = !empty($data['besoin_id']) ? 'associee' : 'declaree';

            $dispo = Disponibilite::create([
                'producteur_id'      => $user->producteur->id,
                'produit_id'         => $data['produit_id'],
                'besoin_id'          => $data['besoin_id'] ?? null,
                'quantite'           => $data['quantite'],
                'date_disponibilite' => $data['date_disponibilite'],
                'date_expiration'    => now()->addDays(15), // RG-011
                'statut'             => $statut,
            ]);

            DB::commit();

            // L'observer recalcule automatiquement le taux du besoin
            // (aucun appel nécessaire ici)

            return redirect()
                ->route('disponibilites.index')
                ->with('success', 'Disponibilité déclarée. Le taux de couverture a été mis à jour.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Suppression (si non confirmée).
     */
    public function destroy(Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if (!$user->isProducteur() || $disponibilite->producteur_id !== $user->producteur->id) {
            abort(403);
        }

        if ($disponibilite->statut === 'confirmee') {
            return back()->with('error', 'Impossible de supprimer une disponibilité confirmée.');
        }

        $disponibilite->delete();

        return back()->with('success', 'Disponibilité supprimée.');
    }
}