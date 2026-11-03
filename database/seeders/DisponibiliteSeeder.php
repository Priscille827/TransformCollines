<?php

namespace Database\Seeders;

use App\Models\Besoin;
use App\Models\Disponibilite;
use App\Models\Producteur;
use App\Models\Produit;
use Illuminate\Database\Seeder;

class DisponibiliteSeeder extends Seeder
{
    public function run(): void
    {
        $produits = Produit::pluck('id', 'nom');

        // Récupérer les producteurs créés
        $fifame = Producteur::whereHas('utilisateur', fn($q) => $q->where('telephone', '+22997000001'))->first();
        $jean   = Producteur::whereHas('utilisateur', fn($q) => $q->where('telephone', '+22997000002'))->first();
        $marie  = Producteur::whereHas('utilisateur', fn($q) => $q->where('telephone', '+22997000003'))->first();
        $pierre = Producteur::whereHas('utilisateur', fn($q) => $q->where('telephone', '+22997000004'))->first();

        // Récupérer les besoins créés
        $b1 = Besoin::where('quantite_recherchee', 20.00)->first(); // Manioc Paouignan
        $b2 = Besoin::where('quantite_recherchee', 15.00)->first(); // Soja Savè
        $b3 = Besoin::where('quantite_recherchee', 8.00)->first();  // Anacarde Glazoué

        // ─── Réponses au besoin 1 (Manioc Paouignan — 20 T) ────
        // Fifamè apporte 8 T → 40 % de couverture
        Disponibilite::create([
            'producteur_id'      => $fifame->id,
            'produit_id'         => $produits['Manioc'],
            'besoin_id'          => $b1->id,
            'quantite'           => 8000.00, // en kg
            'date_disponibilite' => now()->addDays(5),
            'date_expiration'    => now()->addDays(20),
            'statut'             => 'associee',
        ]);

        // Jean apporte 4 T → +20 % → total 60 %
        Disponibilite::create([
            'producteur_id'      => $jean->id,
            'produit_id'         => $produits['Manioc'],
            'besoin_id'          => $b1->id,
            'quantite'           => 4000.00,
            'date_disponibilite' => now()->addDays(6),
            'date_expiration'    => now()->addDays(21),
            'statut'             => 'associee',
        ]);

        // ─── Réponse au besoin 2 (Soja Savè — 15 T) ───────────
        // Marie apporte 6 T → 40 %
        Disponibilite::create([
            'producteur_id'      => $marie->id,
            'produit_id'         => $produits['Soja'],
            'besoin_id'          => $b2->id,
            'quantite'           => 6000.00,
            'date_disponibilite' => now()->addDays(4),
            'date_expiration'    => now()->addDays(19),
            'statut'             => 'associee',
        ]);

        // ─── Réponse au besoin 3 (Anacarde Glazoué — 8 T) ─────
        // Pierre apporte 3 T → 37.5 %
        Disponibilite::create([
            'producteur_id'      => $pierre->id,
            'produit_id'         => $produits['Anacarde'],
            'besoin_id'          => $b3->id,
            'quantite'           => 3000.00,
            'date_disponibilite' => now()->addDays(8),
            'date_expiration'    => now()->addDays(23),
            'statut'             => 'associee',
        ]);

        // ─── Déclarations libres (non associées) ───────────────
        Disponibilite::create([
            'producteur_id'      => $jean->id,
            'produit_id'         => $produits['Soja'],
            'besoin_id'          => null,
            'quantite'           => 2500.00,
            'date_disponibilite' => now()->addDays(10),
            'date_expiration'    => now()->addDays(25),
            'statut'             => 'declaree',
        ]);

        Disponibilite::create([
            'producteur_id'      => $fifame->id,
            'produit_id'         => $produits['Manioc'],
            'besoin_id'          => null,
            'quantite'           => 3000.00,
            'date_disponibilite' => now()->addDays(12),
            'date_expiration'    => now()->addDays(27),
            'statut'             => 'declaree',
        ]);
// Recalcul rapide du taux de couverture
Besoin::all()->each(function ($besoin) {
    $total = $besoin->disponibilites()
        ->whereIn('statut', ['associee', 'confirmee'])
        ->sum('quantite');

    $taux = $besoin->quantite_recherchee > 0
        ? min(100, round(($total / $besoin->quantite_recherchee) * 100, 2))
        : 0;

    $statut = match(true) {
        $taux >= 100 => 'couvert',
        $taux > 0    => 'partiellement_couvert',
        default      => 'actif',
    };

    $besoin->update([
        'taux_couverture' => $taux,
        'statut'          => $statut,
    ]);
});

$this->command->info('✅ Taux de couverture recalculés.');
        $this->command->info('✅ Disponibilités créées et associées aux besoins.');
        $this->command->info('   Taux de couverture attendus :');
        $this->command->info('   - Manioc Paouignan : 12/20 T = 60 %');
        $this->command->info('   - Soja Savè        : 6/15 T  = 40 %');
        $this->command->info('   - Anacarde Glazoué : 3/8 T   = 37.5 %');
    }
}