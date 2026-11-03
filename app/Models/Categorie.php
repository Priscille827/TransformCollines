<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    protected $table = 'categories';

    protected $fillable = ['code', 'nom', 'icone', 'ordre', 'actif'];

    protected $casts = ['actif' => 'boolean'];

    public function produits()
    {
        return $this->hasMany(Produit::class);
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }

    public function scopeActives($q)
    {
        return $q->where('actif', true)->orderBy('ordre');
    }
}