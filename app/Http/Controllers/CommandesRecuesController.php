<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\MouvementStock;
use App\Models\Notification;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandesRecuesController extends Controller
{
    /**
     * Liste des commandes reçues (vendeur).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite() && !$user->isAdmin()) {
            abort(403, 'Accès réservé aux vendeurs.');
        }

        $query = Commande::with(['lignes.produit', 'acheteur.commune']);

        if ($user->isProducteur()) {
            $query->where('producteur_id', $user->producteur->id);
        } elseif ($user->isUnite()) {
            $query->whereHas('lignes.offre', fn($q) => $q->where('unite_id', $user->unite->id));
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $commandes = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'en_attente' => (clone $query)->where('statut', 'en_attente')->count(),
            'en_cours'   => (clone $query)->whereIn('statut', ['acceptee', 'en_preparation', 'expediee', 'disponible_retrait'])->count(),
            'terminees'  => (clone $query)->whereIn('statut', ['livree', 'terminee'])->count(),
        ];

        return view('commandes-recues.index', compact('commandes', 'stats'));
    }

    /**
     * Détail d'une commande reçue.
     */
    public function show(Commande $commande)
    {
        $user = Auth::user();

        if (!$this->peutVoir($user, $commande)) {
            abort(403);
        }

        $commande->load(['lignes.produit', 'lignes.uniteMesure', 'lignes.offre', 'acheteur']);

        return view('commandes-recues.show', compact('commande'));
    }

    /**
     * Accepter une commande.
     */
    public function accepter(Commande $commande)
    {
        $user = Auth::user();

        if (!$this->peutVoir($user, $commande)) {
            abort(403);
        }

        if ($commande->statut !== 'en_attente') {
            return back()->with('error', 'Cette commande ne peut plus être acceptée.');
        }

        DB::beginTransaction();
        try {
            // Vérifier les stocks et les décrémenter
            foreach ($commande->lignes as $ligne) {
                $offre = $ligne->offre;
                if (!$offre) continue;

                if ($ligne->quantite > $offre->quantite_disponible) {
                    throw new \Exception("Stock insuffisant pour « {$offre->titre} ».");
                }

                $offre->decrement('quantite_disponible', $ligne->quantite);

                if ($offre->fresh()->quantite_disponible <= 0) {
                    $offre->update(['statut' => 'epuisee']);
                }

                if ($commande->producteur_id) {
                    $stock = Stock::where('producteur_id', $commande->producteur_id)
                        ->where('produit_id', $ligne->produit_id)
                        ->where('unite_mesure_id', $ligne->unite_mesure_id)
                        ->first();

                    if ($stock && $stock->quantite_disponible >= $ligne->quantite) {
                        $stock->decrement('quantite_disponible', $ligne->quantite);

                        MouvementStock::create([
                            'stock_id'       => $stock->id,
                            'type'           => 'sortie',
                            'quantite'       => -$ligne->quantite,
                            'motif'          => 'Commande ' . $commande->reference,
                            'reference_type' => Commande::class,
                            'reference_id'   => $commande->id,
                            'cree_par'       => $user->id,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            $commande->update(['statut' => 'acceptee']);

            Notification::create([
                'utilisateur_id' => $commande->acheteur_id,
                'code'           => 'N-05',
                'titre'          => 'Commande acceptée',
                'canal'          => 'web',
                'contenu'        => "Votre commande {$commande->reference} a été acceptée. Vous pouvez procéder au paiement.",
                'url'            => route('paiement.checkout', $commande),
                'lu'             => false,
                'created_at'     => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Commande acceptée. Le client peut maintenant procéder au paiement.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Refuser une commande.
     */
    public function refuser(Request $request, Commande $commande)
    {
        $user = Auth::user();

        if (!$this->peutVoir($user, $commande)) {
            abort(403);
        }

        if ($commande->statut !== 'en_attente') {
            return back()->with('error', 'Cette commande ne peut plus être refusée.');
        }

        $data = $request->validate([
            'motif_annulation' => 'required|string|max:500',
        ]);

        $commande->update([
            'statut'           => 'refusee',
            'motif_annulation' => $data['motif_annulation'],
        ]);

        Notification::create([
            'utilisateur_id' => $commande->acheteur_id,
            'code'           => 'N-05',
            'titre'          => 'Commande refusée',
            'canal'          => 'web',
            'contenu'        => "Votre commande {$commande->reference} a été refusée. Motif : {$data['motif_annulation']}",
            'url'            => route('commandes.show', $commande),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return back()->with('success', 'Commande refusée.');
    }

    

    /**
     * Le vendeur confirme avoir livré.
     */
    public function confirmerLivraison(Commande $commande)
    {
        $user = Auth::user();

        if (!$this->peutVoir($user, $commande)) {
            abort(403);
        }

        if ($commande->statut_paiement !== 'paye') {
            return back()->with('error', 'Le paiement n\'a pas encore été reçu. Attendez la confirmation du client.');
        }

        if ($commande->confirme_vendeur) {
            return back()->with('info', 'Vous avez déjà confirmé la livraison.');
        }

        if (!in_array($commande->statut, ['en_preparation', 'expediee', 'disponible_retrait', 'livree'])) {
            return back()->with('error', 'Vous ne pouvez pas encore confirmer la livraison.');
        }

        $commande->update([
            'confirme_vendeur'    => true,
            'confirme_vendeur_le' => now(),
        ]);

        Notification::create([
            'utilisateur_id' => $commande->acheteur_id,
            'code'           => 'N-05',
            'titre'          => 'Livraison confirmée par le vendeur',
            'canal'          => 'web',
            'contenu'        => "Le vendeur a confirmé la livraison de {$commande->reference}. Confirmez la réception pour terminer la transaction.",
            'url'            => route('commandes.show', $commande),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        // Vérifier si les 2 ont confirmé
        $this->verifierFinalisation($commande);

        return back()->with('success', 'Livraison confirmée. En attente de la confirmation du client.');
    }

    /**
     * Vérifie si les 2 parties ont confirmé → commande terminée.
     * Appelée publiquement pour être utilisée depuis CommandeController.
     */
    public function verifierFinalisation(Commande $commande): void
{
    $commande->refresh();

    if (!$commande->confirme_vendeur || !$commande->confirme_acheteur || $commande->statut === 'terminee') {
        return;
    }

    DB::beginTransaction();
    try {
        // 1. Passer la commande à terminee
        $commande->update(['statut' => 'terminee']);

        // 2. Si la commande vient d'un besoin → finaliser la dispo et le stock
        if ($commande->origine === 'besoin' && $commande->disponibilite_id) {
            $disponibilite = \App\Models\Disponibilite::find($commande->disponibilite_id);

            if ($disponibilite) {
                $disponibilite->update([
                    'statut'                 => 'confirmee',
                    'quantite_livree'        => $disponibilite->quantite,
                    'quantite_kg_equivalent' => $disponibilite->quantite_kg_equivalent,
                ]);

                $stock = Stock::where('producteur_id', $disponibilite->producteur_id)
                    ->where('produit_id', $disponibilite->produit_id)
                    ->where('unite_mesure_id', $disponibilite->unite_mesure_id)
                    ->first();

                if ($stock) {
                    $quantite = (float) $disponibilite->quantite;

                    $stock->update([
                        'quantite_disponible' => max(0, (float) $stock->quantite_disponible - $quantite),
                        'quantite_reservee'   => max(0, (float) $stock->quantite_reservee - $quantite),
                    ]);

                    MouvementStock::create([
                        'stock_id'       => $stock->id,
                        'type'           => 'sortie',
                        'quantite'       => -$quantite,
                        'motif'          => 'Commande terminée ' . $commande->reference,
                        'reference_type' => Commande::class,
                        'reference_id'   => $commande->id,
                        'cree_par'       => Auth::id(),
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]);
                }

                app(\App\Services\CouvertureService::class)->recalculer($disponibilite->besoin);
            }
        }

        // 3. Si marketplace → libérer la réservation du stock
        if ($commande->origine === 'marketplace') {
            foreach ($commande->lignes as $ligne) {
                if ($ligne->offre_id) {
                    $offre = \App\Models\Offre::find($ligne->offre_id);
                    if ($offre && $offre->producteur_id) {
                        $stock = Stock::where('producteur_id', $offre->producteur_id)
                            ->where('produit_id', $offre->produit_id)
                            ->where('unite_mesure_id', $offre->unite_mesure_id)
                            ->first();

                        if ($stock) {
                            $quantite = (float) $ligne->quantite;
                            $stock->update([
                                'quantite_reservee' => max(0, (float) $stock->quantite_reservee - $quantite),
                            ]);
                        }
                    }
                }
            }
        }

        // 4. Enregistrer dans le payout du vendeur
        app(\App\Services\PayoutService::class)->enregistrerCommande($commande);

        // 5. Notifier le vendeur
        Notification::create([
            'utilisateur_id' => $commande->producteur->utilisateur_id,
            'code'           => 'N-04',
            'titre'          => 'Transaction terminée',
            'canal'          => 'web',
            'contenu'        => "La commande {$commande->reference} est terminée. Votre paiement de " . number_format($commande->montant_total, 0, ',', ' ') . " FCFA sera reversé à la fin de la semaine.",
            'url'            => route('suivi.index'),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        // 6. Notifier l'acheteur
        Notification::create([
            'utilisateur_id' => $commande->acheteur_id,
            'code'           => 'N-04',
            'titre'          => 'Transaction terminée',
            'canal'          => 'web',
            'contenu'        => "La transaction {$commande->reference} est finalisée. Merci pour votre confiance !",
            'url'            => route('commandes.show', $commande),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        DB::commit();

    } catch (\Throwable $e) {
        DB::rollBack();
        \Log::error('Erreur verifierFinalisation: ' . $e->getMessage());
    }
}

    /**
     * Vérifie si l'utilisateur peut voir cette commande en tant que vendeur.
     */
    private function peutVoir($user, Commande $commande): bool
    {
        if ($user->isAdmin()) return true;

        if ($user->isProducteur() && $commande->producteur_id === $user->producteur->id) {
            return true;
        }

        if ($user->isUnite()) {
            return $commande->lignes()->whereHas('offre', fn($q) => $q->where('unite_id', $user->unite->id))->exists();
        }

        return false;
    }

    public function changerStatut(Request $request, Commande $commande)
{
    $user = Auth::user();

    if (!$this->peutVoir($user, $commande)) {
        abort(403);
    }

    $data = $request->validate([
    'statut' => 'required|in:en_preparation,expediee,disponible_retrait,livree',
]);

    // ⚠️ NOUVEAU : exiger le paiement avant "en_preparation"
    if (in_array($data['statut'], ['en_preparation', 'expediee', 'disponible_retrait', 'livree', 'terminee'])
        && $commande->statut_paiement !== 'paye') {
        return back()->with('error', 'Le client doit d\'abord payer la commande.');
    }

    $transitions = [
        'acceptee'           => ['en_preparation'],
        'en_preparation'     => ['expediee', 'disponible_retrait'],
        'expediee'           => ['livree'],
        'disponible_retrait' => ['livree'],
        'livree'             => ['terminee'],
    ];

    $autorisees = $transitions[$commande->statut] ?? [];
    if (!in_array($data['statut'], $autorisees)) {
        return back()->with('error', 'Transition non autorisée.');
    }

    

    $commande->update(['statut' => $data['statut']]);

    $libelles = [
        'en_preparation'      => 'en préparation',
        'expediee'            => 'expédiée',
        'disponible_retrait'  => 'disponible pour retrait',
        'livree'              => 'livrée',
       
    ];

    Notification::create([
        'utilisateur_id' => $commande->acheteur_id,
        'code'           => 'N-05',
        'titre'          => 'Commande mise à jour',
        'canal'          => 'web',
        'contenu'        => "Votre commande {$commande->reference} est maintenant {$libelles[$data['statut']]}.",
        'url'            => route('commandes.show', $commande),
        'lu'             => false,
        'created_at'     => now(),
    ]);

    return back()->with('success', 'Statut mis à jour.');
}
}