<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MouvementStock extends Model
{
    protected $table = 'mouvements_stock';

    protected $fillable = [
        'stock_id',
        'type',
        'quantite',
        'motif',
        'reference_type',
        'reference_id',
        'cree_par',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
    ];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'cree_par');
    }

    /**
     * Relation polymorphe vers la commande, l'offre, etc.
     */
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}