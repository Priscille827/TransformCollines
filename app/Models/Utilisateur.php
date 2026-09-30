<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    protected $table = 'utilisateurs';
    public $timestamps = true;

    protected $fillable = [
        'nom', 'telephone', 'type_compte', 'commune_id',
        'village', 'statut_compte', 'mot_de_passe',
    ];

    protected $hidden = ['mot_de_passe'];

    protected $casts = [
        'statut_compte' => 'string',
    ];

    

    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    public function producteur()
    {
        return $this->hasOne(Producteur::class);
    }

    public function unite()
    {
        return $this->hasOne(UniteTransformation::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function isProducteur(): bool
    {
        return $this->type_compte === 'producteur';
    }

    public function isUnite(): bool
    {
        return $this->type_compte === 'unite_transformation';
    }

    public function isInstitution(): bool
    {
        return $this->type_compte === 'institution';
    }
    public function getAuthPassword()
{
    return $this->mot_de_passe;
}

public function getAuthIdentifierName()
{
    return 'telephone';
}
}