<?php

namespace App\Services;

use App\Models\Besoin;
use App\Models\Disponibilite;
use App\Models\Notification;

class CouvertureService
{
    private const SEUILS = [25, 50, 100];

    /**
     * Recalcule le taux de couverture d'un besoin et met à jour son statut.
     * RG-004, RG-005, RG-012.
     */
    public function recalculer(Besoin $besoin): void
    {
        $ancienTaux = (float) $besoin->taux_couverture;

        // ✅ IMPORTANT : utiliser quantite_kg_equivalent, PAS quantite
        $totalCouvert = (float) $besoin->disponibilites()
            ->whereIn('statut', ['associee', 'confirmee'])
            ->sum('quantite_kg_equivalent');

        $quantite = (float) $besoin->quantite_recherchee;

        $taux = $quantite > 0
            ? min(100, round(($totalCouvert / $quantite) * 100, 2))
            : 0;

        // Statut automatique
        $statut = match (true) {
            $taux >= 100                                     => 'couvert',
            $taux > 0                                        => 'partiellement_couvert',
            in_array($besoin->statut, ['cloture', 'expire']) => $besoin->statut,
            default                                          => 'actif',
        };

        $besoin->updateQuietly([
            'taux_couverture' => $taux,
            'statut'          => $statut,
        ]);

        // Détection des franchissements de seuil
        $this->verifierSeuils($besoin, $ancienTaux, $taux);
    }

    /**
     * Envoie une notification N-02 à l'unité pour chaque seuil franchi.
     */
    private function verifierSeuils(Besoin $besoin, float $ancienTaux, float $nouveauTaux): void
    {
        if ($nouveauTaux <= $ancienTaux) {
            return;
        }

        foreach (self::SEUILS as $seuil) {
            if ($ancienTaux < $seuil && $nouveauTaux >= $seuil) {
                if ($besoin->aNotifieSeuil($seuil)) {
                    continue;
                }

                Notification::create([
                    'utilisateur_id' => $besoin->unite->utilisateur_id,
                    'code'           => 'N-02',
                    'canal'          => 'web',
                    'contenu'        => sprintf(
                        'Votre besoin en %s est couvert à %d %%.',
                        $besoin->produit->nom,
                        $seuil
                    ),
                    'lu'             => false,
                    'created_at'     => now(),
                ]);

                $besoin->marquerSeuilNotifie($seuil);
            }
        }
    }

    /**
     * Recalcule un besoin depuis une disponibilité.
     */
    public function recalculerDepuisDisponibilite(Disponibilite $dispo): void
    {
        if ($dispo->besoin_id && $dispo->besoin) {
            $this->recalculer($dispo->besoin);
        }
    }
}