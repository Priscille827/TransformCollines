<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniteTransformation extends Model
{
    protected $table = 'unites_transformation';

    protected $fillable = [
        'utilisateur_id',
        'zone_collecte_rayon_km',
    ];

    protected $casts = [
        'zone_collecte_rayon_km' => 'decimal:2',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    /**
     * Produits BRUTS que l'unité achète (matières premières).
     */
    public function produits()
    {
        return $this->belongsToMany(
            Produit::class,
            'unite_produits',
            'unite_id',
            'produit_id'
        );
    }

    /**
     * Besoins publiés par l'unité.
     */
    public function besoins()
    {
        return $this->hasMany(Besoin::class, 'unite_id');
    }

    /**
     * Offres de l'unité (vente de ses produits finis).
     */
    public function offres()
    {
        return $this->hasMany(Offre::class, 'unite_id');
    }

    /**
     * Stocks de l'unité (produits finis).
     */
    public function stocks()
    {
        return $this->hasMany(Stock::class, 'unite_id');
    }
}