<?php

namespace App\Console\Commands;

use App\Models\Commande;
use App\Models\Payout;
use App\Services\PayoutService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillPayoutsCommandesIds extends Command
{
    protected $signature = 'transform:backfill-payouts {--dry-run : Affiche sans appliquer}';
    protected $description = 'Ré-attribue commandes_ids aux payouts existants qui l\'ont perdu (bug historique)';

    public function handle(PayoutService $payoutService)
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->info('🔍 Analyse des payouts avec commandes_ids NULL ou vide...');

        $payouts = Payout::where(function ($q) {
            $q->whereNull('commandes_ids')
              ->orWhere('commandes_ids', '')
              ->orWhere('commandes_ids', '[]')
              ->orWhere('commandes_ids', 'null');
        })->orderBy('id')->get();

        if ($payouts->isEmpty()) {
            $this->info('✅ Aucun payout à corriger.');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  {$payouts->count()} payout(s) à traiter.");

        $corriges = 0;
        $nonResolus = [];

        foreach ($payouts as $payout) {
            // Déterminer le vendeur
            $producteurId = $payout->producteur_id;
            $uniteId      = $payout->unite_id;

            // Trouver les commandes qui correspondent :
            // - terminées + payées
            // - dans la période du payout
            // - du bon vendeur
            $commandes = Commande::where('statut', 'terminee')
                ->where('statut_paiement', 'paye')
                ->whereBetween('updated_at', [
                    $payout->periode_debut->copy()->startOfDay(),
                    $payout->periode_fin->copy()->endOfDay(),
                ])
                ->when($producteurId, fn($q) => $q->where('producteur_id', $producteurId))
                ->when(!$producteurId && $uniteId, function ($q) use ($uniteId) {
                    $q->whereHas('lignes.offre', fn($o) => $o->where('unite_id', $uniteId));
                })
                ->get();

            if ($commandes->isEmpty()) {
                $nonResolus[] = $payout->id;
                $this->error("  ❌ Payout #{$payout->id} : aucune commande correspondante trouvée.");
                continue;
            }

            // Vérifier que la somme des commandes correspond au montant du payout
            $totalCommandes = $commandes->sum('montant_total');
            $ecart = abs($totalCommandes - (float) $payout->montant);

            if ($ecart > 1) { // tolérance 1 FCFA pour arrondis
                $this->warn(
                    "  ⚠️  Payout #{$payout->id} : écart de {$ecart} FCFA " .
                    "(payout={$payout->montant}, commandes={$totalCommandes}). À vérifier manuellement."
                );
            }

            $ids = $commandes->pluck('id')->all();

            $this->line(
                "  ✅ Payout #{$payout->id} → commandes [" . implode(',', $ids) . "] " .
                "(" . number_format($totalCommandes, 0, ',', ' ') . " F)"
            );

            if (!$dryRun) {
                $payout->update(['commandes_ids' => $ids]);
            }
            $corriges++;
        }

        $this->newLine();
        $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info(($dryRun ? '🔎 [DRY-RUN] ' : '✅ ') . "{$corriges} payout(s) corrigé(s)");
        if (!empty($nonResolus)) {
            $this->error('❌ Payouts non résolus : #' . implode(', #', $nonResolus));
            $this->warn('   → Corrige-les manuellement via tinker.');
        }

        return Command::SUCCESS;
    }
}