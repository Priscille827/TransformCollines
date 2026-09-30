<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producteur extends Model
{
    protected $table = 'producteurs';
    public $timestamps = true;

    protected $fillable = [
        'utilisateur_id', 'est_cooperative',
        'nombre_membres_approx', 'score_fiabilite',
    ];

    protected $casts = [
        'est_cooperative' => 'boolean',
    ];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function produits()
{
    return $this->belongsToMany(
        Produit::class,
        'producteur_produits',
        'producteur_id',
        'produit_id'
    );
}

    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }
    public function historiqueFiabilite()
{
    return $this->hasMany(HistoriqueFiabilite::class);
}
public function producteur(Producteur $producteur)
{
    return $this->showProducteur($producteur, false);
}
}