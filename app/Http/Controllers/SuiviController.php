<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Stock;
use App\Models\Payout;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SuiviController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403, 'Cette page est réservée aux producteurs et unités de transformation.');
        }

        $producteurId = $user->isProducteur() ? $user->producteur->id : null;
        $uniteId = $user->isUnite() ? $user->unite->id : null;

        // ═══════════════════════════════════════════════════════════
        // 1. STOCK ACTUEL VALORISÉ
        // ═══════════════════════════════════════════════════════════

        $stocks = Stock::with(['produit', 'uniteMesure', 'uniteReference'])
            ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
            ->when($uniteId, fn($q) => $q->where('unite_id', $uniteId))
            ->get();

        // Valeur totale du stock
        $stockValeurDetail = 0;
        $stockValeurGros = 0;
        $stockQuantiteTotale = 0;

        foreach ($stocks as $stock) {
            $vendable = $stock->quantite_vendable;
            $prixDetail = (float) ($stock->prix_unitaire ?? 0);

            // Prix de gros : on peut l'avoir dans les offres actives
            $offreActive = \App\Models\Offre::where('produit_id', $stock->produit_id)
                ->where('unite_mesure_id', $stock->unite_mesure_id)
                ->where('statut', 'active')
                ->where(function ($q) use ($producteurId, $uniteId) {
                    if ($producteurId) $q->where('producteur_id', $producteurId);
                    if ($uniteId) $q->where('unite_id', $uniteId);
                })
                ->first();

            $prixGros = $offreActive?->prix_gros ?? $prixDetail;

            $stockValeurDetail += $vendable * $prixDetail;
            $stockValeurGros += $vendable * (float) $prixGros;
            $stockQuantiteTotale += $stock->quantite_kg;
        }

        // ═══════════════════════════════════════════════════════════
        // 2. CHIFFRE D'AFFAIRES RÉALISÉ
        // ═══════════════════════════════════════════════════════════

        $commandes = Commande::query()
            ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
            ->when($uniteId, fn($q) => $q->whereHas('lignes.offre', fn($s) => $s->where('unite_id', $uniteId)))
            ->with(['lignes.produit', 'acheteur'])
            ->get();

        $commandesTerminees = $commandes->whereIn('statut', ['livree', 'terminee']);
        $commandesEnCours = $commandes->whereIn('statut', ['en_attente', 'acceptee', 'en_preparation', 'expediee', 'disponible_retrait']);
        $commandesAnnulees = $commandes->whereIn('statut', ['annulee', 'refusee']);

        $caRealise = $commandesTerminees->sum('montant_total');
        $caEnAttente = $commandesEnCours->sum('montant_total');
        $caPerdu = $commandesAnnulees->sum('montant_total');

        // ═══════════════════════════════════════════════════════════
        // 3. TOTAL GLOBAL
        // ═══════════════════════════════════════════════════════════

        $totalPatrimoine = $caRealise + $stockValeurDetail;
        $totalPotentiel = $caRealise + $caEnAttente + $stockValeurDetail;

        // ═══════════════════════════════════════════════════════════
        // 4. DÉTAIL PAR PRODUIT
        // ═══════════════════════════════════════════════════════════

        // On regroupe par produit_id
        $produitsSuivi = [];

        // 4.1 — Depuis le stock
        foreach ($stocks as $stock) {
            $produitId = $stock->produit_id;
            $vendable = $stock->quantite_vendable;
            $prixDetail = (float) ($stock->prix_unitaire ?? 0);

            $offreActive = \App\Models\Offre::where('produit_id', $stock->produit_id)
                ->where('unite_mesure_id', $stock->unite_mesure_id)
                ->where('statut', 'active')
                ->where(function ($q) use ($producteurId, $uniteId) {
                    if ($producteurId) $q->where('producteur_id', $producteurId);
                    if ($uniteId) $q->where('unite_id', $uniteId);
                })
                ->first();

            $prixGros = $offreActive?->prix_gros ?? $prixDetail;

            if (!isset($produitsSuivi[$produitId])) {
                $produitsSuivi[$produitId] = [
                    'produit'         => $stock->produit,
                    'unite'           => $stock->uniteMesure,
                    'stock_qte'       => 0,
                    'stock_valeur'    => 0,
                    'stock_valeur_gros' => 0,
                    'ca_realise'      => 0,
                    'qte_vendue'      => 0,
                    'prix_detail'     => $prixDetail,
                    'prix_gros'       => (float) $prixGros,
                ];
            }

            $produitsSuivi[$produitId]['stock_qte'] += $vendable;
            $produitsSuivi[$produitId]['stock_valeur'] += $vendable * $prixDetail;
            $produitsSuivi[$produitId]['stock_valeur_gros'] += $vendable * (float) $prixGros;
        }

        // 4.2 — Depuis les ventes terminées
        $ventesParProduit = LigneCommande::query()
            ->whereHas('commande', function ($q) use ($producteurId, $uniteId) {
                $q->whereIn('statut', ['livree', 'terminee'])
                  ->when($producteurId, fn($s) => $s->where('producteur_id', $producteurId))
                  ->when($uniteId, fn($s) => $s->whereHas('lignes.offre', fn($x) => $x->where('unite_id', $uniteId)));
            })
            ->select('produit_id', DB::raw('SUM(quantite) as qte_vendue'), DB::raw('SUM(sous_total) as ca'))
            ->groupBy('produit_id')
            ->with('produit')
            ->get();

        foreach ($ventesParProduit as $vente) {
            $produitId = $vente->produit_id;

            if (!isset($produitsSuivi[$produitId])) {
                $produitsSuivi[$produitId] = [
                    'produit'         => $vente->produit,
                    'unite'           => null,
                    'stock_qte'       => 0,
                    'stock_valeur'    => 0,
                    'stock_valeur_gros' => 0,
                    'ca_realise'      => 0,
                    'qte_vendue'      => 0,
                    'prix_detail'     => 0,
                    'prix_gros'       => 0,
                ];
            }

            $produitsSuivi[$produitId]['ca_realise'] += (float) $vente->ca;
            $produitsSuivi[$produitId]['qte_vendue'] += (float) $vente->qte_vendue;
        }

        // Trier par CA réalisé décroissant
        usort($produitsSuivi, fn($a, $b) => $b['ca_realise'] <=> $a['ca_realise']);

        // ═══════════════════════════════════════════════════════════
        // 5. CA DES 30 DERNIERS JOURS
        // ═══════════════════════════════════════════════════════════

        $ventes30j = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');

            $totalJour = Commande::query()
                ->whereDate('created_at', $date)
                ->whereIn('statut', ['livree', 'terminee'])
                ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
                ->when($uniteId, fn($q) => $q->whereHas('lignes.offre', fn($s) => $s->where('unite_id', $uniteId)))
                ->sum('montant_total');

            $ventes30j[] = [
                'date'  => $date,
                'label' => now()->subDays($i)->format('d/m'),
                'total' => (float) $totalJour,
            ];
        }

        // ═══════════════════════════════════════════════════════════
        // 6. TOP CLIENTS
        // ═══════════════════════════════════════════════════════════

        $topClients = Commande::query()
            ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
            ->when($uniteId, fn($q) => $q->whereHas('lignes.offre', fn($s) => $s->where('unite_id', $uniteId)))
            ->whereIn('statut', ['livree', 'terminee'])
            ->select('acheteur_id', DB::raw('COUNT(*) as nb_commandes'), DB::raw('SUM(montant_total) as total_achats'))
            ->groupBy('acheteur_id')
            ->with('acheteur')
            ->orderByDesc('total_achats')
            ->take(5)
            ->get();

        // ═══════════════════════════════════════════════════════════
        // 7. COMMANDES RÉCENTES
        // ═══════════════════════════════════════════════════════════

        $commandesRecentes = Commande::query()
            ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
            ->when($uniteId, fn($q) => $q->whereHas('lignes.offre', fn($s) => $s->where('unite_id', $uniteId)))
            ->with(['lignes.produit', 'acheteur'])
            ->latest()
            ->take(10)
            ->get();

        return view('suivi.index', compact(
            'stockValeurDetail',
            'stockValeurGros',
            'stockQuantiteTotale',
            'caRealise',
            'caEnAttente',
            'caPerdu',
            'totalPatrimoine',
            'totalPotentiel',
            'produitsSuivi',
            'ventes30j',
            'topClients',
            'commandesRecentes',
            'stocks'
        ));
    }

    /**
 * Page des payouts du vendeur.
 */
/**
 * Payouts ACTIFS du vendeur connecté.
 */
public function payouts()
{
    $user = Auth::user();

    $baseQuery = Payout::query()
        ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
        ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
        ->where('confirme_vendeur', false);   // ✅ Actifs uniquement

    $stats = [
        'total_du'                   => (clone $baseQuery)->where('statut', 'en_attente')->sum('montant_net'),
        'total_attente_confirmation' => (clone $baseQuery)
                                            ->where('marque_par_admin', true)
                                            ->where('confirme_vendeur', false)
                                            ->sum('montant_net'),
        'total_paye'                 => Payout::query()
                                            ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
                                            ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
                                            ->where('confirme_vendeur', true)
                                            ->sum('montant_net'),
    ];

    $stats['total_brut_cumule'] = Payout::query()
        ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
        ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
        ->sum('montant');

    $stats['total_commission'] = Payout::query()
        ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
        ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
        ->sum('commission');

    $stats['nb_commandes_total'] = Payout::query()
        ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
        ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
        ->pluck('commandes_ids')->flatten()->filter()->unique()->count();

    $caParMois = Payout::query()
        ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
        ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
        ->where('periode_debut', '>=', now()->subMonths(12)->startOfMonth())
        ->selectRaw("DATE_FORMAT(periode_debut, '%Y-%m') as mois, SUM(montant_net) as total")
        ->groupBy('mois')
        ->orderBy('mois')
        ->get()
        ->map(fn($r) => ['mois' => $r->mois, 'total' => (float) $r->total]);

    $payouts = $baseQuery->orderByDesc('periode_debut')->paginate(15);

    // Charger les commandes liées
    $tousLesIds = $payouts->pluck('commandes_ids')->flatten()->filter()->unique()->values();
    $commandesParId = \App\Models\Commande::whereIn('id', $tousLesIds)
        ->with(['lignes.produit'])->get()->keyBy('id');
    $commandesParPayout = [];
    foreach ($payouts as $payout) {
        $ids = $payout->commandes_ids ?? [];
        $commandesParPayout[$payout->id] = collect($ids)
            ->map(fn($id) => $commandesParId->get($id))->filter()->values();
    }

    return view('suivi.payouts', compact('payouts', 'stats', 'caParMois', 'commandesParPayout'));
}

/**
 * Historique des payouts du vendeur.
 */
public function historiquePayouts()
{
    $user = Auth::user();

    $query = Payout::query()
        ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
        ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
        ->where('confirme_vendeur', true)
        ->orderByDesc('confirme_vendeur_le');

    $payouts = $query->paginate(15);

    $stats = [
        'total_recu'       => Payout::query()
                                    ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
                                    ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
                                    ->where('confirme_vendeur', true)
                                    ->sum('montant_net'),
        'nb_payouts'       => Payout::query()
                                    ->when($user->isProducteur(), fn($q) => $q->where('producteur_id', $user->producteur->id))
                                    ->when($user->isUnite(), fn($q) => $q->where('unite_id', $user->unite->id))
                                    ->where('confirme_vendeur', true)
                                    ->count(),
    ];

    return view('suivi.payouts-historique', compact('payouts', 'stats'));
}
/**
 * Le vendeur confirme avoir reçu le paiement.
 */
public function confirmerPayout(Payout $payout)
{
    $user = Auth::user();

    $producteurId = $user->isProducteur() ? $user->producteur->id : null;
    $uniteId = $user->isUnite() ? $user->unite->id : null;

    $appartient = ($producteurId && $payout->producteur_id === $producteurId)
               || ($uniteId && $payout->unite_id === $uniteId);

    if (!$appartient) {
        abort(403);
    }

    if (!$payout->marque_par_admin) {
        return back()->with('error', 'L\'admin n\'a pas encore marqué ce paiement comme effectué.');
    }

    if ($payout->confirme_vendeur) {
        return back()->with('info', 'Vous avez déjà confirmé ce paiement.');
    }

    $payout->update([
        'confirme_vendeur'    => true,
        'confirme_vendeur_le' => now(),
        'statut'              => 'paye',
    ]);

    // Notifier l'admin
    $admins = Utilisateur::where('type_compte', 'admin')->get();
    foreach ($admins as $admin) {
        \App\Models\Notification::create([
            'utilisateur_id' => $admin->id,
            'code'           => 'N-04',
            'titre'          => 'Payout confirmé',
            'canal'          => 'web',
            'contenu'        => "{$user->nom} a confirmé avoir reçu son paiement de " . number_format($payout->montant_net, 0, ',', ' ') . " FCFA.",
            'url'            => route('admin.payouts'),
            'lu'             => false,
            'created_at'     => now(),
        ]);
    }

    return back()->with('success', 'Merci ! Votre confirmation a été enregistrée.');
}
}