<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $table = 'stocks';

    protected $fillable = [
        'producteur_id',
        'unite_id',
        'produit_id',
        'unite_mesure_id',
        'poids_unitaire_kg',
        'unite_reference_id',
        'quantite_disponible',
        'quantite_reservee',
        'seuil_alerte',
        'prix_unitaire',
        'prix_b2b',
    ];

    protected $casts = [
        'quantite_disponible' => 'decimal:2',
        'quantite_reservee'   => 'decimal:2',
        'poids_unitaire_kg'   => 'decimal:2',
        'seuil_alerte'        => 'decimal:2',
        'prix_unitaire'       => 'decimal:2',
        'prix_b2b' => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }

    public function unite()
    {
        return $this->belongsTo(UniteTransformation::class, 'unite_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function uniteMesure()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_id');
    }

    public function uniteReference()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_reference_id');
    }

    public function mouvements()
    {
        return $this->hasMany(MouvementStock::class);
    }

    // ─── Helpers détenteur ────────────────────────────────────
    public function estDetenuParProducteur(): bool
    {
        return $this->producteur_id !== null;
    }

    public function estDetenuParUnite(): bool
    {
        return $this->unite_id !== null;
    }

    public function detenteur()
    {
        return $this->estDetenuParUnite() ? $this->unite : $this->producteur;
    }

    // ─── Helpers quantités ────────────────────────────────────
    public function getQuantiteVendableAttribute(): float
    {
        return max(0, (float) $this->quantite_disponible - (float) $this->quantite_reservee);
    }

    /**
     * Quantité totale dans l'unité de référence (kg, g, L, ml).
     */
    public function getQuantiteKgAttribute(): float
    {
        if ($this->uniteMesure && $this->uniteMesure->est_reference) {
            return (float) $this->quantite_disponible;
        }
        if ($this->poids_unitaire_kg) {
            return (float) $this->quantite_disponible * (float) $this->poids_unitaire_kg;
        }
        return (float) $this->quantite_disponible;
    }

    public function getQuantiteVendableKgAttribute(): float
    {
        if ($this->uniteMesure && $this->uniteMesure->est_reference) {
            return $this->quantite_vendable;
        }
        if ($this->poids_unitaire_kg) {
            return $this->quantite_vendable * (float) $this->poids_unitaire_kg;
        }
        return $this->quantite_vendable;
    }

    public function convertirEnKg(float $quantite): float
    {
        if ($this->uniteMesure && $this->uniteMesure->est_reference) {
            return $quantite;
        }
        if ($this->poids_unitaire_kg) {
            return $quantite * (float) $this->poids_unitaire_kg;
        }
        return $quantite;
    }

    public function getQuantiteFormateeAttribute(): string
    {
        $qte = number_format($this->quantite_disponible, 0, ',', ' ');
        $uniteNom = $this->uniteMesure?->nom ?? 'unité';
        $pluriel = $this->quantite_disponible > 1 ? 's' : '';

        if ($this->uniteMesure && $this->uniteMesure->est_reference) {
            return "{$qte} {$this->uniteMesure->symbole}";
        }

        $base = "{$qte} {$uniteNom}{$pluriel}";
        if ($this->poids_unitaire_kg && $this->uniteReference) {
            $base .= ' (' . number_format($this->quantite_kg, 0, ',', ' ') . ' ' . $this->uniteReference->symbole . ')';
        }
        return $base;
    }

    public function isEnAlerte(): bool
    {
        return $this->seuil_alerte !== null
            && $this->quantite_vendable <= (float) $this->seuil_alerte;
    }

    public function scopeDisponibles($q)
    {
        return $q->whereRaw('quantite_disponible - quantite_reservee > 0');
    }

    public function scopePourProducteur($q, $producteurId)
    {
        return $q->where('producteur_id', $producteurId);
    }

    public function scopePourUnite($q, $uniteId)
    {
        return $q->where('unite_id', $uniteId);
    }

    /**
 * Prix B2B appliqué (avec fallback sur prix détail).
 */
public function prixB2BApplique(): float
{
    if ($this->prix_b2b !== null && (float) $this->prix_b2b > 0) {
        return (float) $this->prix_b2b;
    }
    return (float) ($this->prix_unitaire ?? 0);
}

/**
 * Vérifie si le prix B2B est défini.
 */
public function aUnPrixB2B(): bool
{
    return $this->prix_b2b !== null && (float) $this->prix_b2b > 0;
}
}