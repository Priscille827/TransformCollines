<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Commune;
use App\Models\Offre;
use App\Models\Produit;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    /**
     * Catalogue public — accessible sans compte.
     */
    public function index(Request $request)
    {
        $query = Offre::with(['produit', 'categorie', 'uniteMesure', 'producteur.utilisateur.commune', 'unite.utilisateur.commune'])
            ->where('statut', 'active')
            ->where('quantite_disponible', '>', 0);

        // Filtres
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sq) use ($q) {
                $sq->where('titre', 'LIKE', "%{$q}%")
                    ->orWhere('description', 'LIKE', "%{$q}%");
            });
        }
        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }
        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }
        if ($request->filled('commune_id')) {
            $query->where(function ($sq) use ($request) {
                $sq->whereHas('producteur.utilisateur', fn($u) => $u->where('commune_id', $request->commune_id))
                    ->orWhereHas('unite.utilisateur', fn($u) => $u->where('commune_id', $request->commune_id));
            });
        }

        // Tri
        $tri = $request->input('tri', 'recent');
        match ($tri) {
            'prix_asc'  => $query->orderBy('prix_detail'),
            'prix_desc' => $query->orderByDesc('prix_detail'),
            'populaire' => $query->orderByDesc('vues'),
            default     => $query->latest(),
        };

        $offres     = $query->paginate(12)->withQueryString();
        $categories = Categorie::actives()->get();
        $produits   = Produit::actifs()->orderBy('nom')->get();
        $communes   = Commune::orderBy('nom')->get();

        return view('marketplace.index', compact('offres', 'categories', 'produits', 'communes'));
    }

    /**
     * Détail public d'une offre.
     */
    public function show(Offre $offre)
    {
        if ($offre->statut !== 'active') {
            abort(404);
        }

        // Incrémenter les vues
        $offre->increment('vues');

        $offre->load([
            'produit',
            'categorie',
            'uniteMesure',
            'photos',
            'producteur.utilisateur.commune',
            'unite.utilisateur.commune',
        ]);

        // Autres offres du même vendeur
        $autresOffres = Offre::with(['produit', 'uniteMesure'])
            ->where('id', '!=', $offre->id)
            ->where('statut', 'active')
            ->where(function ($q) use ($offre) {
                if ($offre->producteur_id) {
                    $q->where('producteur_id', $offre->producteur_id);
                } else {
                    $q->where('unite_id', $offre->unite_id);
                }
            })
            ->take(4)
            ->get();

        return view('marketplace.show', compact('offre', 'autresOffres'));
    }

    /**
     * Profil public d'un vendeur (producteur ou unité).
     */
    public function vendeur($type, $id)
    {
        if (!in_array($type, ['producteur', 'unite'])) {
            abort(404);
        }

        if ($type === 'producteur') {
            $vendeur = \App\Models\Producteur::with('utilisateur.commune')->findOrFail($id);
        } else {
            $vendeur = \App\Models\UniteTransformation::with('utilisateur.commune')->findOrFail($id);
        }

        // Vérifier que le compte est validé et actif
        if (!$vendeur->utilisateur->isValide() || $vendeur->utilisateur->statut_compte !== 'actif') {
            abort(404);
        }

        $offres = Offre::with(['produit', 'uniteMesure'])
            ->where('statut', 'active')
            ->where('quantite_disponible', '>', 0)
            ->where(function ($q) use ($type, $id) {
                if ($type === 'producteur') {
                    $q->where('producteur_id', $id);
                } else {
                    $q->where('unite_id', $id);
                }
            })
            ->latest()
            ->paginate(12);

        return view('marketplace.vendeur', compact('vendeur', 'type', 'offres'));
    }
}