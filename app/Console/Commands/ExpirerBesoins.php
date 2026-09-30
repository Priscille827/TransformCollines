<?php

namespace App\Console\Commands;

use App\Models\Besoin;
use App\Services\CouvertureService;
use Illuminate\Console\Command;

class ExpirerBesoins extends Command
{
    protected $signature = 'transform:expirer-besoins';
    protected $description = 'Marque comme expirés les besoins dépassés sans couverture complète (RG-006)';

    public function __construct(private CouvertureService $couverture)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $besoins = Besoin::whereIn('statut', ['actif', 'partiellement_couvert'])
            ->where('delai', '<', now()->startOfDay())
            ->get();

        $count = 0;
        foreach ($besoins as $besoin) {
            // On libère les dispos associées non confirmées (RG-010)
            $besoin->disponibilites()
                ->whereIn('statut', ['associee'])
                ->update([
                    'statut'   => 'declaree',
                    'besoin_id' => null,
                ]);

            $besoin->update(['statut' => 'expire']);
            $count++;
        }

        $this->info("✅ {$count} besoin(s) expiré(s).");

        return Command::SUCCESS;
    }
}