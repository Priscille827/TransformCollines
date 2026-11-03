<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Utilisateur extends Authenticatable
{
    use Notifiable;

    protected $table = 'utilisateurs';

    protected $fillable = [
        'nom',
        'email',
        'nom_responsable',
        'telephone',
        'whatsapp',
        'npi',
        'rccm',
        'ifu',
        'document_type',
        'document_chemin',
        'document_verifie',
        'type_compte',
        'commune_id',
        'village',
        'latitude',
        'longitude',
        'statut_compte',
        'statut_validation',
        'motif_refus',
        'valide_par',
        'valide_le',
        'mot_de_passe',
    ];

    protected $hidden = [
        'mot_de_passe',
        'remember_token',
    ];

    protected $casts = [
        'valide_le'        => 'datetime',
        'latitude'         => 'decimal:7',
        'longitude'        => 'decimal:7',
        'document_verifie' => 'boolean',
    ];

    // ─── Auth ────────────────────────────────────────────────
    public function getAuthPassword()
    {
        return $this->mot_de_passe;
    }

    public function getAuthIdentifierName()
    {
        return 'id';
    }

    // ─── Relations ───────────────────────────────────────────
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

    public function panierItems()
    {
        return $this->hasMany(PanierItem::class);
    }

    public function commandes()
    {
        return $this->hasMany(Commande::class, 'acheteur_id');
    }

    public function propositionsProduits()
    {
        return $this->hasMany(PropositionProduit::class, 'propose_par');
    }

    public function validePar()
    {
        return $this->belongsTo(Utilisateur::class, 'valide_par');
    }

    public function valides()
    {
        return $this->hasMany(Utilisateur::class, 'valide_par');
    }

    // ─── Helpers rôle ────────────────────────────────────────
    public function isProducteur(): bool
    {
        return $this->type_compte === 'producteur';
    }

    public function isUnite(): bool
    {
        return $this->type_compte === 'unite_transformation';
    }

    public function isPopulation(): bool
    {
        return $this->type_compte === 'population';
    }

    public function isAdmin(): bool
    {
        return $this->type_compte === 'admin';
    }

    // ─── Helpers statut ──────────────────────────────────────
    public function isEnAttente(): bool
    {
        return $this->statut_validation === 'en_attente';
    }

    public function isValide(): bool
    {
        return $this->statut_validation === 'valide';
    }

    public function isRefuse(): bool
    {
        return $this->statut_validation === 'refuse';
    }

    public function isActif(): bool
    {
        return $this->statut_compte === 'actif' && $this->isValide();
    }

    // ─── Helpers document ────────────────────────────────────
    public function aUnDocument(): bool
    {
        return !empty($this->document_chemin);
    }

    public function documentEstVerifie(): bool
    {
        return $this->document_verifie === true;
    }

    public function dossierEstComplet(): bool
    {
        return $this->aUnDocument() && $this->documentEstVerifie();
    }

    // ─── Helpers affichage ───────────────────────────────────
    public function nomAffiche(): string
    {
        if ($this->isUnite() && $this->nom_responsable) {
            return $this->nom . ' · ' . $this->nom_responsable;
        }
        return $this->nom;
    }

    public function conversations()
{
    return Conversation::where('user1_id', $this->id)
        ->orWhere('user2_id', $this->id);
}

public function messagesEnvoyes()
{
    return $this->hasMany(Message::class, 'expediteur_id');
}

/**
 * Nombre total de messages non lus (toutes conversations).
 */
public function messagesNonLusCount(): int
{
    return Message::whereHas('conversation', function ($q) {
            $q->where('user1_id', $this->id)->orWhere('user2_id', $this->id);
        })
        ->where('expediteur_id', '!=', $this->id)
        ->whereNull('lu_le')
        ->count();
}
}