<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Commune;
use App\Models\Disponibilite;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitutionController extends Controller
{
    /**
     * Tableau de bord institutionnel agrégé (F-14).
     */
    public function dashboard(Request $request)
    {
        // Filtres
        $produitId  = $request->input('produit_id');
        $communeId  = $request->input('commune_id');
        $periode    = $request->input('periode', '30'); // jours

        // ─── Stats globales ────────────────────────────────────
        $statsGlobales = $this->statsGlobales($produitId, $communeId, $periode);

        // ─── Répartition par produit ───────────────────────────
        $parProduit = $this->statsParProduit($communeId, $periode);

        // ─── Répartition par commune ───────────────────────────
        $parCommune = $this->statsParCommune($produitId, $periode);

        // ─── Zones de tension (besoins < 50% couverts) ────────
        $zonesTension = Besoin::with(['produit', 'unite.utilisateur.commune'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->where('taux_couverture', '<', 50)
            ->where('delai', '>=', now())
            ->when($produitId, fn($q) => $q->where('produit_id', $produitId))
            ->orderBy('taux_couverture')
            ->take(10)
            ->get();

        // ─── Zones sous-mobilisées (communes à faible potentiel) ─
        $zonesSousMobilisees = $this->zonesSousMobilisees();

        $produits = Produit::orderBy('nom')->get();
        $communes = Commune::orderBy('nom')->get();

        return view('institution.dashboard', compact(
            'statsGlobales',
            'parProduit',
            'parCommune',
            'zonesTension',
            'zonesSousMobilisees',
            'produits',
            'communes'
        ));
    }

    /**
     * Statistiques globales.
     */
    private function statsGlobales($produitId, $communeId, $periode): array
    {
        $dateDebut = now()->subDays((int) $periode);

        $besoinsQuery = Besoin::where('created_at', '>=', $dateDebut)
            ->when($produitId, fn($q) => $q->where('produit_id', $produitId))
            ->when($communeId, fn($q) => $q->whereHas('communes', fn($sq) => $sq->where('commune_id', $communeId)));

        $disposQuery = Disponibilite::where('created_at', '>=', $dateDebut)
            ->when($produitId, fn($q) => $q->where('produit_id', $produitId))
            ->when($communeId, fn($q) => $q->whereHas('producteur.utilisateur', fn($sq) => $sq->where('commune_id', $communeId)));

        return [
            'besoins_total'      => $besoinsQuery->count(),
            'besoins_actifs'     => (clone $besoinsQuery)->whereIn('statut', ['actif', 'partiellement_couvert'])->count(),
            'volume_recherche'   => (float) (clone $besoinsQuery)->sum('quantite_recherchee'),
            'volume_couvert'     => (float) (clone $besoinsQuery)->sum(DB::raw('quantite_recherchee * taux_couverture / 100')),
            'dispos_total'       => $disposQuery->count(),
            'volume_dispo'       => (float) (clone $disposQuery)->sum('quantite'),
            'taux_moyen'         => (float) round((clone $besoinsQuery)->avg('taux_couverture') ?? 0, 1),
            'producteurs_actifs' => (clone $disposQuery)->distinct('producteur_id')->count('producteur_id'),
            'unites_actives'     => (clone $besoinsQuery)->distinct('unite_id')->count('unite_id'),
        ];
    }

    /**
     * Stats par produit.
     */
    private function statsParProduit($communeId, $periode): array
    {
        $dateDebut = now()->subDays((int) $periode);

        return Produit::withCount([
            'besoins' => fn($q) => $q->where('created_at', '>=', $dateDebut)
                ->when($communeId, fn($sq) => $sq->whereHas('communes', fn($s) => $s->where('commune_id', $communeId))),
        ])->get()
        ->map(function ($p) use ($dateDebut, $communeId) {
            $besoins = Besoin::where('produit_id', $p->id)
                ->where('created_at', '>=', $dateDebut)
                ->when($communeId, fn($q) => $q->whereHas('communes', fn($sq) => $sq->where('commune_id', $communeId)))
                ->get();

            $dispos = Disponibilite::where('produit_id', $p->id)
                ->where('created_at', '>=', $dateDebut)
                ->when($communeId, fn($q) => $q->whereHas('producteur.utilisateur', fn($sq) => $sq->where('commune_id', $communeId)))
                ->get();

            return [
                'produit'          => $p->nom,
                'besoins_count'    => $besoins->count(),
                'volume_recherche' => (float) $besoins->sum('quantite_recherchee'),
                'volume_couvert'   => (float) $besoins->sum(fn($b) => $b->quantite_recherchee * $b->taux_couverture / 100),
                'dispos_count'     => $dispos->count(),
                'volume_dispo'     => (float) $dispos->sum('quantite'),
                'taux_moyen'       => (float) round($besoins->avg('taux_couverture') ?? 0, 1),
            ];
        })
        ->filter(fn($s) => $s['besoins_count'] > 0 || $s['dispos_count'] > 0)
        ->values()
        ->toArray();
    }

    /**
     * Stats par commune.
     */
    private function statsParCommune($produitId, $periode): array
    {
        $dateDebut = now()->subDays((int) $periode);

        return Commune::all()->map(function ($c) use ($dateDebut, $produitId) {
            $besoins = Besoin::where('created_at', '>=', $dateDebut)
                ->when($produitId, fn($q) => $q->where('produit_id', $produitId))
                ->whereHas('communes', fn($q) => $q->where('commune_id', $c->id))
                ->get();

            $dispos = Disponibilite::where('created_at', '>=', $dateDebut)
                ->when($produitId, fn($q) => $q->where('produit_id', $produitId))
                ->whereHas('producteur.utilisateur', fn($q) => $q->where('commune_id', $c->id))
                ->get();

            $producteurs = \App\Models\Utilisateur::where('type_compte', 'producteur')
                ->where('commune_id', $c->id)
                ->count();

            return [
                'commune'          => $c->nom,
                'besoins_count'    => $besoins->count(),
                'volume_recherche' => (float) $besoins->sum('quantite_recherchee'),
                'volume_couvert'   => (float) $besoins->sum(fn($b) => $b->quantite_recherchee * $b->taux_couverture / 100),
                'dispos_count'     => $dispos->count(),
                'volume_dispo'     => (float) $dispos->sum('quantite'),
                'taux_moyen'       => (float) round($besoins->avg('taux_couverture') ?? 0, 1),
                'producteurs'      => $producteurs,
            ];
        })
        ->filter(fn($s) => $s['besoins_count'] > 0 || $s['dispos_count'] > 0 || $s['producteurs'] > 0)
        ->values()
        ->toArray();
    }

    /**
     * Zones sous-mobilisées (peu de producteurs inscrits par rapport au potentiel).
     * Pour le MVP : on identifie simplement les communes avec le moins d'inscrits.
     */
    private function zonesSousMobilisees(): array
    {
        return Commune::withCount(['utilisateurs' => fn($q) => $q->where('type_compte', 'producteur')])
            ->orderBy('utilisateurs_count')
            ->take(3)
            ->get()
            ->map(fn($c) => [
                'commune'      => $c->nom,
                'producteurs'  => $c->utilisateurs_count,
            ])
            ->toArray();
    }

    /**
     * Export CSV (F-15).
     */
    public function export(Request $request)
    {
        $produitId = $request->input('produit_id');
        $communeId = $request->input('commune_id');
        $periode   = $request->input('periode', '30');

        $parProduit = $this->statsParProduit($communeId, $periode);
        $parCommune = $this->statsParCommune($produitId, $periode);

        $filename = 'transform-collines_export_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($parProduit, $parCommune, $periode) {
            $out = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // En-tête
            fputcsv($out, ['Rapport Transform\'Collines']);
            fputcsv($out, ['Généré le', now()->format('d/m/Y H:i')]);
            fputcsv($out, ['Période', "{$periode} derniers jours"]);
            fputcsv($out, []);
            fputcsv($out, ['Données agrégées et anonymisées — aucune donnée nominative']);
            fputcsv($out, []);

            // Section 1 : par produit
            fputcsv($out, ['=== RÉPARTITION PAR PRODUIT ===']);
            fputcsv($out, ['Produit', 'Nb besoins', 'Volume recherché (kg)', 'Volume couvert (kg)', 'Nb dispos', 'Volume dispo (kg)', 'Taux moyen (%)']);
            foreach ($parProduit as $s) {
                fputcsv($out, [
                    $s['produit'],
                    $s['besoins_count'],
                    number_format($s['volume_recherche'], 0, ',', ' '),
                    number_format($s['volume_couvert'], 0, ',', ' '),
                    $s['dispos_count'],
                    number_format($s['volume_dispo'], 0, ',', ' '),
                    $s['taux_moyen'],
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, []);

            // Section 2 : par commune
            fputcsv($out, ['=== RÉPARTITION PAR COMMUNE ===']);
            fputcsv($out, ['Commune', 'Nb besoins', 'Volume recherché (kg)', 'Volume couvert (kg)', 'Nb dispos', 'Volume dispo (kg)', 'Taux moyen (%)', 'Producteurs inscrits']);
            foreach ($parCommune as $s) {
                fputcsv($out, [
                    $s['commune'],
                    $s['besoins_count'],
                    number_format($s['volume_recherche'], 0, ',', ' '),
                    number_format($s['volume_couvert'], 0, ',', ' '),
                    $s['dispos_count'],
                    number_format($s['volume_dispo'], 0, ',', ' '),
                    $s['taux_moyen'],
                    $s['producteurs'],
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}