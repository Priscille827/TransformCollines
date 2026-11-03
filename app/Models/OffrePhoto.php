<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OffrePhoto extends Model
{
    protected $table = 'offre_photos';

    protected $fillable = [
        'offre_id',
        'chemin',
        'ordre',
    ];

    public function offre()
    {
        return $this->belongsTo(Offre::class);
    }
}