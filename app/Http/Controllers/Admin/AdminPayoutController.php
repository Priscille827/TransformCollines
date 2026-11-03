<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminPayoutController extends Controller
{
    /**
     * Liste de tous les payouts.
     */
  /**
 * Liste des payouts ACTIFS (non terminés).
 */
public function index(Request $request)
{
    $query = Payout::with(['producteur.utilisateur', 'unite.utilisateur'])
        ->where('confirme_vendeur', false);   // ✅ On exclut les terminés

    if ($request->filled('statut')) {
        $query->where('statut', $request->statut);
    }

    if ($request->filled('confirmation')) {
        if ($request->confirmation === 'non_confirme') {
            $query->where('marque_par_admin', true)
                  ->where('confirme_vendeur', false);
        }
    }

    $payouts = $query->orderByDesc('created_at')->paginate(20);

    $stats = [
        'total_a_payer'      => Payout::where('statut', 'en_attente')->where('confirme_vendeur', false)->sum('montant_net'),
        'total_paye'         => Payout::where('marque_par_admin', true)->sum('montant_net'),
        'en_attente_vendeur' => Payout::where('marque_par_admin', true)->where('confirme_vendeur', false)->sum('montant_net'),
        'nb_en_attente'      => Payout::where('statut', 'en_attente')->where('confirme_vendeur', false)->count(),
        'nb_confirmes'       => Payout::where('confirme_vendeur', true)->count(),
    ];

    return view('admin.payouts.index', compact('payouts', 'stats'));
}

/**
 * Historique des payouts TERMINÉS.
 */
public function historique(Request $request)
{
    $query = Payout::with(['producteur.utilisateur', 'unite.utilisateur'])
        ->where('confirme_vendeur', true);    // ✅ Uniquement terminés

    if ($request->filled('vendeur')) {
        $search = $request->vendeur;
        $query->where(function ($q) use ($search) {
            $q->whereHas('producteur.utilisateur', fn($u) => $u->where('nom', 'like', "%{$search}%"))
              ->orWhereHas('unite.utilisateur', fn($u) => $u->where('nom', 'like', "%{$search}%"));
        });
    }

    if ($request->filled('periode')) {
        // format attendu : 2026-10
        [$annee, $mois] = explode('-', $request->periode);
        $query->whereYear('periode_debut', $annee)
              ->whereMonth('periode_debut', $mois);
    }

    $payouts = $query->orderByDesc('confirme_vendeur_le')->paginate(20);

    $stats = [
        'total_historique'   => Payout::where('confirme_vendeur', true)->sum('montant_net'),
        'total_commission'   => Payout::where('confirme_vendeur', true)->sum('commission'),
        'nb_historique'      => Payout::where('confirme_vendeur', true)->count(),
        'derniere_semaine'   => Payout::where('confirme_vendeur', true)
                                    ->where('confirme_vendeur_le', '>=', now()->subDays(7))
                                    ->sum('montant_net'),
    ];

    return view('admin.payouts.historique', compact('payouts', 'stats'));
}

    /**
     * Marquer un payout comme payé par l'admin.
     */
    public function marquerPaye(Request $request, Payout $payout)
    {
        if ($payout->marque_par_admin) {
            return back()->with('info', 'Ce payout est déjà marqué comme payé.');
        }

        $data = $request->validate([
            'reference_kkiapay' => 'required|string|max:100',
        ], [
            'reference_kkiapay.required' => 'Indiquez la référence du paiement Kkiapay.',
        ]);

        $payout->update([
            'statut'             => 'en_cours',
            'marque_par_admin'   => true,
            'marque_par_admin_le' => now(),
            'reference_kkiapay'  => $data['reference_kkiapay'],
            'paye_le'            => now(),
        ]);

        // Notifier le vendeur
        $vendeurUserId = $payout->producteur?->utilisateur_id ?? $payout->unite?->utilisateur_id;

        if ($vendeurUserId) {
            Notification::create([
                'utilisateur_id' => $vendeurUserId,
                'code'           => 'N-04',
                'titre'          => 'Paiement envoyé',
                'canal'          => 'web',
                'contenu'        => "Un paiement de " . number_format($payout->montant_net, 0, ',', ' ') . " FCFA vous a été envoyé. Confirmez sa réception.",
                'url'            => route('suivi.payouts'),
                'lu'             => false,
                'created_at'     => now(),
            ]);
        }

        return back()->with('success', 'Payout marqué comme payé. Le vendeur a été notifié.');
    }

    /**
 * Détail d'un payout : audit des commandes + action de paiement.
 */
public function show(Payout $payout)
{
    $payout->load(['producteur.utilisateur', 'unite.utilisateur']);

    // Récupérer les commandes incluses dans ce payout
    $ids = $payout->commandes_ids ?? [];

    $commandes = \App\Models\Commande::whereIn('id', $ids)
        ->with(['lignes.produit', 'lignes.uniteMesure', 'acheteur'])
        ->orderBy('created_at')
        ->get();

    // Vérification de cohérence : somme des commandes vs montant du payout
    $totalVerifie = $commandes->sum('montant_total');
    $ecart = abs($totalVerifie - (float) $payout->montant);

    // Vendeur lié (pour les infos contact)
    $vendeur = $payout->producteur?->utilisateur ?? $payout->unite?->utilisateur;

    return view('admin.payouts.show', compact(
        'payout',
        'commandes',
        'totalVerifie',
        'ecart',
        'vendeur'
    ));
}
}