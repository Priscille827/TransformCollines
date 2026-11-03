<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\LigneCommande;
use App\Models\Notification;
use App\Models\PanierItem;
use App\Services\KkiapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommandeController extends Controller
{
    public function __construct(private KkiapayService $kkiapay) {}

    /**
     * Page de validation du panier.
     */
    public function checkout()
    {
        $user = Auth::user();

        $items = PanierItem::with([
                'offre.produit',
                'offre.uniteMesure',
                'offre.producteur.utilisateur',
                'offre.unite.utilisateur',
            ])
            ->where('utilisateur_id', $user->id)
            ->get()
            ->filter(fn($item) => $item->offre !== null && $item->offre->statut === 'active');

        if ($items->isEmpty()) {
            return redirect()->route('panier.index')
                ->with('error', 'Votre panier est vide ou contient des offres expirées.');
        }

        $total = $items->sum(fn($i) => $i->sousTotal());

        $parVendeur = $items->groupBy(function ($item) {
            if ($item->offre->producteur_id) {
                return 'producteur_' . $item->offre->producteur_id;
            }
            return 'unite_' . ($item->offre->unite_id ?? 'inconnu');
        });

        return view('commandes.checkout', compact('items', 'total', 'parVendeur'));
    }

    /**
     * Valider et créer les commandes.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'mode_reception'    => 'required|in:retrait,livraison',
            'adresse_livraison' => 'required_if:mode_reception,livraison|nullable|string|max:500',
            'notes'             => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();

        $items = PanierItem::with(['offre'])->where('utilisateur_id', $user->id)->get();

        if ($items->isEmpty()) {
            return redirect()->route('panier.index')->with('error', 'Votre panier est vide.');
        }

        DB::beginTransaction();
        try {
            $parVendeur = $items->groupBy(function ($item) {
                return $item->offre->producteur_id
                    ? 'p_' . $item->offre->producteur_id
                    : 'u_' . $item->offre->unite_id;
            });

            $commandesCreees = [];

            foreach ($parVendeur as $itemsVendeur) {
                $premiereOffre = $itemsVendeur->first()->offre;
                $producteurId = $premiereOffre->producteur_id;

                foreach ($itemsVendeur as $item) {
                    if ($item->quantite > $item->offre->quantite_disponible) {
                        throw new \Exception("Stock insuffisant pour « {$item->offre->titre} ».");
                    }
                }

                $commande = Commande::create([
                    'reference'         => Commande::genererReference(),
                    'acheteur_id'       => $user->id,
                    'producteur_id'     => $producteurId,
                    'montant_total'     => 0,
                    'statut'            => 'en_attente',
                    'statut_paiement'   => 'en_attente',
                    'mode_reception'    => $data['mode_reception'],
                    'adresse_livraison' => $data['adresse_livraison'] ?? null,
                    'notes'             => $data['notes'] ?? null,
                ]);

                $montantTotal = 0;

                foreach ($itemsVendeur as $item) {
                    $prixUnitaire = $item->offre->prixPourQuantite((float) $item->quantite);
                    $sousTotal = $prixUnitaire * (float) $item->quantite;

                    LigneCommande::create([
                        'commande_id'     => $commande->id,
                        'offre_id'        => $item->offre->id,
                        'produit_id'      => $item->offre->produit_id,
                        'unite_mesure_id' => $item->offre->unite_mesure_id,
                        'quantite'        => $item->quantite,
                        'prix_unitaire'   => $prixUnitaire,
                        'sous_total'      => $sousTotal,
                    ]);

                    $montantTotal += $sousTotal;
                }

                $commande->update(['montant_total' => $montantTotal]);

                $vendeur = $premiereOffre->producteur?->utilisateur ?? $premiereOffre->unite?->utilisateur;
                if ($vendeur) {
                    Notification::create([
                        'utilisateur_id' => $vendeur->id,
                        'code'           => 'N-05',
                        'titre'          => 'Nouvelle commande',
                        'canal'          => 'web',
                        'contenu'        => "Nouvelle commande {$commande->reference} de {$user->nom}",
                        'url'            => route('commandes.recues.show', $commande),
                        'lu'             => false,
                        'created_at'     => now(),
                    ]);
                }

                $commandesCreees[] = $commande;
            }

            PanierItem::where('utilisateur_id', $user->id)->delete();

            DB::commit();

            $count = count($commandesCreees);
            $message = $count === 1
                ? "Commande {$commandesCreees[0]->reference} envoyée. En attente d'acceptation par le vendeur."
                : "{$count} commandes envoyées aux vendeurs. En attente d'acceptation.";

            return redirect()->route('commandes.index')->with('success', $message);

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Liste des commandes (acheteur).
     */
    public function index()
    {
        $commandes = Commande::with(['lignes.produit', 'lignes.uniteMesure', 'producteur.utilisateur'])
            ->where('acheteur_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('commandes.index', compact('commandes'));
    }

    /**
     * Détail d'une commande.
     */
    public function show(Commande $commande)
    {
        $user = Auth::user();

        $estAcheteur = $commande->acheteur_id === $user->id;
        $estVendeur = false;

        if ($user->isProducteur() && $commande->producteur_id === $user->producteur->id) {
            $estVendeur = true;
        }
        if ($user->isUnite()) {
            $offreIds = $commande->lignes()->pluck('offre_id');
            $estVendeur = \App\Models\Offre::whereIn('id', $offreIds)
                ->where('unite_id', $user->unite->id)
                ->exists();
        }

        if (!$estAcheteur && !$estVendeur && !$user->isAdmin()) {
            abort(403);
        }

        $commande->load([
            'lignes.produit',
            'lignes.uniteMesure',
            'lignes.offre',
            'acheteur.commune',
            'producteur.utilisateur.commune',
        ]);

        return view('commandes.show', compact('commande', 'estAcheteur', 'estVendeur'));
    }

    /**
     * Annuler une commande.
     */
    public function annuler(Request $request, Commande $commande)
    {
        $user = Auth::user();

        $estAcheteur = $commande->acheteur_id === $user->id;
        $estVendeur = $user->isProducteur() && $commande->producteur_id === $user->producteur->id;

        if (!$estAcheteur && !$estVendeur && !$user->isAdmin()) {
            abort(403);
        }

        if (!$commande->peutEtreAnnulee()) {
            return back()->with('error', 'Cette commande ne peut plus être annulée.');
        }

        $data = $request->validate([
            'motif_annulation' => 'required|string|max:500',
        ]);

        // Si déjà payée, rembourser via Kkiapay
        $rembourse = false;
        if ($commande->statut_paiement === 'paye' && $commande->transaction_id) {
            $rembourse = $this->kkiapay->rembourser($commande->transaction_id);
        }

        $commande->update([
            'statut'           => 'annulee',
            'motif_annulation' => $data['motif_annulation'],
            'statut_paiement'  => $rembourse ? 'rembourse' : $commande->statut_paiement,
        ]);

        $destinataire = $estAcheteur
            ? $commande->producteur->utilisateur
            : $commande->acheteur;

        Notification::create([
            'utilisateur_id' => $destinataire->id,
            'code'           => 'N-05',
            'titre'          => 'Commande annulée',
            'canal'          => 'web',
            'contenu'        => "La commande {$commande->reference} a été annulée. Motif : {$data['motif_annulation']}" . ($rembourse ? ' (Remboursement effectué)' : ''),
            'url'            => route('commandes.show', $commande),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return back()->with('success', 'Commande annulée.' . ($rembourse ? ' Remboursement effectué.' : ''));
    }

    /**
     * L'acheteur confirme avoir reçu.
     */
    public function confirmerReception(Commande $commande)
    {
        $user = Auth::user();

        if ($commande->acheteur_id !== $user->id) {
            abort(403);
        }

        if ($commande->statut_paiement !== 'paye') {
            return back()->with('error', 'Le paiement n\'a pas encore été effectué.');
        }

        if ($commande->confirme_acheteur) {
            return back()->with('info', 'Vous avez déjà confirmé la réception.');
        }

        $commande->update([
            'confirme_acheteur'    => true,
            'confirme_acheteur_le' => now(),
        ]);

        // Vérifier si les 2 ont confirmé (appel du contrôleur vendeur)
        app(\App\Http\Controllers\CommandesRecuesController::class)->verifierFinalisation($commande);

        return back()->with('success', 'Réception confirmée. Merci !');
    }
}