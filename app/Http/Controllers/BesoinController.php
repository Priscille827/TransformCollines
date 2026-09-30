<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBesoinRequest;
use App\Models\Besoin;
use App\Models\Commune;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BesoinController extends Controller
{
    /**
     * Liste des besoins de l'unité connectée.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isUnite() && $user->type_compte !== 'admin') {
            abort(403, 'Seules les unités peuvent accéder à cette page.');
        }

        $besoins = Besoin::with(['produit', 'communes'])
            ->where('unite_id', $user->unite->id)
            ->latest()
            ->paginate(15);

        return view('besoins.index', compact('besoins'));
    }

    /**
     * Formulaire de création.
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isUnite() && $user->type_compte !== 'admin') {
            abort(403, 'Seules les unités peuvent publier un besoin.');
        }

        // L'unité ne voit que les produits qu'elle transforme
        $produits = $user->unite->produits()->orderBy('nom')->get();

        if ($produits->isEmpty()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'Aucun produit renseigné dans votre profil. Contactez l\'administrateur.');
        }

        $communes = Commune::orderBy('nom')->get();

        return view('besoins.create', compact('produits', 'communes'));
    }

    /**
     * Enregistrement.
     */
    public function store(StoreBesoinRequest $request)
    {
        $user = Auth::user();
        $data = $request->validated();

        DB::beginTransaction();
        try {
            $besoin = Besoin::create([
                'unite_id'            => $user->unite->id,
                'produit_id'          => $data['produit_id'],
                'quantite_recherchee' => $data['quantite_recherchee'],
                'delai'               => $data['delai'],
                'taux_couverture'     => 0,
                'statut'              => 'actif',
            ]);

            // Zone de collecte : soit rayon, soit communes
            if ($data['mode_zone'] === 'communes') {
                $besoin->communes()->attach($data['communes']);
            }
            // Si rayon : on l'enregistre côté unité (déjà existant) — pas de table pivot nécessaire

            DB::commit();

            return redirect()
                ->route('besoins.show', $besoin)
                ->with('success', 'Besoin publié avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Détail d'un besoin.
     */
    public function show(Besoin $besoin)
    {
        $besoin->load([
            'produit',
            'communes',
            'unite.utilisateur.commune',
            'disponibilites.producteur.utilisateur',
        ]);

        return view('besoins.show', compact('besoin'));
    }

    /**
     * Liste publique des besoins actifs (tous utilisateurs).
     */
    public function publics(Request $request)
    {
        $query = Besoin::with(['produit', 'unite.utilisateur.commune', 'communes'])
            ->whereIn('statut', ['actif', 'partiellement_couvert']);

        // Filtres
        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }
        if ($request->filled('commune_id')) {
            $query->whereHas('communes', fn($q) => $q->where('commune_id', $request->commune_id));
        }

        $besoins = $query->orderBy('delai')->paginate(12)->withQueryString();

        $produits = Produit::orderBy('nom')->get();
        $communes = Commune::orderBy('nom')->get();

        return view('besoins.index', compact('besoins', 'produits', 'communes'));
    }
}