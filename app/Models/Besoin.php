<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Besoin extends Model
{
    protected $table = 'besoins';
    public $timestamps = true;

    protected $fillable = [
        'unite_id', 'produit_id', 'quantite_recherchee',
        'delai', 'taux_couverture', 'seuils_notifies', 'statut',
    ];

    protected $casts = [
        'delai' => 'date',
        'taux_couverture' => 'decimal:2',
    ];

    public function unite()
    {
        return $this->belongsTo(UniteTransformation::class, 'unite_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function communes()
{
    return $this->belongsToMany(
        Commune::class,
        'besoin_communes',
        'besoin_id',
        'commune_id'
    );
}
    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }

    // Scopes utiles
    public function scopeActifs($q)
    {
        return $q->whereIn('statut', ['actif', 'partiellement_couvert']);
    }

    /**
 * Liste des seuils déjà notifiés : [25, 50] par exemple.
 */
public function seuilsNotifies(): array
{
    if (!$this->seuils_notifies) {
        return [];
    }
    return array_map('intval', explode(',', $this->seuils_notifies));
}

/**
 * Vérifie si un seuil a déjà été notifié.
 */
public function aNotifieSeuil(int $seuil): bool
{
    return in_array($seuil, $this->seuilsNotifies());
}

/**
 * Marque un seuil comme notifié.
 */
public function marquerSeuilNotifie(int $seuil): void
{
    $seuils = $this->seuilsNotifies();
    if (!in_array($seuil, $seuils)) {
        $seuils[] = $seuil;
        sort($seuils);
        $this->update(['seuils_notifies' => implode(',', $seuils)]);
    }
}
}