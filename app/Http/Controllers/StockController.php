<?php

namespace App\Http\Controllers;

use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Models\UniteMesure;
use App\Services\ConversionService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function __construct(
        private ConversionService $conversion,
        private StockService $stockService
    ) {}

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

        $query = Stock::with(['produit', 'uniteMesure', 'uniteReference']);

        if ($user->isProducteur()) {
            $query->where('producteur_id', $user->producteur->id);
        } elseif ($user->isUnite()) {
            $query->where('unite_id', $user->unite->id);
        }

        $stocks = $query->orderByDesc('updated_at')->paginate(20);

        $totalKg = $stocks->getCollection()->sum(fn($s) => $s->quantite_kg);
        $totalValeur = $stocks->getCollection()->sum(function ($s) {
            return (float) $s->quantite_disponible * (float) ($s->prix_unitaire ?? 0);
        });

        return view('stocks.index', compact('stocks', 'totalKg', 'totalValeur'));
    }

    public function create()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403);
        }

        $produits = $user->isProducteur()
            ? $user->producteur->produits()->where('actif', true)->orderBy('nom')->get()
            : $user->unite->produits()->where('actif', true)->orderBy('nom')->get();

        if ($produits->isEmpty()) {
            return redirect()->route('produits.choisir')
                ->with('error', 'Commencez par sélectionner vos produits.');
        }

        $mesures = UniteMesure::actives()->mesures()->orderBy('nom')->get();
        $contenants = UniteMesure::actives()->contenants()->orderBy('nom')->get();

        $uniteKg = UniteMesure::where('code', 'kg')->first();
        $uniteLitre = UniteMesure::where('code', 'litre')->first();

        return view('stocks.create', compact('produits', 'mesures', 'contenants', 'uniteKg', 'uniteLitre'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403);
        }

        $data = $request->validate([
            'produit_id'          => 'required|exists:produits,id',
            'type_unite'          => 'required|in:mesure,contenant',
            'unite_mesure_id'     => 'required|exists:unites_mesure,id',
            'poids_unitaire_kg'   => 'nullable|numeric|min:0.001',
            'unite_reference_id'  => 'nullable|exists:unites_mesure,id',
            'quantite_disponible' => 'required|numeric|min:0.01',
            'prix_unitaire'       => 'nullable|numeric|min:0',
            'prix_b2b'            => 'nullable|numeric|min:0',
            'seuil_alerte'        => 'nullable|numeric|min:0',
            'motif'               => 'nullable|string|max:255',
        ], [
            'produit_id.required'          => 'Sélectionnez un produit.',
            'unite_mesure_id.required'     => 'Sélectionnez une unité.',
            'quantite_disponible.required' => 'Indiquez la quantité.',
        ]);

        // ✅ Normaliser : si 0 ou vide → null
        if (empty($data['poids_unitaire_kg']) || (float) $data['poids_unitaire_kg'] <= 0) {
            $data['poids_unitaire_kg'] = null;
        }
        if (empty($data['unite_reference_id']) || (int) $data['unite_reference_id'] <= 0) {
            $data['unite_reference_id'] = null;
        }

        // Vérifier que le produit fait partie du catalogue
        $aLeProduit = $user->isProducteur()
            ? $user->producteur->produits()->where('produits.id', $data['produit_id'])->exists()
            : $user->unite->produits()->where('produits.id', $data['produit_id'])->exists();

        if (!$aLeProduit) {
            return back()->withErrors(['produit_id' => 'Ce produit ne fait pas partie de votre liste.'])->withInput();
        }

        $unite = UniteMesure::find($data['unite_mesure_id']);

        // Si contenant → poids obligatoire
        if ($unite->isContenant() && empty($data['poids_unitaire_kg'])) {
            return back()->withErrors([
                'poids_unitaire_kg' => "Indiquez combien représente 1 {$unite->nom}.",
            ])->withInput();
        }

        // Si contenant → unité de référence obligatoire
        if ($unite->isContenant() && empty($data['unite_reference_id'])) {
            return back()->withErrors([
                'unite_reference_id' => "Choisissez l'unité (g, kg, ml, L).",
            ])->withInput();
        }

        $poids = $unite->isContenant() ? $data['poids_unitaire_kg'] : null;
        $uniteRef = $unite->isContenant() ? $data['unite_reference_id'] : null;

        DB::beginTransaction();
        try {
            $criteria = [
                'produit_id'      => $data['produit_id'],
                'unite_mesure_id' => $data['unite_mesure_id'],
            ];

            if ($user->isProducteur()) {
                $criteria['producteur_id'] = $user->producteur->id;
                $criteria['unite_id'] = null;
            } else {
                $criteria['unite_id'] = $user->unite->id;
                $criteria['producteur_id'] = null;
            }

            $stock = Stock::where($criteria)->first();

            if ($stock) {
                $stock->quantite_disponible = (float) $stock->quantite_disponible + (float) $data['quantite_disponible'];
                if ($poids) $stock->poids_unitaire_kg = $poids;
                if ($uniteRef) $stock->unite_reference_id = $uniteRef;
                if (!empty($data['prix_unitaire'])) $stock->prix_unitaire = $data['prix_unitaire'];
                if (!empty($data['prix_b2b'])) $stock->prix_b2b = $data['prix_b2b'];
                if (!empty($data['seuil_alerte'])) $stock->seuil_alerte = $data['seuil_alerte'];
                $stock->save();
            } else {
                $stock = Stock::create(array_merge($criteria, [
                    'poids_unitaire_kg'   => $poids,
                    'unite_reference_id'  => $uniteRef,
                    'quantite_disponible' => $data['quantite_disponible'],
                    'quantite_reservee'   => 0,
                    'prix_unitaire'       => $data['prix_unitaire'] ?? null,
                    'prix_b2b'            => $data['prix_b2b'] ?? null,
                    'seuil_alerte'        => $data['seuil_alerte'] ?? null,
                ]));
            }

            MouvementStock::create([
                'stock_id'   => $stock->id,
                'type'       => 'entree',
                'quantite'   => $data['quantite_disponible'],
                'motif'      => $data['motif'] ?? 'Ajout de stock',
                'cree_par'   => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('stocks.index')
                ->with('success', 'Stock ajouté avec succès.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    public function edit(Stock $stock)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $stock)) {
            abort(403);
        }

        $stock->load(['produit', 'uniteMesure', 'uniteReference']);
        $mouvements = $stock->mouvements()->latest()->take(20)->get();

        return view('stocks.edit', compact('stock', 'mouvements'));
    }

    public function update(Request $request, Stock $stock)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $stock)) {
            abort(403);
        }

        $data = $request->validate([
            'type'     => 'required|in:entree,sortie,ajustement',
            'quantite' => 'required|numeric|min:0.01',
            'motif'    => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $ancienne = (float) $stock->quantite_disponible;
            $delta = 0;

            switch ($data['type']) {
                case 'entree':
                    $delta = (float) $data['quantite'];
                    break;
                case 'sortie':
                    $vendable = $stock->quantite_vendable;
                    if ((float) $data['quantite'] > $vendable) {
                        throw new \Exception("Stock insuffisant. Vendable : {$vendable}.");
                    }
                    $delta = -(float) $data['quantite'];
                    break;
                case 'ajustement':
                    $delta = (float) $data['quantite'] - $ancienne;
                    break;
            }

            $stock->quantite_disponible = max(0, $ancienne + $delta);
            $stock->save();

            MouvementStock::create([
                'stock_id'   => $stock->id,
                'type'       => $data['type'],
                'quantite'   => $delta,
                'motif'      => $data['motif'] ?? null,
                'cree_par'   => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('stocks.edit', $stock)
                ->with('success', 'Mouvement enregistré.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Stock $stock)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $stock)) {
            abort(403);
        }

        if ($stock->quantite_reservee > 0) {
            return back()->with('error', 'Impossible : ce stock a des réservations en cours.');
        }

        $stock->delete();

        return redirect()->route('stocks.index')->with('success', 'Stock supprimé.');
    }

    private function peutModifier($user, Stock $stock): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isProducteur() && $stock->producteur_id === $user->producteur->id) return true;
        if ($user->isUnite() && $stock->unite_id === $user->unite->id) return true;
        return false;
    }
}