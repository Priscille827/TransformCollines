<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignalementSms extends Model
{
    protected $table = 'signalements_sms';
    public $timestamps = false;

    protected $fillable = [
        'telephone', 'message_brut', 'produit_id', 'quantite',
        'commune_id', 'disponibilite_id', 'statut_traitement', 'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'quantite'   => 'decimal:2',
    ];

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function disponibilite()
    {
        return $this->belongsTo(Disponibilite::class);
    }
}