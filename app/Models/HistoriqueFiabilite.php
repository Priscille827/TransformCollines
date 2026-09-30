<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueFiabilite extends Model
{
    protected $table = 'historique_fiabilite';
    public $timestamps = false;

    protected $fillable = [
        'type_acteur',
        'producteur_id',
        'unite_id',
        'disponibilite_id',
        'type_evenement',
        'resultat',
        'commentaire',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relations
    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }

    public function unite()
    {
        return $this->belongsTo(UniteTransformation::class, 'unite_id');
    }

    public function disponibilite()
    {
        return $this->belongsTo(Disponibilite::class);
    }
}