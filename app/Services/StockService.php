<?php

namespace App\Services;

use App\Models\MouvementStock;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * Réserve une quantité sur un stock (pour une dispo ou une offre).
     */
    public function reserver(Stock $stock, float $quantite): bool
    {
        $disponible = (float) $stock->quantite_disponible - (float) $stock->quantite_reservee;

        if ($quantite > $disponible) {
            return false;
        }

        $stock->increment('quantite_reservee', $quantite);

        return true;
    }

    /**
     * Libère une réservation (annulation).
     */
    public function liberer(Stock $stock, float $quantite): void
    {
        $nouvelleReservee = max(0, (float) $stock->quantite_reservee - $quantite);
        $stock->update(['quantite_reservee' => $nouvelleReservee]);
    }

    /**
     * Confirme la livraison : la quantité réservée est retirée du stock définitif.
     * Peut être liée à une commande ou à un besoin via reference_type/reference_id.
     */
    public function confirmerLivraison(
        Stock $stock,
        float $quantite,
        ?string $motif = null,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): void {
        DB::beginTransaction();
        try {
            $stock->update([
                'quantite_disponible' => max(0, (float) $stock->quantite_disponible - $quantite),
                'quantite_reservee'   => max(0, (float) $stock->quantite_reservee - $quantite),
            ]);

            MouvementStock::create([
                'stock_id'       => $stock->id,
                'type'           => 'sortie',
                'quantite'       => -$quantite,
                'motif'          => $motif ?? 'Livraison confirmée',
                'reference_type' => $referenceType,
                'reference_id'   => $referenceId,
                'cree_par'       => $userId,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Enregistre une entrée de stock (récolte, achat...).
     */
    public function entrer(Stock $stock, float $quantite, ?string $motif = null, ?int $userId = null): void
    {
        DB::beginTransaction();
        try {
            $stock->increment('quantite_disponible', $quantite);

            MouvementStock::create([
                'stock_id'   => $stock->id,
                'type'       => 'entree',
                'quantite'   => $quantite,
                'motif'      => $motif ?? 'Entrée de stock',
                'cree_par'   => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sortie manuelle (perte, vente directe...).
     */
    public function sortir(Stock $stock, float $quantite, ?string $motif = null, ?int $userId = null): bool
    {
        $disponible = (float) $stock->quantite_disponible - (float) $stock->quantite_reservee;

        if ($quantite > $disponible) {
            return false;
        }

        DB::beginTransaction();
        try {
            $stock->decrement('quantite_disponible', $quantite);

            MouvementStock::create([
                'stock_id'   => $stock->id,
                'type'       => 'sortie',
                'quantite'   => -$quantite,
                'motif'      => $motif ?? 'Sortie de stock',
                'cree_par'   => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}