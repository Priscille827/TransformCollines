<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\PanierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanierController extends Controller
{
    /**
     * Afficher le panier.
     */
    public function index()
    {
        $items = PanierItem::with(['offre.produit', 'offre.uniteMesure', 'offre.producteur.utilisateur', 'offre.unite.utilisateur'])
            ->where('utilisateur_id', Auth::id())
            ->get();

        $total = $items->sum(fn($i) => $i->sousTotal());

        return view('panier.index', compact('items', 'total'));
    }

    /**
     * Ajouter au panier (avec redirection login si non connecté — géré par le middleware).
     */
    public function ajouter(Request $request, Offre $offre)
    {
        if ($offre->statut !== 'active' || $offre->quantite_disponible <= 0) {
            return back()->with('error', 'Cette offre n\'est plus disponible.');
        }

        $data = $request->validate([
            'quantite' => ['required', 'numeric', 'min:0.01'],
        ]);

        // Vérifier la disponibilité
        if ($data['quantite'] > $offre->quantite_disponible) {
            return back()->withErrors([
                'quantite' => "Quantité demandée supérieure au stock disponible ({$offre->quantite_disponible} {$offre->uniteMesure->symbole}).",
            ]);
        }

        // Vérifier qu'on ne commande pas sa propre offre
        $user = Auth::user();
        if ($offre->producteur_id && $user->producteur?->id === $offre->producteur_id) {
            return back()->with('error', 'Vous ne pouvez pas commander votre propre offre.');
        }

        PanierItem::updateOrCreate(
            [
                'utilisateur_id' => $user->id,
                'offre_id'       => $offre->id,
            ],
            [
                'quantite' => $data['quantite'],
            ]
        );

        return redirect()->route('panier.index')
            ->with('success', 'Article ajouté au panier.');
    }

    /**
     * Modifier la quantité.
     */
    public function modifier(Request $request, PanierItem $item)
    {
        if ($item->utilisateur_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'quantite' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($data['quantite'] > $item->offre->quantite_disponible) {
            return back()->withErrors(['quantite' => 'Quantité supérieure au stock disponible.']);
        }

        $item->update(['quantite' => $data['quantite']]);

        return back()->with('success', 'Quantité mise à jour.');
    }

    /**
     * Retirer du panier.
     */
    public function retirer(PanierItem $item)
    {
        if ($item->utilisateur_id !== Auth::id()) {
            abort(403);
        }

        $item->delete();

        return back()->with('success', 'Article retiré du panier.');
    }
}