<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producteur extends Model
{
    protected $table = 'producteurs';

    protected $fillable = ['utilisateur_id'];

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

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'producteur_id');
    }
}