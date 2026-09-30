<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notation extends Model
{
    protected $table = 'notations';
    public $timestamps = false;

    protected $fillable = [
        'disponibilite_id',
        'note_qualite',
        'note_paiement',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function disponibilite()
    {
        return $this->belongsTo(Disponibilite::class);
    }
}