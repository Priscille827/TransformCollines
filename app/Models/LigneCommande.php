<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LigneCommande extends Model
{
    protected $table = 'ligne_commandes';

    protected $fillable = [
        'commande_id',
        'offre_id',
        'produit_id',
        'unite_mesure_id',
        'quantite',
        'prix_unitaire',
        'sous_total',
    ];

    protected $casts = [
        'quantite'     => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'sous_total'   => 'decimal:2',
    ];

    public function commande()
    {
        return $this->belongsTo(Commande::class);
    }

    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function uniteMesure()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_id');
    }
}