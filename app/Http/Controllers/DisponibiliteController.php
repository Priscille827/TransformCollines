<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Disponibilite;
use App\Models\Notification;
use App\Models\Stock;
use App\Services\ConversionService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisponibiliteController extends Controller
{
    public function __construct(
        private ConversionService $conversion,
        private StockService $stockService
    ) {}

    /**
     * Liste des disponibilités du producteur.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isAdmin()) {
            abort(403);
        }

        $dispos = Disponibilite::with(['produit', 'uniteMesure', 'besoin.unite.utilisateur'])
            ->where('producteur_id', $user->producteur->id)
            ->latest()
            ->paginate(20);

        $stats = [
            'total'      => Disponibilite::where('producteur_id', $user->producteur->id)->count(),
            'actives'    => Disponibilite::where('producteur_id', $user->producteur->id)
                ->whereIn('statut', ['declaree', 'associee'])->count(),
            'confirmees' => Disponibilite::where('producteur_id', $user->producteur->id)
                ->where('statut', 'confirmee')->count(),
        ];

        return view('disponibilites.index', compact('dispos', 'stats'));
    }

    /**
     * Écran de sélection d'un besoin + stock.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isAdmin()) {
            abort(403, 'Seuls les producteurs peuvent proposer leurs produits.');
        }

        if (!$user->isValide()) {
            return redirect()->route('dashboard')
                ->with('error', 'Votre compte doit être validé pour proposer des produits.');
        }

        // Stock vendable du producteur
        $mesStocks = Stock::with(['produit', 'uniteMesure'])
            ->where('producteur_id', $user->producteur->id)
            ->whereRaw('quantite_disponible - quantite_reservee > 0')
            ->orderBy('produit_id')
            ->get()
            ->keyBy('produit_id');

        if ($mesStocks->isEmpty()) {
            return redirect()->route('stocks.create')
                ->with('error', 'Vous n\'avez pas de stock disponible. Ajoutez d\'abord du stock.');
        }

        // Besoins actifs correspondant aux produits du producteur
        $produitIds = $mesStocks->pluck('produit_id');

        $besoinsActifs = Besoin::with(['produit', 'unite.utilisateur', 'communes'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->whereIn('produit_id', $produitIds)
            ->orderBy('delai')
            ->get()
            ->map(function ($besoin) {
                // Calculer ce qui reste à couvrir
                $dejaCouvert = (float) $besoin->disponibilites()
                    ->whereIn('statut', ['associee', 'confirmee'])
                    ->sum('quantite_kg_equivalent');

                $besoin->restant_kg = max(0, (float) $besoin->quantite_recherchee - $dejaCouvert);
                return $besoin;
            })
            // Ne garder que les besoins non totalement couverts
            ->filter(fn($b) => $b->restant_kg > 0)
            ->values();

        // Si un besoin précis est passé en paramètre
        $besoinPre = null;
        if ($request->filled('besoin_id')) {
            $besoinPre = Besoin::with(['produit', 'unite.utilisateur'])
                ->whereIn('statut', ['actif', 'partiellement_couvert'])
                ->find($request->besoin_id);

            // Recalculer le restant
            if ($besoinPre) {
                $dejaCouvert = (float) $besoinPre->disponibilites()
                    ->whereIn('statut', ['associee', 'confirmee'])
                    ->sum('quantite_kg_equivalent');
                $besoinPre->restant_kg = max(0, (float) $besoinPre->quantite_recherchee - $dejaCouvert);
            }
        }

        // Préparer les données JSON pour Alpine.js
        $stocksJson = $mesStocks->map(function ($s) {
            return [
                'id'             => $s->id,
                'produit'        => $s->produit->nom,
                'unite'          => $s->uniteMesure->nom,
                'poids_unitaire' => (float) ($s->poids_unitaire_kg ?? 0),
                'est_reference'  => (bool) $s->uniteMesure->est_reference,
                'vendable'       => (float) $s->quantite_vendable,
            ];
        })->values()->toArray();

        $besoinsJson = $besoinsActifs->map(function ($b) {
            return [
                'id'         => $b->id,
                'produit_id' => $b->produit_id,
                'restant_kg' => (float) $b->restant_kg,
            ];
        })->values()->toArray();

        // Déterminer le stock présélectionné
        $stockIdInitial = null;
        if ($besoinPre && $besoinPre->produit_id && $mesStocks->has($besoinPre->produit_id)) {
            $stockIdInitial = $mesStocks[$besoinPre->produit_id]->id;
        }

        return view('disponibilites.create', [
            'mesStocks'      => $mesStocks,
            'stocksJson'     => $stocksJson,
            'besoinsJson'    => $besoinsJson,
            'stockIdInitial' => $stockIdInitial,
            'besoinsActifs'  => $besoinsActifs,
            'besoinPre'      => $besoinPre,
        ]);
    }

    /**
     * Enregistrer la proposition (réserve le stock).
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur()) {
            abort(403);
        }

        $data = $request->validate([
            'stock_id'           => 'required|exists:stocks,id',
            'besoin_id'          => 'nullable|exists:besoins,id',
            'quantite'           => 'required|numeric|min:0.01',
            'prix_b2b' => 'nullable|numeric|min:0',
            'mode_saisie'        => 'required|in:unite,kg',
            'date_disponibilite' => 'required|date|after_or_equal:today',
        ], [
            'stock_id.required'  => 'Sélectionnez un stock.',
            'quantite.required'  => 'Indiquez une quantité.',
            'quantite.min'       => 'La quantité doit être supérieure à 0.',
        ]);

        // Vérifier que le stock appartient au producteur
        $stock = Stock::with(['produit', 'uniteMesure'])
            ->where('id', $data['stock_id'])
            ->where('producteur_id', $user->producteur->id)
            ->first();

        if (!$stock) {
            return back()->withErrors(['stock_id' => 'Ce stock ne vous appartient pas.'])->withInput();
        }

        // Convertir la quantité en unité du stock
        $quantiteEnUnite = (float) $data['quantite'];
        $quantiteKg = 0;

        if ($data['mode_saisie'] === 'kg') {
            $quantiteEnUnite = $this->conversion->convertirDepuisKg(
                $stock->uniteMesure,
                (float) $data['quantite'],
                $stock->poids_unitaire_kg
            );
            $quantiteKg = (float) $data['quantite'];
        } else {
            $quantiteKg = $this->conversion->convertirEnKg(
                $stock->uniteMesure,
                (float) $data['quantite'],
                $stock->poids_unitaire_kg
            );
        }

        if ($quantiteKg === null || $quantiteKg <= 0) {
            return back()->withErrors(['quantite' => 'Impossible de convertir cette quantité.'])->withInput();
        }

        // Vérifier le stock vendable
        $vendable = $stock->quantite_vendable;
        if ($quantiteEnUnite > $vendable) {
            return back()->withErrors([
                'quantite' => "Stock insuffisant. Vendable : " .
                    $this->conversion->formaterQuantite(
                        $stock->uniteMesure,
                        $vendable,
                        $stock->poids_unitaire_kg
                    ),
            ])->withInput();
        }

        // Vérifier la limite du besoin (si associé)
        if (!empty($data['besoin_id'])) {
            $besoin = Besoin::find($data['besoin_id']);

            $dejaCouvert = (float) $besoin->disponibilites()
                ->whereIn('statut', ['associee', 'confirmee'])
                ->sum('quantite_kg_equivalent');

            $restantKg = max(0, (float) $besoin->quantite_recherchee - $dejaCouvert);

            if ($quantiteKg > $restantKg) {
                return back()->withErrors([
                    'quantite' => "Impossible de proposer plus que le restant du besoin. " .
                        "Restant à couvrir : " . number_format($restantKg, 0, ',', ' ') . " kg.",
                ])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $dispo = Disponibilite::create([
                'producteur_id'          => $user->producteur->id,
                'produit_id'             => $stock->produit_id,
                'unite_mesure_id'        => $stock->unite_mesure_id,
                'poids_unitaire_kg'      => $stock->poids_unitaire_kg,
                'besoin_id'              => $data['besoin_id'] ?? null,
                'quantite'               => $quantiteEnUnite,
                'quantite_kg_equivalent' => $quantiteKg,
                'prix_b2b' => $data['prix_b2b'] ?? null,
                'date_disponibilite'     => $data['date_disponibilite'],
                'date_expiration'        => now()->addDays(15),
                'statut'                 => !empty($data['besoin_id']) ? 'associee' : 'declaree',
            ]);

            // Réserver le stock
            $this->stockService->reserver($stock, $quantiteEnUnite);

            // Recalculer le besoin
            if ($dispo->besoin_id) {
                app(\App\Services\CouvertureService::class)->recalculer($dispo->besoin);

                Notification::create([
                    'utilisateur_id' => $dispo->besoin->unite->utilisateur_id,
                    'code'           => 'N-01',
                    'titre'          => 'Nouvelle proposition',
                    'canal'          => 'web',
                    'contenu'        => sprintf(
                        '%s propose %s pour votre besoin en %s.',
                        $user->nom,
                        $this->conversion->formaterQuantite(
                            $stock->uniteMesure,
                            $quantiteEnUnite,
                            $stock->poids_unitaire_kg
                        ),
                        $stock->produit->nom
                    ),
                    'url'            => route('besoins.show', $dispo->besoin_id),
                    'lu'             => false,
                    'created_at'     => now(),
                ]);
            }

            DB::commit();

            return redirect()->route('disponibilites.index')
                ->with('success', 'Proposition enregistrée. Votre stock a été réservé.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Supprimer (libère la réservation).
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

        DB::beginTransaction();
        try {
            // Libérer la réservation
            $stock = Stock::where('producteur_id', $user->producteur->id)
                ->where('produit_id', $disponibilite->produit_id)
                ->where('unite_mesure_id', $disponibilite->unite_mesure_id)
                ->first();

            if ($stock && $disponibilite->statut === 'associee') {
                $this->stockService->liberer($stock, (float) $disponibilite->quantite);
            }

            // Si associée, recalculer le besoin
            if ($disponibilite->besoin_id) {
                $besoinId = $disponibilite->besoin_id;
                $disponibilite->delete();

                $besoin = Besoin::find($besoinId);
                if ($besoin) {
                    app(\App\Services\CouvertureService::class)->recalculer($besoin);
                }
            } else {
                $disponibilite->delete();
            }

            DB::commit();

            return back()->with('success', 'Proposition supprimée. Stock libéré.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()]);
        }
    }
}