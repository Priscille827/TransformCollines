<?php

namespace App\Console\Commands;

use App\Models\Commande;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Console\Command;

class ProgrammerPayouts extends Command
{
    protected $signature = 'transform:payouts';
    protected $description = 'Vérifie et complète les payouts de la semaine';

    public function handle(PayoutService $payoutService)
    {
        $periodeDebut = now()->startOfWeek();
        $periodeFin = now()->endOfWeek();

        // Trouver les commandes terminées de la semaine
        $commandes = Commande::where('statut', 'terminee')
            ->where('statut_paiement', 'paye')
            ->whereBetween('updated_at', [$periodeDebut, $periodeFin])
            ->get();

        if ($commandes->isEmpty()) {
            $this->info('Aucune commande terminée cette semaine.');
            return Command::SUCCESS;
        }

        $ajoutees = 0;

        foreach ($commandes as $commande) {
            // Chercher si la commande est déjà dans un payout
            $dejaIncluse = Payout::where('periode_debut', $periodeDebut)
                ->where('periode_fin', $periodeFin)
                ->get()
                ->contains(function ($payout) use ($commande) {
                    return in_array($commande->id, $payout->commandes_ids ?? []);
                });

            if (!$dejaIncluse) {
                $payout = $payoutService->enregistrerCommande($commande);
                if ($payout) {
                    $ajoutees++;
                    $this->info("✅ Commande {$commande->reference} ajoutée au payout #{$payout->id}");
                }
            }
        }

        $total = Payout::where('periode_debut', $periodeDebut)
            ->where('periode_fin', $periodeFin)
            ->where('statut', 'en_attente')
            ->get();

        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("📊 {$ajoutees} commande(s) ajoutée(s)");
        $this->info("📊 {$total->count()} payout(s) en attente cette semaine");
        $this->info("💰 Montant total net dû : " . number_format($total->sum('montant_net'), 0, ',', ' ') . " FCFA");

        return Command::SUCCESS;
    }
}