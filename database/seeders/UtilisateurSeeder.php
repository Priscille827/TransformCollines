<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Producteur;
use App\Models\Produit;
use App\Models\UniteTransformation;
use App\Models\Utilisateur;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UtilisateurSeeder extends Seeder
{
    public function run(): void
    {
        $communes = Commune::pluck('id', 'nom');
        $produits = Produit::pluck('id', 'nom');

        // ─── ADMIN ─────────────────────────────────────────────
        Utilisateur::create([
            'nom'           => 'Administrateur Plateforme',
            'telephone'     => '+22990000000',
            'type_compte'   => 'admin',
            'commune_id'    => $communes['Dassa-Zoumé'],
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);

        // ─── INSTITUTION (MAEP) ────────────────────────────────
        Utilisateur::create([
            'nom'           => 'Agent MAEP Collines',
            'telephone'     => '+22991000000',
            'type_compte'   => 'institution',
            'commune_id'    => $communes['Dassa-Zoumé'],
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);

        // ─── PRODUCTEURS ───────────────────────────────────────
        $fifame = Utilisateur::create([
            'nom'           => 'Fifamè AGBODJAN',
            'telephone'     => '+22997000001',
            'type_compte'   => 'producteur',
            'commune_id'    => $communes['Savalou'],
            'village'       => 'Savalou-Agbado',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $pFifame = Producteur::create([
            'utilisateur_id'        => $fifame->id,
            'est_cooperative'       => true,
            'nombre_membres_approx' => 25,
            'score_fiabilite'       => 'fiable',
        ]);
        $pFifame->produits()->attach([$produits['Manioc']]);

        $jean = Utilisateur::create([
            'nom'           => 'Jean HOUNKPATIN',
            'telephone'     => '+22997000002',
            'type_compte'   => 'producteur',
            'commune_id'    => $communes['Dassa-Zoumé'],
            'village'       => 'Paouignan',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $pJean = Producteur::create([
            'utilisateur_id'        => $jean->id,
            'est_cooperative'       => false,
            'score_fiabilite'       => 'nouveau',
        ]);
        $pJean->produits()->attach([$produits['Manioc'], $produits['Soja']]);

        $marie = Utilisateur::create([
            'nom'           => 'Marie DOSSOU',
            'telephone'     => '+22997000003',
            'type_compte'   => 'producteur',
            'commune_id'    => $communes['Savè'],
            'village'       => 'Savè Centre',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $pMarie = Producteur::create([
            'utilisateur_id'        => $marie->id,
            'est_cooperative'       => true,
            'nombre_membres_approx' => 12,
            'score_fiabilite'       => 'a_surveiller',
        ]);
        $pMarie->produits()->attach([$produits['Soja']]);

        $pierre = Utilisateur::create([
            'nom'           => 'Pierre ADJOVI',
            'telephone'     => '+22997000004',
            'type_compte'   => 'producteur',
            'commune_id'    => $communes['Glazoué'],
            'village'       => 'Glazoué',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $pPierre = Producteur::create([
            'utilisateur_id'        => $pierre->id,
            'est_cooperative'       => false,
            'score_fiabilite'       => 'fiable',
        ]);
        $pPierre->produits()->attach([$produits['Anacarde'], $produits['Karité']]);

        // ─── UNITÉS DE TRANSFORMATION ──────────────────────────
        $unitePaouignan = Utilisateur::create([
            'nom'           => 'Unité de Transformation de Paouignan',
            'telephone'     => '+22996000001',
            'type_compte'   => 'unite_transformation',
            'commune_id'    => $communes['Dassa-Zoumé'],
            'village'       => 'Paouignan',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $uPaouignan = UniteTransformation::create([
            'utilisateur_id'             => $unitePaouignan->id,
            'capacite_traitement_approx' => 50.00,
            'zone_collecte_rayon_km'     => 60.00,
            'score_fiabilite'            => 'fiable',
        ]);
        $uPaouignan->produits()->attach([$produits['Manioc']]);

        $uniteSave = Utilisateur::create([
            'nom'           => 'Coopérative de Transformation de Savè',
            'telephone'     => '+22996000002',
            'type_compte'   => 'unite_transformation',
            'commune_id'    => $communes['Savè'],
            'village'       => 'Savè',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $uSave = UniteTransformation::create([
            'utilisateur_id'             => $uniteSave->id,
            'capacite_traitement_approx' => 30.00,
            'zone_collecte_rayon_km'     => 45.00,
            'score_fiabilite'            => 'nouveau',
        ]);
        $uSave->produits()->attach([$produits['Soja']]);

        $uniteGlazoue = Utilisateur::create([
            'nom'           => 'Unité Anacarde Glazoué',
            'telephone'     => '+22996000003',
            'type_compte'   => 'unite_transformation',
            'commune_id'    => $communes['Glazoué'],
            'village'       => 'Glazoué',
            'statut_compte' => 'actif',
            'mot_de_passe'  => Hash::make('password'),
        ]);
        $uGlazoue = UniteTransformation::create([
            'utilisateur_id'             => $uniteGlazoue->id,
            'capacite_traitement_approx' => 20.00,
            'zone_collecte_rayon_km'     => 50.00,
            'score_fiabilite'            => 'nouveau',
        ]);
        $uGlazoue->produits()->attach([$produits['Anacarde']]);

        $this->command->info('✅ Utilisateurs, producteurs et unités créés.');
        $this->command->info('   Logins de test (mot de passe : password) :');
        $this->command->info('   - Producteur  : +22997000001 (Fifamè / Savalou)');
        $this->command->info('   - Unité       : +22996000001 (Paouignan)');
        $this->command->info('   - Institution : +22991000000 (MAEP)');
    }
}