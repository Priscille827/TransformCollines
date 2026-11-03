<?php

namespace Database\Seeders;

use App\Models\Besoin;
use App\Models\Commune;
use App\Models\Produit;
use App\Models\UniteTransformation;
use Illuminate\Database\Seeder;

class BesoinSeeder extends Seeder
{
    public function run(): void
    {
        $unites   = UniteTransformation::with('utilisateur')->get();
        $produits = Produit::pluck('id', 'nom');
        $communes = Commune::pluck('id', 'nom');

        // Besoin 1 — Paouignan recherche 20 T de manioc dans 10 jours
        $uPaouignan = $unites->firstWhere('utilisateur.nom', 'Unité de Transformation de Paouignan');
        $b1 = Besoin::create([
            'unite_id'            => $uPaouignan->id,
            'produit_id'          => $produits['Manioc'],
            'quantite_recherchee' => 20.00,
            'delai'               => now()->addDays(10),
            'statut'              => 'actif',
            'taux_couverture'     => 0,
        ]);
        $b1->communes()->attach([
            $communes['Dassa-Zoumé'],
            $communes['Savalou'],
            $communes['Glazoué'],
        ]);

        // Besoin 2 — Savè recherche 15 T de soja dans 7 jours
        $uSave = $unites->firstWhere('utilisateur.nom', 'Coopérative de Transformation de Savè');
        $b2 = Besoin::create([
            'unite_id'            => $uSave->id,
            'produit_id'          => $produits['Soja'],
            'quantite_recherchee' => 15.00,
            'delai'               => now()->addDays(7),
            'statut'              => 'actif',
            'taux_couverture'     => 0,
        ]);
        $b2->communes()->attach([
            $communes['Savè'],
            $communes['Ouèssè'],
        ]);

        // Besoin 3 — Glazoué recherche 8 T d'anacarde dans 15 jours
        $uGlazoue = $unites->firstWhere('utilisateur.nom', 'Unité Anacarde Glazoué');
        $b3 = Besoin::create([
            'unite_id'            => $uGlazoue->id,
            'produit_id'          => $produits['Anacarde'],
            'quantite_recherchee' => 8.00,
            'delai'               => now()->addDays(15),
            'statut'              => 'actif',
            'taux_couverture'     => 0,
        ]);
        $b3->communes()->attach([
            $communes['Glazoué'],
            $communes['Bantè'],
        ]);

        // Besoin 4 — Paouignan recherche à nouveau 10 T de manioc (plus tard)
        $b4 = Besoin::create([
            'unite_id'            => $uPaouignan->id,
            'produit_id'          => $produits['Manioc'],
            'quantite_recherchee' => 10.00,
            'delai'               => now()->addDays(20),
            'statut'              => 'actif',
            'taux_couverture'     => 0,
        ]);
        $b4->communes()->attach([$communes['Dassa-Zoumé']]);

        // Besoin 5 — Savè recherche 5 T de manioc (besoin urgent, J-3)
        $b5 = Besoin::create([
            'unite_id'            => $uSave->id,
            'produit_id'          => $produits['Manioc'],
            'quantite_recherchee' => 5.00,
            'delai'               => now()->addDays(3),
            'statut'              => 'actif',
            'taux_couverture'     => 0,
        ]);
        $b5->communes()->attach([$communes['Savè'], $communes['Glazoué']]);

        $this->command->info('✅ 5 besoins créés.');
    }
}