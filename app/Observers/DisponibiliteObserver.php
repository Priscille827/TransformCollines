<?php

namespace App\Observers;

use App\Models\Disponibilite;
use App\Services\CouvertureService;

class DisponibiliteObserver
{
    public function __construct(
        private CouvertureService $couverture
    ) {}

    /**
     * Après création d'une disponibilité.
     */
    public function created(Disponibilite $dispo): void
    {
        $this->couverture->recalculerDepuisDisponibilite($dispo);
    }

    /**
     * Après modification.
     */
    public function updated(Disponibilite $dispo): void
    {
        // Recalcul sur le besoin actuel
        $this->couverture->recalculerDepuisDisponibilite($dispo);

        // Si le besoin_id a changé, recalculer aussi l'ancien
        if ($dispo->wasChanged('besoin_id')) {
            $ancienId = $dispo->getOriginal('besoin_id');
            if ($ancienId) {
                $ancienBesoin = \App\Models\Besoin::find($ancienId);
                if ($ancienBesoin) {
                    $this->couverture->recalculer($ancienBesoin);
                }
            }
        }
    }

    /**
     * Après suppression.
     */
    public function deleted(Disponibilite $dispo): void
    {
        $this->couverture->recalculerDepuisDisponibilite($dispo);
    }
}