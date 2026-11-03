<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropositionProduit extends Model
{
    protected $table = 'propositions_produits';

    protected $fillable = [
        'nom_saisi',
        'type_produit',
        'propose_par',
        'propose_par_role',
        'statut',
        'nom_corrige',
        'produit_id',
        'motif_refus',
        'traite_par',
        'traite_le',
    ];

    protected $casts = [
        'traite_le' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────
    public function proposeur()
    {
        return $this->belongsTo(Utilisateur::class, 'propose_par');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function traitePar()
    {
        return $this->belongsTo(Utilisateur::class, 'traite_par');
    }

    // ─── Helpers ──────────────────────────────────────────────
    public function estEnAttente(): bool
    {
        return $this->statut === 'a_verifier';
    }

    public function estValidee(): bool
    {
        return $this->statut === 'valide';
    }

    public function estRefusee(): bool
    {
        return $this->statut === 'refuse';
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeAverifier($q)
    {
        return $q->where('statut', 'a_verifier');
    }

    public function scopeValidees($q)
    {
        return $q->where('statut', 'valide');
    }

    public function scopeRefusees($q)
    {
        return $q->where('statut', 'refuse');
    }

    public function scopePourProducteur($q)
    {
        return $q->where('propose_par_role', 'producteur');
    }

    public function scopePourUnite($q)
    {
        return $q->where('propose_par_role', 'unite_transformation');
    }
}