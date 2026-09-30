<?php

namespace App\Console\Commands;

use App\Models\Disponibilite;
use Illuminate\Console\Command;

class ExpirerDisponibilites extends Command
{
    protected $signature = 'transform:expirer-dispos';
    protected $description = 'Marque comme expirées les disponibilités dépassées (RG-011)';

    public function handle(): int
    {
        $count = Disponibilite::whereIn('statut', ['declaree', 'associee'])
            ->where('date_expiration', '<', now()->startOfDay())
            ->update(['statut' => 'expiree']);

        $this->info("✅ {$count} disponibilité(s) expirée(s).");

        return Command::SUCCESS;
    }
}