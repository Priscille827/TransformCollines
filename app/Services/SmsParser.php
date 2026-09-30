<?php

namespace App\Services;

use App\Models\Commune;
use App\Models\Produit;

class SmsParser
{
    /**
     * Format attendu : "MANIOC 500 SAVALOU"
     * Retourne un tableau ou null si le format est invalide.
     */
    public function parse(string $message): ?array
    {
        // Normaliser : majuscules, espaces multiples en 1 seul
        $message = strtoupper(trim(preg_replace('/\s+/', ' ', $message)));
        $parts = explode(' ', $message);

        // Il faut au minimum 3 mots
        if (count($parts) < 3) {
            return null;
        }

        // 1er mot : produit
        $produitNom = $parts[0];
        $produit = Produit::whereRaw('UPPER(nom) = ?', [$produitNom])->first();
        if (!$produit) {
            return null;
        }

        // 2e mot : quantité
        $quantite = $parts[1];
        if (!is_numeric($quantite) || $quantite <= 0) {
            return null;
        }

        // 3e mot : commune
        $communeNom = $parts[2];
        $commune = Commune::whereRaw('UPPER(nom) = ?', [$communeNom])->first();
        if (!$commune) {
            return null;
        }

        return [
            'produit_id' => $produit->id,
            'produit_nom' => $produit->nom,
            'quantite'    => (float) $quantite,
            'commune_id'  => $commune->id,
            'commune_nom' => $commune->nom,
        ];
    }

    /**
     * Message d'aide en cas de format non reconnu.
     */
    public function aideFormat(): string
    {
        $produits = Produit::pluck('nom')->map(fn($n) => strtoupper($n))->join(', ');
        $communes = Commune::pluck('nom')->map(fn($n) => strtoupper($n))->join(', ');

        return "Format invalide. Envoyez : PRODUIT QUANTITE COMMUNE\n"
            . "Ex: MANIOC 500 SAVALOU\n"
            . "Produits: {$produits}\n"
            . "Communes: {$communes}";
    }
}