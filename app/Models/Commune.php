<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    protected $table = 'communes';
    public $timestamps = true;

    protected $fillable = ['nom'];

    public function utilisateurs()
    {
        return $this->hasMany(Utilisateur::class);
    }

    public function besoins()
{
    return $this->belongsToMany(
        Besoin::class,
        'besoin_communes',
        'commune_id',
        'besoin_id'
    );
}
}