<?php

namespace App\Services;

use App\Models\HistoriqueFiabilite;
use App\Models\Producteur;
use App\Models\UniteTransformation;

class FiabiliteService
{
    /**
     * Recalcule le score de fiabilité d'un producteur (RG-016, RG-018).
     */
    public function recalculerProducteur(Producteur $producteur): void
    {
        $historique = HistoriqueFiabilite::where('type_acteur', 'producteur')
            ->where('producteur_id', $producteur->id)
            ->where('type_evenement', 'livraison')
            ->get();

        // Pas assez de données → "nouveau"
        if ($historique->count() < 3) {
            $producteur->update(['score_fiabilite' => 'nouveau']);
            return;
        }

        $tenus = $historique->where('resultat', 'tenu')->count();
        $total = $historique->count();
        $taux  = ($tenus / $total) * 100;

        $score = match (true) {
            $taux >= 80 => 'fiable',
            $taux >= 50 => 'a_surveiller',
            default     => 'a_surveiller',
        };

        $producteur->update(['score_fiabilite' => $score]);
    }

    /**
     * Recalcule le score de fiabilité d'une unité (RG-017, RG-018).
     */
    public function recalculerUnite(UniteTransformation $unite): void
    {
        $historique = HistoriqueFiabilite::where('type_acteur', 'unite_transformation')
            ->where('unite_id', $unite->id)
            ->where('type_evenement', 'paiement')
            ->get();

        if ($historique->count() < 3) {
            $unite->update(['score_fiabilite' => 'nouveau']);
            return;
        }

        $aTemps = $historique->where('resultat', 'a_temps')->count();
        $total  = $historique->count();
        $taux   = ($aTemps / $total) * 100;

        $score = match (true) {
            $taux >= 80 => 'fiable',
            $taux >= 50 => 'a_surveiller',
            default     => 'a_surveiller',
        };

        $unite->update(['score_fiabilite' => $score]);
    }
}