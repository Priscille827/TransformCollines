<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\Payout;
use Illuminate\Support\Facades\DB;

class PayoutService
{
    /**
     * Enregistre une commande terminée dans le payout du vendeur pour la semaine.
     * Crée le payout s'il n'existe pas, sinon le met à jour.
     */
    public function enregistrerCommande(Commande $commande): ?Payout
    {
        if ($commande->statut !== 'terminee' || $commande->statut_paiement !== 'paye') {
            return null;
        }

        // Déterminer le vendeur
        $producteurId = $commande->producteur_id;
        $uniteId = null;

        if (!$producteurId) {
            $offre = $commande->lignes()->first()?->offre;
            if ($offre && $offre->unite_id) {
                $uniteId = $offre->unite_id;
            }
        }

        if (!$producteurId && !$uniteId) {
            return null;
        }

        // Calculer la période (semaine en cours)
        $periodeDebut = now()->startOfWeek()->toDateString();
        $periodeFin   = now()->endOfWeek()->toDateString();

        DB::beginTransaction();
        try {
            // ✅ lockForUpdate() pour éviter les races entre cron et web
            $payout = Payout::where('periode_debut', $periodeDebut)
                ->where('periode_fin', $periodeFin)
                ->where('statut', 'en_attente')
                ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
                ->when($uniteId, fn($q) => $q->where('unite_id', $uniteId))
                ->lockForUpdate()
                ->first();

            // ✅ Cast 'array' garantit un tableau PHP (plus de NULL trompeur)
            $commandesIds = $payout?->commandes_ids ?? [];

            // Éviter les doublons
            if (in_array($commande->id, $commandesIds)) {
                DB::commit();
                return $payout;
            }

            $commandesIds[] = $commande->id;

            if ($payout) {
                // Mettre à jour le payout existant
                $nouveauMontant    = (float) $payout->montant + (float) $commande->montant_total;
                $nouvelleCommission = $nouveauMontant * 0.02;
                $nouveauNet        = $nouveauMontant - $nouvelleCommission;

                $payout->update([
                    'montant'       => $nouveauMontant,
                    'commission'    => $nouvelleCommission,
                    'montant_net'   => $nouveauNet,
                    'commandes_ids' => $commandesIds,
                ]);
            } else {
                // Créer un nouveau payout
                $montant     = (float) $commande->montant_total;
                $commission  = $montant * 0.02;
                $net         = $montant - $commission;

                $payout = Payout::create([
                    'producteur_id' => $producteurId,
                    'unite_id'      => $uniteId,
                    'montant'       => $montant,
                    'commission'    => $commission,
                    'montant_net'   => $net,
                    'commandes_ids' => $commandesIds,
                    'periode_debut' => $periodeDebut,
                    'periode_fin'   => $periodeFin,
                    'statut'        => 'en_attente',
                ]);
            }

            DB::commit();
            return $payout;

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Erreur PayoutService: ' . $e->getMessage());
            return null;
        }
    }
}