<?php

namespace App\Services;

use App\Models\Besoin;
use App\Models\Disponibilite;
use App\Models\Notification;

class CouvertureService
{
    /**
     * Seuils à surveiller (RG-013).
     */
    private const SEUILS = [25, 50, 100];

    /**
     * Recalcule le taux de couverture d'un besoin et met à jour son statut.
     * RG-004, RG-005, RG-012.
     */
    public function recalculer(Besoin $besoin): void
    {
        // Ancien taux pour comparaison
        $ancienTaux = (float) $besoin->taux_couverture;

        // Somme des disponibilités "associee" + "confirmee"
        $totalCouvert = $besoin->disponibilites()
            ->whereIn('statut', ['associee', 'confirmee'])
            ->sum('quantite');

        $quantite = (float) $besoin->quantite_recherchee;
        $taux = $quantite > 0
            ? min(100, round(($totalCouvert / $quantite) * 100, 2))
            : 0;

        // Statut automatique (RG-004, RG-005)
        $statut = match (true) {
            $taux >= 100                                    => 'couvert',
            $taux > 0                                       => 'partiellement_couvert',
            in_array($besoin->statut, ['cloture', 'expire']) => $besoin->statut,
            default                                         => 'actif',
        };

        // Sauvegarder sans déclencher d'événement (update silencieux)
        $besoin->updateQuietly([
            'taux_couverture' => $taux,
            'statut'          => $statut,
        ]);

        // Détection des franchissements de seuil (RG-013)
        $this->verifierSeuils($besoin, $ancienTaux, $taux);
    }

    /**
     * Envoie une notification N-02 à l'unité pour chaque seuil franchi (RG-013).
     * Un seul envoi par seuil.
     */
    private function verifierSeuils(Besoin $besoin, float $ancienTaux, float $nouveauTaux): void
    {
        // Ne rien faire si le taux baisse ou reste stable
        if ($nouveauTaux <= $ancienTaux) {
            return;
        }

        foreach (self::SEUILS as $seuil) {
            // Le seuil doit avoir été franchi
            if ($ancienTaux < $seuil && $nouveauTaux >= $seuil) {
                // Vérifier qu'on ne l'a pas déjà notifié
                if ($besoin->aNotifieSeuil($seuil)) {
                    continue;
                }

                // Créer la notification N-02
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

                // Marquer le seuil comme notifié
                $besoin->marquerSeuilNotifie($seuil);
            }
        }
    }

    /**
     * Recalcule un besoin depuis une disponibilité (utilitaire).
     */
    public function recalculerDepuisDisponibilite(Disponibilite $dispo): void
    {
        if ($dispo->besoin_id && $dispo->besoin) {
            $this->recalculer($dispo->besoin);
        }
    }
}