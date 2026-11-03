<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Commande;
use App\Models\Disponibilite;
use App\Models\LigneCommande;
use App\Models\Notification;
use App\Models\Stock;
use App\Services\ConversionService;
use App\Services\CouvertureService;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionController extends Controller
{
    public function __construct(
        private ConversionService $conversion,
        private StockService $stockService,
        private CouvertureService $couverture
    ) {}

    /**
     * Liste des propositions reçues (à accepter et payer).
     * Ne montre que les propositions NON ENCORE PAYÉES.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isUnite() && !$user->isAdmin()) {
            abort(403);
        }

        $dispos = Disponibilite::with([
                'produit',
                'uniteMesure',
                'producteur.utilisateur.commune',
                'besoin',
            ])
            ->whereHas('besoin', fn($q) => $q->where('unite_id', $user->unite->id))
            ->whereIn('statut', ['associee'])  // ← 'associee' seulement, pas 'en_paiement'
            ->whereNotNull('unite_mesure_id')
            ->orderBy('date_disponibilite')
            ->paginate(20);

        return view('receptions.index', compact('dispos'));
    }

    /**
     * Accepter une proposition et payer (crée la commande).
     */
    public function accepterEtPayer(Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if ($disponibilite->besoin->unite_id !== $user->unite->id) {
            abort(403);
        }

        if ($disponibilite->statut !== 'associee') {
            return back()->with('error', 'Cette proposition a déjà été traitée.');
        }

        DB::beginTransaction();
        try {
            // Calculer le montant avec le prix B2B
            $prixUnitaire = $disponibilite->prixB2BApplique();

            if ($prixUnitaire <= 0) {
                throw new \Exception('Le prix B2B n\'est pas défini pour cette proposition.');
            }

            $montantTotal = $prixUnitaire * (float) $disponibilite->quantite;

            // Créer la commande
            $commande = Commande::create([
                'reference'         => Commande::genererReference(),
                'acheteur_id'       => $user->id,
                'producteur_id'     => $disponibilite->producteur_id,
                'montant_total'     => $montantTotal,
                'statut'            => 'acceptee',
                'statut_paiement'   => 'en_attente',
                'origine'           => 'besoin',
                'disponibilite_id'  => $disponibilite->id,
                'mode_reception'    => 'retrait',
            ]);

            // Créer la ligne de commande
            LigneCommande::create([
                'commande_id'     => $commande->id,
                'offre_id'        => null,
                'produit_id'      => $disponibilite->produit_id,
                'unite_mesure_id' => $disponibilite->unite_mesure_id,
                'quantite'        => $disponibilite->quantite,
                'prix_unitaire'   => $prixUnitaire,
                'sous_total'      => $montantTotal,
            ]);

            // ⚠️ Sortir la dispo de la liste des propositions
            $disponibilite->update(['statut' => 'en_paiement']);

            DB::commit();

            return redirect()->route('paiement.checkout', $commande)
                ->with('success', 'Proposition acceptée. Procédez au paiement pour confirmer.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()]);
        }
    }

    /**
     * Refuser une proposition avec motif.
     */
    public function refuser(Request $request, Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if ($disponibilite->besoin->unite_id !== $user->unite->id) {
            abort(403);
        }

        if ($disponibilite->statut !== 'associee') {
            return back()->with('error', 'Cette proposition a déjà été traitée.');
        }

        $data = $request->validate([
            'motif_refus' => 'required|string|max:500',
        ], [
            'motif_refus.required' => 'Indiquez le motif du refus.',
        ]);

        DB::beginTransaction();
        try {
            // ⚠️ Récupérer le besoin_id AVANT de le mettre à null
            $besoinId = $disponibilite->besoin_id;

            // Libérer le stock réservé
            $stock = Stock::where('producteur_id', $disponibilite->producteur_id)
                ->where('produit_id', $disponibilite->produit_id)
                ->where('unite_mesure_id', $disponibilite->unite_mesure_id)
                ->first();

            if ($stock) {
                $this->stockService->liberer($stock, (float) $disponibilite->quantite);
            }

            // Marquer la disponibilité comme refusée (libérée)
            $disponibilite->update([
                'statut'    => 'declaree',
                'besoin_id' => null,
            ]);

            // Recalculer le taux de couverture du besoin
            $besoin = Besoin::find($besoinId);
            if ($besoin) {
                $this->couverture->recalculer($besoin);
            }

            // Notifier le producteur
            Notification::create([
                'utilisateur_id' => $disponibilite->producteur->utilisateur_id,
                'code'           => 'N-04',
                'titre'          => 'Proposition refusée',
                'canal'          => 'web',
                'contenu'        => "Votre proposition de {$disponibilite->quantite} {$disponibilite->uniteMesure->nom} a été refusée. Motif : {$data['motif_refus']}",
                'url'            => route('disponibilites.index'),
                'lu'             => false,
                'created_at'     => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Proposition refusée. Le producteur a été notifié.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()]);
        }
    }

    /**
     * Historique des réceptions confirmées.
     */
    public function historique(Request $request)
    {
        $user = Auth::user();

        if (!$user->isUnite() && !$user->isAdmin()) {
            abort(403);
        }

        $query = Disponibilite::with([
                'produit',
                'uniteMesure',
                'producteur.utilisateur.commune',
                'besoin',
            ])
            ->whereHas('besoin', fn($q) => $q->where('unite_id', $user->unite->id))
            ->where('statut', 'confirmee')
            ->whereNotNull('unite_mesure_id');

        if ($request->filled('produit_id')) {
            $query->where('produit_id', $request->produit_id);
        }

        if ($request->filled('periode')) {
            $dateDebut = match ($request->periode) {
                '7'   => now()->subDays(7),
                '30'  => now()->subDays(30),
                '90'  => now()->subDays(90),
                '365' => now()->subDays(365),
                default => null,
            };
            if ($dateDebut) $query->where('updated_at', '>=', $dateDebut);
        }

        if ($request->filled('ecart')) {
            if ($request->ecart === 'conforme') {
                $query->whereRaw('quantite_livree = quantite');
            } elseif ($request->ecart === 'partiel') {
                $query->whereRaw('quantite_livree < quantite AND quantite_livree > 0');
            } elseif ($request->ecart === 'absent') {
                $query->where('quantite_livree', 0);
            }
        }

        $receptions = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        $toutes = Disponibilite::whereHas('besoin', fn($q) => $q->where('unite_id', $user->unite->id))
            ->where('statut', 'confirmee')
            ->get();

        $stats = [
            'total'           => $toutes->count(),
            'conformes'       => $toutes->filter(fn($r) => (float) $r->quantite_livree == (float) $r->quantite)->count(),
            'partielles'      => $toutes->filter(fn($r) => (float) $r->quantite_livree < (float) $r->quantite && (float) $r->quantite_livree > 0)->count(),
            'absentes'        => $toutes->filter(fn($r) => (float) $r->quantite_livree == 0)->count(),
            'volume_total_kg' => (float) $toutes->sum('quantite_kg_equivalent'),
        ];

        $stats['taux_conformite'] = $stats['total'] > 0
            ? round(($stats['conformes'] / $stats['total']) * 100, 1)
            : 0;

        $produits = \App\Models\Produit::orderBy('nom')->get();

        return view('receptions.historique', compact('receptions', 'stats', 'produits'));
    }
}