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
     * ⚠️ Réservé aux unités (pas admin).
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isUnite()) {
            abort(403, 'Seules les unités peuvent accéder à cette page.');
        }

        $besoins = Besoin::with(['produit', 'communes'])
            ->where('unite_id', $user->unite->id)
            ->latest()
            ->paginate(15);

        $stats = [
            'total'    => Besoin::where('unite_id', $user->unite->id)->count(),
            'actifs'   => Besoin::where('unite_id', $user->unite->id)->whereIn('statut', ['actif', 'partiellement_couvert'])->count(),
            'couverts' => Besoin::where('unite_id', $user->unite->id)->where('statut', 'couvert')->count(),
            'expires'  => Besoin::where('unite_id', $user->unite->id)->whereIn('statut', ['expire', 'cloture'])->count(),
        ];

        return view('besoins.index', compact('besoins', 'stats'));
    }

    /**
     * Formulaire de création.
     * ⚠️ Réservé aux unités (pas admin).
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isUnite()) {
            abort(403, 'Seules les unités peuvent publier un besoin.');
        }

        // ⚠️ Uniquement les PRODUITS BRUTS
        $produits = Produit::where('actif', true)
            ->where('statut', 'actif')
            ->where('type_produit', 'brut')
            ->orderBy('nom')
            ->get();

        if ($produits->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Aucun produit brut disponible dans le catalogue.');
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

        // Sécurité : seul l'unité peut publier
        if (!$user->isUnite()) {
            abort(403);
        }

        $data = $request->validated();

        // Vérifier que le produit est bien un produit BRUT
        $produit = Produit::find($data['produit_id']);
        if (!$produit || $produit->type_produit !== 'brut' || $produit->statut !== 'actif') {
            return back()->withErrors([
                'produit_id' => 'Seuls les produits bruts peuvent faire l\'objet d\'un besoin.',
            ])->withInput();
        }

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

            if ($data['mode_zone'] === 'communes') {
                $besoin->communes()->attach($data['communes']);
            }

            DB::commit();

            return redirect()->route('besoins.show', $besoin)
                ->with('success', 'Besoin publié avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Détail d'un besoin.
     * Accessible à tous ceux qui ont le droit de le voir.
     */
    public function show(Besoin $besoin)
    {
        $besoin->load([
            'produit',
            'communes',
            'unite.utilisateur.commune',
            'disponibilites.producteur.utilisateur',
            'disponibilites.uniteMesure',
        ]);

        return view('besoins.show', compact('besoin'));
    }

    /**
     * Liste publique des besoins actifs.
     * ✅ Accessible à : admin, unité, producteur
     * ❌ Non accessible à : population
     */
    public function publics(Request $request)
    {
        $user = Auth::user();

        // Population ne peut pas voir les besoins
        if ($user && $user->isPopulation()) {
            abort(403, 'Les besoins des unités ne sont pas accessibles à la population.');
        }

        $query = Besoin::with(['produit', 'unite.utilisateur.commune', 'communes'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->where('delai', '>=', now());

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }
        if ($request->filled('commune_id')) {
            $query->whereHas('communes', fn($q) => $q->where('commune_id', $request->commune_id));
        }

        $besoins = $query->orderBy('delai')->paginate(12)->withQueryString();

        // Filtres : uniquement les produits bruts
        $produits = Produit::where('actif', true)
            ->where('statut', 'actif')
            ->where('type_produit', 'brut')
            ->orderBy('nom')
            ->get();

        $communes = Commune::orderBy('nom')->get();

        return view('besoins.index', compact('besoins', 'produits', 'communes'));
    }
}