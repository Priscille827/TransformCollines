<?php

namespace Database\Seeders;

use App\Models\Utilisateur;
use Illuminate\Database\Seeder;

class CoordonneesSeeder extends Seeder
{
    public function run(): void
    {
        // Coordonnées GPS des chefs-lieux des 6 communes des Collines
        $coords = [
            'Dassa-Zoumé' => [7.7833, 2.1833],
            'Glazoué'     => [7.9667, 2.2333],
            'Ouèssè'      => [8.4833, 2.4167],
            'Savalou'     => [7.9333, 1.9667],
            'Savè'        => [8.0333, 2.4833],
            'Bantè'       => [8.4167, 1.8833],
        ];

        // Appliquer aux utilisateurs qui n'ont pas encore de coordonnées
        Utilisateur::whereNull('latitude')->each(function ($u) use ($coords) {
            $commune = $u->commune->nom ?? null;
            if ($commune && isset($coords[$commune])) {
                // Petite variation aléatoire pour ne pas superposer les points
                $u->update([
                    'latitude'  => $coords[$commune][0] + (rand(-80, 80) / 10000),
                    'longitude' => $coords[$commune][1] + (rand(-80, 80) / 10000),
                ]);
            }
        });

        $this->command->info('✅ Coordonnées GPS attribuées aux utilisateurs.');
    }
}