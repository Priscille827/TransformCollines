<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UniteMesure extends Model
{
    protected $table = 'unites_mesure';

    protected $fillable = [
        'code',
        'type',
        'nom',
        'symbole',
        'est_reference',
        'unite_reference_id',
        'actif',
    ];

    protected $casts = [
        'est_reference' => 'boolean',
        'actif'         => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'unite_mesure_id');
    }

    public function offres()
    {
        return $this->hasMany(Offre::class, 'unite_mesure_id');
    }

    public function uniteReference()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_reference_id');
    }

    public function isMesure(): bool
    {
        return $this->type === 'mesure';
    }

    public function isContenant(): bool
    {
        return $this->type === 'contenant';
    }

    public function scopeActives($q)
    {
        return $q->where('actif', true);
    }

    public function scopeMesures($q)
    {
        return $q->where('type', 'mesure');
    }

    public function scopeContenants($q)
    {
        return $q->where('type', 'contenant');
    }
}