<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\UniteMesure;

class ConversionService
{
    /**
     * Convertit une quantité donnée dans une unité en kg.
     * Si l'unité est déjà kg, retourne la quantité telle quelle.
     * Si c'est un contenant (sac, panier), utilise le poids unitaire.
     */
    public function convertirEnKg(
        UniteMesure $unite,
        float $quantite,
        ?float $poidsUnitaireKg = null
    ): ?float {
        // Unité de référence (kg) → direct
        if ($unite->est_reference) {
            return $quantite;
        }

        // Tonne → 1000 kg
        if ($unite->code === 'tonne') {
            return $quantite * 1000;
        }

        // Contenant → besoin du poids
        if (!$poidsUnitaireKg) {
            return null; // Impossible sans poids
        }

        return $quantite * $poidsUnitaireKg;
    }

    /**
     * Convertit une quantité en kg vers l'unité choisie.
     */
    public function convertirDepuisKg(
        UniteMesure $unite,
        float $kg,
        ?float $poidsUnitaireKg = null
    ): ?float {
        if ($unite->est_reference) {
            return $kg;
        }
        if ($unite->code === 'tonne') {
            return $kg / 1000;
        }
        if (!$poidsUnitaireKg) {
            return null;
        }
        return $kg / $poidsUnitaireKg;
    }

    /**
     * Formate un poids en kg pour affichage.
     */
    public function formaterKg(float $kg): string
    {
        if ($kg >= 1000) {
            return number_format($kg / 1000, 2, ',', ' ') . ' T';
        }
        return number_format($kg, 0, ',', ' ') . ' kg';
    }

    /**
     * Formate une quantité avec son unité.
     * Ex: "10 sacs (500 kg)"
     */
    public function formaterQuantite(
        UniteMesure $unite,
        float $quantite,
        ?float $poidsUnitaireKg = null
    ): string {
        if ($unite->est_reference) {
            return $this->formaterKg($quantite);
        }

        $pluriel = $quantite > 1 ? 's' : '';
        $label = "{$quantite} {$unite->nom}{$pluriel}";

        if ($poidsUnitaireKg) {
            $kg = $quantite * $poidsUnitaireKg;
            $label .= ' (' . $this->formaterKg($kg) . ')';
        }

        return $label;
    }
}