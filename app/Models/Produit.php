<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table = 'produits';
    public $timestamps = true;

    protected $fillable = ['nom'];

    public function producteurs()
{
    return $this->belongsToMany(
        Producteur::class,
        'producteur_produits',
        'produit_id',
        'producteur_id'
    );
}

   public function unites()
{
    return $this->belongsToMany(
        UniteTransformation::class,
        'unite_produits',
        'produit_id',   // clé de CE modèle dans la pivot
        'unite_id'      // clé de l'autre modèle dans la pivot
    );
}

    public function besoins()
    {
        return $this->hasMany(Besoin::class);
    }

    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }
}