<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disponibilite extends Model
{
    protected $table = 'disponibilites';
    public $timestamps = true;

    protected $fillable = [
        'producteur_id', 'produit_id', 'besoin_id',
        'quantite', 'quantite_livree', 'date_disponibilite',
        'statut', 'date_expiration',
    ];

    protected $casts = [
        'date_disponibilite' => 'date',
        'date_expiration' => 'date',
    ];

    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }
    public function notation()
{
    return $this->hasOne(Notation::class);
}

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function besoin()
    {
        return $this->belongsTo(Besoin::class);
    }

    public function scopeLibres($q)
    {
        return $q->whereNull('besoin_id')->where('statut', 'declaree');
    }
}