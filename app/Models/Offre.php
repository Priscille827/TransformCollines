<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Offre extends Model
{
    protected $table = 'offres';

    protected $fillable = [
        'producteur_id',
        'unite_id',
        'produit_id',
        'categorie_id',
        'titre',
        'description',
        'unite_mesure_id',
        'poids_unitaire_kg',
        'unite_reference_id',
        'quantite_disponible',
        'quantite_kg_equivalent',
        'prix_detail',
        'prix_gros',
        'seuil_gros',
        'localisation',
        'conditions_retrait',
        'statut',
        'photo_principale',
        'vues',
        'prix_b2b',
    ];

    protected $casts = [
        'quantite_disponible'    => 'decimal:2',
        'poids_unitaire_kg'      => 'decimal:2',
        'quantite_kg_equivalent' => 'decimal:2',
        'prix_detail'            => 'decimal:2',
        'prix_gros'              => 'decimal:2',
        'seuil_gros'             => 'decimal:2',
        'prix_b2b' => 'decimal:2',
    ];

    // ─── Relations ───────────────────────────────────────────
    public function producteur()
    {
        return $this->belongsTo(Producteur::class);
    }

    public function unite()
    {
        return $this->belongsTo(UniteTransformation::class, 'unite_id');
    }

    public function produit()
    {
        return $this->belongsTo(Produit::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function uniteMesure()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_id');
    }

    public function uniteReference()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_reference_id');
    }

    public function photos()
    {
        return $this->hasMany(OffrePhoto::class)->orderBy('ordre');
    }

    public function lignesCommandes()
    {
        return $this->hasMany(LigneCommande::class);
    }

    public function panierItems()
    {
        return $this->hasMany(PanierItem::class);
    }

    // ─── Helpers ─────────────────────────────────────────────
    public function estPublieeParUnite(): bool
    {
        return $this->unite_id !== null;
    }

    public function vendeur()
    {
        return $this->estPublieeParUnite() ? $this->unite : $this->producteur;
    }

    public function nomVendeur(): string
    {
        return $this->vendeur()?->utilisateur?->nom ?? 'Vendeur inconnu';
    }

    public function prixPourQuantite(float $quantite): float
    {
        if ($this->prix_gros && $this->seuil_gros && $quantite >= $this->seuil_gros) {
            return (float) $this->prix_gros;
        }
        return (float) $this->prix_detail;
    }

    public function getQuantiteFormateeAttribute(): string
    {
        if (!$this->uniteMesure || $this->uniteMesure->est_reference) {
            return number_format($this->quantite_disponible, 0, ',', ' ') . ' ' . ($this->uniteMesure->symbole ?? 'kg');
        }

        $uniteNom = $this->uniteMesure->nom;
        $qte = $this->quantite_disponible;
        $pluriel = $qte > 1 ? 's' : '';

        if ($this->poids_unitaire_kg && $this->uniteReference) {
            return "{$qte} {$uniteNom}{$pluriel} (" .
                number_format($this->quantite_kg_equivalent, 0, ',', ' ') . ' ' .
                $this->uniteReference->symbole . ')';
        }

        return "{$qte} {$uniteNom}{$pluriel}";
    }

    public function scopeActives($q)
    {
        return $q->where('statut', 'active')->where('quantite_disponible', '>', 0);
    }

    
}