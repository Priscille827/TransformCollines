<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    protected $table = 'commandes';

    protected $fillable = [
        'reference',
        'acheteur_id',
        'producteur_id',
        'montant_total',
        'statut',
        'statut_paiement',
        'transaction_id',
        'paye_le',
        'confirme_vendeur',
        'confirme_vendeur_le',
        'confirme_acheteur',
        'confirme_acheteur_le',
        'mode_reception',
        'adresse_livraison',
        'notes',
        'motif_annulation',
        'origine',
'disponibilite_id',
    ];

    protected $casts = [
        'montant_total'         => 'decimal:2',
        'paye_le'               => 'datetime',
        'confirme_vendeur'      => 'boolean',
        'confirme_vendeur_le'   => 'datetime',
        'confirme_acheteur'     => 'boolean',
        'confirme_acheteur_le'  => 'datetime',

    ];

    // ─── Relations ───────────────────────────────────────────────
    public function acheteur()
    {
        return $this->belongsTo(Utilisateur::class, 'acheteur_id');
    }

    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function payout()
    {
        return $this->hasOne(Payout::class);
    }

    // ─── Helpers ────────────────────────────────────────────────
    public static function genererReference(): string
    {
        $annee = now()->format('Y');
        $dernier = self::whereYear('created_at', $annee)->count() + 1;
        return sprintf('CMD-%s-%04d', $annee, $dernier);
    }

    public function peutEtreAnnulee(): bool
    {
        return in_array($this->statut, ['en_attente', 'acceptee']);
    }

    public function estPayee(): bool
    {
        return $this->statut_paiement === 'paye';
    }

    public function estTerminee(): bool
    {
        return $this->statut === 'terminee';
    }

    public function peutEtreConfirmee(): bool
    {
        return $this->confirme_vendeur && $this->confirme_acheteur;
    }

    public function disponibilite()
{
    return $this->belongsTo(Disponibilite::class);
}

public function estAchatB2B(): bool
{
    return $this->origine === 'besoin';
}
}