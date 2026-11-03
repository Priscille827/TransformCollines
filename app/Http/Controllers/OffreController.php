<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\Offre;
use App\Models\Stock;
use App\Models\UniteMesure;
use App\Services\ConversionService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OffreController extends Controller
{
    public function __construct(
        private ConversionService $conversion,
        private StockService $stockService
    ) {}

    /**
     * Mes ventes.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite() && !$user->isAdmin()) {
            abort(403);
        }

        if (!$user->isValide() && !$user->isAdmin()) {
            return redirect()->route('dashboard')
                ->with('error', 'Votre compte doit être validé.');
        }

        $query = Offre::with(['produit', 'uniteMesure', 'categorie']);

        if ($user->isProducteur()) {
            $query->where('producteur_id', $user->producteur->id);
        } elseif ($user->isUnite()) {
            $query->where('unite_id', $user->unite->id);
        }

        $offres = $query->latest()->paginate(15);

        $stats = [
            'total'    => $offres->total(),
            'actives'  => (clone $query)->where('statut', 'active')->count(),
            'epuisees' => (clone $query)->where('statut', 'epuisee')->count(),
        ];

        return view('offres.index', compact('offres', 'stats'));
    }

    /**
     * Formulaire de création — depuis le stock uniquement.
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403);
        }

        if (!$user->isValide()) {
            return redirect()->route('dashboard')
                ->with('error', 'Votre compte doit être validé.');
        }

        // Récupérer le stock vendable (producteur OU unité)
        $query = Stock::with(['produit.categorie', 'uniteMesure', 'uniteReference'])
            ->whereRaw('quantite_disponible - quantite_reservee > 0');

        if ($user->isProducteur()) {
            $query->where('producteur_id', $user->producteur->id);
        } else {
            $query->where('unite_id', $user->unite->id);
        }

        $mesStocks = $query->orderBy('produit_id')->get();

        if ($mesStocks->isEmpty()) {
            return redirect()->route('stocks.create')
                ->with('error', 'Vous n\'avez pas de stock disponible. Ajoutez d\'abord du stock.');
        }

        // Préparer JSON pour Alpine
        $stocksJson = $mesStocks->map(function ($s) {
            return [
                'id'             => $s->id,
                'produit_id'     => $s->produit_id,
                'produit'        => $s->produit->nom,
                'categorie_id'   => $s->produit->categorie_id,
                'unite_id'       => $s->unite_mesure_id,
                'unite'          => $s->uniteMesure->nom,
                'symbole'        => $s->uniteMesure->symbole,
                'poids_unitaire' => (float) ($s->poids_unitaire_kg ?? 0),
                'est_reference'  => (bool) $s->uniteMesure->est_reference,
                'unite_ref'      => $s->uniteReference?->symbole ?? 'kg',
                'vendable'       => (float) $s->quantite_vendable,
                'prix_suggere'   => (float) ($s->prix_unitaire ?? 0),
            ];
        })->values()->toArray();

        $categories = Categorie::actives()->get();

        return view('offres.create', compact('mesStocks', 'stocksJson', 'categories'));
    }

    /**
     * Enregistrer l'offre (depuis stock).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403);
        }

        $data = $request->validate([
            'stock_id'            => 'required|exists:stocks,id',
            'titre'               => 'required|string|max:150',
            'description'         => 'nullable|string|max:2000',
            'quantite_disponible' => 'required|numeric|min:0.01',
            'prix_detail'         => 'required|numeric|min:0',
            'prix_gros'           => 'nullable|numeric|min:0',
            'seuil_gros'          => 'nullable|numeric|min:0',
            'localisation'        => 'nullable|string|max:255',
            'conditions_retrait'  => 'nullable|string|max:1000',
        ]);

        // Vérifier que le stock appartient à l'utilisateur
        $stock = Stock::with(['produit', 'uniteMesure', 'uniteReference'])
            ->where('id', $data['stock_id']);

        if ($user->isProducteur()) {
            $stock->where('producteur_id', $user->producteur->id);
        } else {
            $stock->where('unite_id', $user->unite->id);
        }

        $stock = $stock->first();

        if (!$stock) {
            return back()->withErrors(['stock_id' => 'Ce stock ne vous appartient pas.'])->withInput();
        }

        // Vérifier la quantité
        if ((float) $data['quantite_disponible'] > $stock->quantite_vendable) {
            return back()->withErrors([
                'quantite_disponible' => "Quantité insuffisante. Vendable : {$stock->quantite_vendable}.",
            ])->withInput();
        }

        // Calculer kg équivalent
        $quantiteKg = $this->conversion->convertirEnKg(
            $stock->uniteMesure,
            (float) $data['quantite_disponible'],
            $stock->poids_unitaire_kg
        );

        DB::beginTransaction();
        try {
            $offre = Offre::create([
                'producteur_id'          => $user->isProducteur() ? $user->producteur->id : null,
                'unite_id'               => $user->isUnite() ? $user->unite->id : null,
                'produit_id'             => $stock->produit_id,
                'categorie_id'           => $stock->produit->categorie_id,
                'titre'                  => $data['titre'],
                'description'            => $data['description'] ?? null,
                'unite_mesure_id'        => $stock->unite_mesure_id,
                'poids_unitaire_kg'      => $stock->poids_unitaire_kg,
                'unite_reference_id'     => $stock->unite_reference_id,
                'quantite_disponible'    => $data['quantite_disponible'],
                'quantite_kg_equivalent' => $quantiteKg,
                'prix_detail'            => $data['prix_detail'],
                'prix_gros'              => $data['prix_gros'] ?? null,
                'seuil_gros'             => $data['seuil_gros'] ?? null,
                'localisation'           => $data['localisation'] ?? null,
                'conditions_retrait'     => $data['conditions_retrait'] ?? null,
                'statut'                 => 'active',
            ]);

            // Réserver le stock
            $this->stockService->reserver($stock, (float) $data['quantite_disponible']);

            DB::commit();

            return redirect()->route('offres.show', $offre)
                ->with('success', 'Offre publiée. Elle est maintenant visible sur le marché.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    public function show(Offre $offre)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $offre)) {
            abort(403);
        }

        $offre->load(['produit', 'uniteMesure', 'uniteReference', 'categorie', 'photos']);

        return view('offres.show', compact('offre'));
    }

    public function edit(Offre $offre)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $offre)) {
            abort(403);
        }

        $categories = Categorie::actives()->get();

        return view('offres.edit', compact('offre', 'categories'));
    }

    public function update(Request $request, Offre $offre)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $offre)) {
            abort(403);
        }

        $data = $request->validate([
            'titre'              => 'required|string|max:150',
            'description'        => 'nullable|string|max:2000',
            'prix_detail'        => 'required|numeric|min:0',
            'prix_gros'          => 'nullable|numeric|min:0',
            'seuil_gros'         => 'nullable|numeric|min:0',
            'localisation'       => 'nullable|string|max:255',
            'conditions_retrait' => 'nullable|string|max:1000',
            'statut'             => 'required|in:brouillon,active,epuisee,archivee',
        ]);

        $offre->update($data);

        return redirect()->route('offres.show', $offre)
            ->with('success', 'Offre mise à jour.');
    }

    public function destroy(Offre $offre)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $offre)) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            // Libérer la réservation
            if ($offre->statut === 'active') {
                $query = Stock::where('produit_id', $offre->produit_id)
                    ->where('unite_mesure_id', $offre->unite_mesure_id);

                if ($offre->producteur_id) {
                    $query->where('producteur_id', $offre->producteur_id);
                } else {
                    $query->where('unite_id', $offre->unite_id);
                }

                $stock = $query->first();

                if ($stock) {
                    $this->stockService->liberer($stock, (float) $offre->quantite_disponible);
                }
            }

            $offre->delete();
            DB::commit();

            return redirect()->route('offres.index')
                ->with('success', 'Offre supprimée. Stock libéré.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()]);
        }
    }

    private function peutModifier($user, Offre $offre): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isProducteur() && $offre->producteur_id === $user->producteur->id) return true;
        if ($user->isUnite() && $offre->unite_id === $user->unite->id) return true;
        return false;
    }
}