<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    protected $table = 'produits';

    protected $fillable = [
        'nom',
        'categorie_id',
        'type_produit',
        'unite_mesure_defaut_id',
        'poids_kg_reference',
        'description',
        'actif',
        'statut',
        'cree_par',
    ];

    protected $casts = [
        'actif'              => 'boolean',
        'poids_kg_reference' => 'decimal:2',
    ];

    // ─── Relations ───────────────────────────────────────────
    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

    public function uniteMesureDefaut()
    {
        return $this->belongsTo(UniteMesure::class, 'unite_mesure_defaut_id');
    }

    public function createur()
    {
        return $this->belongsTo(Utilisateur::class, 'cree_par');
    }

    public function producteurs()
    {
        return $this->belongsToMany(
            Producteur::class,
            'producteur_produits',
            'produit_id',
            'producteur_id'
        );
    }

    public function unites()
    {
        return $this->belongsToMany(
            UniteTransformation::class,
            'unite_produits',
            'produit_id',
            'unite_id'
        );
    }

    public function besoins()
    {
        return $this->hasMany(Besoin::class);
    }

    public function disponibilites()
    {
        return $this->hasMany(Disponibilite::class);
    }

    public function offres()
    {
        return $this->hasMany(Offre::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function propositions()
    {
        return $this->hasMany(PropositionProduit::class);
    }

    // ─── Scopes ──────────────────────────────────────────────
    public function scopeActifs($q)
    {
        return $q->where('actif', true)->where('statut', 'actif');
    }

    public function scopeBruts($q)
    {
        return $q->where('type_produit', 'brut');
    }

    public function scopeTransformes($q)
    {
        return $q->where('type_produit', 'transforme');
    }

    public function scopeValides($q)
    {
        return $q->where('statut', 'actif');
    }

    public function scopeEnAttente($q)
    {
        return $q->where('statut', 'en_attente');
    }

    // ─── Helpers ─────────────────────────────────────────────
    public function isBrut(): bool
    {
        return $this->type_produit === 'brut';
    }

    public function isTransforme(): bool
    {
        return $this->type_produit === 'transforme';
    }
}