<?php

namespace App\Console\Commands;

use App\Models\Besoin;
use App\Models\Notification;
use App\Models\Utilisateur;
use Illuminate\Console\Command;

class AlertePenurieAnticipee extends Command
{
    protected $signature = 'transform:alerte-penurie';
    protected $description = 'Alerte les producteurs proches si un besoin < 70% à J-3 (RG-014)';

    public function handle(): int
    {
        // Besoins dont l'échéance est dans 3 jours
        $cibles = Besoin::with(['produit', 'unite.utilisateur.commune', 'communes'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->where('taux_couverture', '<', 70)
            ->whereDate('delai', now()->addDays(3)->startOfDay())
            ->get();

        $totalNotifs = 0;

        foreach ($cibles as $besoin) {
            // Producteurs du même produit, non encore mobilisés sur ce besoin
            $producteursMobilises = $besoin->disponibilites()
                ->whereIn('statut', ['associee', 'confirmee'])
                ->pluck('producteur_id');

            $producteurs = Utilisateur::where('type_compte', 'producteur')
                ->where('statut_compte', 'actif')
                ->whereHas('producteur.produits', fn($q) => $q->where('produits.id', $besoin->produit_id))
                ->whereNotIn('id', function ($sub) use ($besoin) {
                    $sub->select('utilisateur_id')
                        ->from('producteurs')
                        ->whereIn('id', $besoin->disponibilites()
                            ->whereIn('statut', ['associee', 'confirmee'])
                            ->pluck('producteur_id'));
                })
                // Filtre par zone de collecte (communes ciblées ou même commune que l'unité)
                ->where(function ($q) use ($besoin) {
                    if ($besoin->communes->isNotEmpty()) {
                        $communeIds = $besoin->communes->pluck('id');
                        $q->whereIn('commune_id', $communeIds);
                    } else {
                        $q->where('commune_id', $besoin->unite->utilisateur->commune_id);
                    }
                })
                ->get();

            $manquant = max(0, $besoin->quantite_recherchee - $besoin->disponibilites()
                ->whereIn('statut', ['associee', 'confirmee'])
                ->sum('quantite'));

            foreach ($producteurs as $p) {
                // Éviter les doublons : une notif N-03 par besoin par utilisateur
                $dejaEnvoyee = Notification::where('utilisateur_id', $p->id)
                    ->where('code', 'N-03')
                    ->where('contenu', 'LIKE', "%besoin #{$besoin->id}%")
                    ->exists();

                if ($dejaEnvoyee) continue;

                Notification::create([
                    'utilisateur_id' => $p->id,
                    'code'           => 'N-03',
                    'canal'          => 'web',
                    'contenu'        => sprintf(
                        'Besoin urgent en %s à %s — quantité manquante : %s kg. [besoin #%d]',
                        $besoin->produit->nom,
                        $besoin->unite->utilisateur->commune->nom,
                        number_format($manquant, 0, ',', ' '),
                        $besoin->id
                    ),
                    'lu'             => false,
                      'created_at'     => now(),
                ]);
                $totalNotifs++;
            }
        }

        $this->info("✅ {$totalNotifs} notification(s) de pénurie anticipée envoyée(s).");

        return Command::SUCCESS;
    }
}