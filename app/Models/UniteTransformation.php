<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniteTransformation extends Model
{
    protected $table = 'unites_transformation';
    public $timestamps = true;

    protected $fillable = [
        'utilisateur_id', 'capacite_traitement_approx',
        'zone_collecte_rayon_km', 'score_fiabilite',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

   public function produits()
{
    return $this->belongsToMany(
        Produit::class,
        'unite_produits',
        'unite_id',     // clé de CE modèle dans la pivot
        'produit_id'    // clé de l'autre modèle
    );
}

    public function besoins()
    {
        return $this->hasMany(Besoin::class);
    }
    public function historiqueFiabilite()
{
    return $this->hasMany(HistoriqueFiabilite::class, 'unite_id');
}
}