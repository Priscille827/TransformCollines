<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    protected $table = 'disponibilites';

    public $timestamps = true;

    protected $fillable = [
        'producteur_id',
        'produit_id',
        'unite_mesure_id',
        'poids_unitaire_kg',
        'unite_reference_id',
        'besoin_id',
        'quantite',
        'quantite_kg_equivalent',
        'quantite_livree',
        'date_disponibilite',
        'statut',
        'date_expiration',
        'prix_b2b',
    ];

    protected $casts = [
        'quantite'               => 'decimal:2',
        'poids_unitaire_kg'      => 'decimal:2',
        'quantite_kg_equivalent' => 'decimal:2',
        'quantite_livree'        => 'decimal:2',
        'date_disponibilite'     => 'date',
        'date_expiration'        => 'date',
        'prix_b2b' => 'decimal:2',
    ];

    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
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

    public function besoin()
    {
        return $this->belongsTo(Besoin::class);
    }

    public function getQuantiteFormateeAttribute(): string
    {
        if (!$this->uniteMesure || $this->uniteMesure->est_reference) {
            return number_format($this->quantite, 0, ',', ' ') . ' kg';
        }

        $uniteNom = $this->uniteMesure->nom;
        $qte = $this->quantite;
        $pluriel = $qte > 1 ? 's' : '';

        if ($this->quantite_kg_equivalent && $this->uniteReference) {
            return "{$qte} {$uniteNom}{$pluriel} (" .
                number_format($this->quantite_kg_equivalent, 0, ',', ' ') . ' ' .
                $this->uniteReference->symbole . ')';
        }

        return "{$qte} {$uniteNom}{$pluriel}";
    }

    public function scopeLibres($q)
    {
        return $q->whereNull('besoin_id')->where('statut', 'declaree');
    }

    public function scopeActives($q)
    {
        return $q->whereIn('statut', ['declaree', 'associee']);
    }

    /**
 * Prix B2B appliqué pour cette proposition.
 */
public function prixB2BApplique(): float
{
    if ($this->prix_b2b !== null && (float) $this->prix_b2b > 0) {
        return (float) $this->prix_b2b;
    }

    // Fallback : chercher dans le stock du producteur
    $stock = \App\Models\Stock::where('producteur_id', $this->producteur_id)
        ->where('produit_id', $this->produit_id)
        ->where('unite_mesure_id', $this->unite_mesure_id)
        ->first();

    return $stock?->prixB2BApplique() ?? 0;
}
}