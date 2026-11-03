<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    protected $table = 'payouts';

    protected $fillable = [
        'producteur_id',
        'unite_id',
        'montant',
        'commission',
        'montant_net',
        'commandes_ids',        // ✅ Ajouté
        'periode_debut',
        'periode_fin',
        'statut',
        'reference_kkiapay',
        'paye_le',
        'confirme_vendeur',
        'confirme_vendeur_le',
        'marque_par_admin',
        'marque_par_admin_le',
    ];

    protected $casts = [
        'montant'               => 'decimal:2',
        'commission'            => 'decimal:2',
        'montant_net'           => 'decimal:2',
        'commandes_ids'         => 'array',   // ✅ Cast JSON <-> array
        'periode_debut'         => 'date',
        'periode_fin'           => 'date',
        'paye_le'               => 'datetime',
        'confirme_vendeur'      => 'boolean',
        'confirme_vendeur_le'   => 'datetime',
        'marque_par_admin'      => 'boolean',
        'marque_par_admin_le'   => 'datetime',
    ];

    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }

    public function unite()
    {
        return $this->belongsTo(UniteTransformation::class, 'unite_id');
    }

    // ─── Helpers ────────────────────────────────────────────
    public function vendeurNom(): string
    {
        if ($this->producteur_id) {
            return $this->producteur?->utilisateur?->nom ?? '—';
        }
        return $this->unite?->utilisateur?->nom ?? '—';
    }

    public function estTermine(): bool
    {
        return $this->confirme_vendeur && $this->marque_par_admin;
    }

    /**
     * Nombre de commandes regroupées dans ce payout.
     */
    public function nombreCommandes(): int
    {
        return count($this->commandes_ids ?? []);
    }

    /**
     * Récupère les commandes incluses dans ce payout.
     */
    public function commandes()
    {
        $ids = $this->commandes_ids ?? [];
        if (empty($ids)) {
            return collect();
        }
        return Commande::whereIn('id', $ids)->get();
    }
}