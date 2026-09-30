<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UtilisateurSeeder::class,
            BesoinSeeder::class,
            DisponibiliteSeeder::class,
              CoordonneesSeeder::class, 
        ]);
    }
}