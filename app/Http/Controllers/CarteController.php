<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Commune;
use App\Models\Disponibilite;
use App\Models\Produit;
use Illuminate\Http\Request;

class CarteController extends Controller
{
    public function index()
    {
        $produits = Produit::orderBy('nom')->get();
        $communes = Commune::orderBy('nom')->get();

        return view('carte', compact('produits', 'communes'));
    }

    /**
     * API JSON pour Leaflet.
     */
    public function data(Request $request)
    {
        // ─── Besoins actifs ────────────────────────────────
        $besoinsQuery = Besoin::with([
            'produit',
            'unite.utilisateur',
            'communes',
        ])->whereIn('statut', ['actif', 'partiellement_couvert']);

        if ($request->filled('produit_id')) {
            $besoinsQuery->where('produit_id', $request->produit_id);
        }
        if ($request->filled('commune_id')) {
            $besoinsQuery->whereHas('communes', fn($q) => $q->where('commune_id', $request->commune_id));
        }

        $besoins = $besoinsQuery->get()
            ->filter(fn($b) => $b->unite->utilisateur->latitude)
            ->map(function ($b) {
                $u = $b->unite->utilisateur;
                return [
                    'id'              => $b->id,
                    'type'            => 'besoin',
                    'titre'           => $b->produit->nom,
                    'unite'           => $u->nom,
                    'commune'         => $u->commune->nom ?? '',
                    'quantite'        => (float) $b->quantite_recherchee,
                    'taux'            => (float) $b->taux_couverture,
                    'delai'           => $b->delai->format('d/m/Y'),
                    'lat'             => (float) $u->latitude,
                    'lng'             => (float) $u->longitude,
                    'url'             => route('besoins.show', $b->id),
                ];
            })->values();

        // ─── Disponibilités (libres ou associées non confirmées) ──
        $disposQuery = Disponibilite::with([
            'produit',
            'producteur.utilisateur',
        ])->whereIn('statut', ['declaree', 'associee']);

        if ($request->filled('produit_id')) {
            $disposQuery->where('produit_id', $request->produit_id);
        }
        if ($request->filled('commune_id')) {
            $disposQuery->whereHas('producteur.utilisateur', fn($q) => $q->where('commune_id', $request->commune_id));
        }

        $dispos = $disposQuery->get()
            ->filter(fn($d) => $d->producteur->utilisateur->latitude)
            ->map(function ($d) {
                $u = $d->producteur->utilisateur;
                return [
                    'id'        => $d->id,
                    'type'      => 'dispo',
                    'titre'     => $d->produit->nom,
                    'producteur' => $u->nom,
                    'commune'   => $u->commune->nom ?? '',
                    'quantite'  => (float) $d->quantite,
                    'statut'    => $d->statut,
                    'date'      => $d->date_disponibilite->format('d/m/Y'),
                    'lat'       => (float) $u->latitude,
                    'lng'       => (float) $u->longitude,
                ];
            })->values();

        return response()->json([
            'besoins' => $besoins,
            'dispos'  => $dispos,
            'centre'  => ['lat' => 7.9333, 'lng' => 2.1833], // centre des Collines
        ]);
    }
}