<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanierItem extends Model
{
    protected $table = 'panier_items';

    protected $fillable = ['utilisateur_id', 'offre_id', 'quantite'];

    protected $casts = ['quantite' => 'decimal:2'];

    public function utilisateur()
    {
        return $this->belongsTo(Utilisateur::class);
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }

    public function sousTotal(): float
    {
        return (float) $this->quantite * $this->offre->prixPourQuantite((float) $this->quantite);
    }
}