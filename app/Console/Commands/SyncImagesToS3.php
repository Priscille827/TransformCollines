<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SyncImagesToS3 extends Command
{
    /**
     * Nom de la commande Artisan.
     */
    protected $signature = 'images:sync-s3';

    /**
     * Description de la commande.
     */
    protected $description = 'Transfère les images du stockage local vers Supabase Storage (S3).';

    /**
     * Exécute la synchronisation des images.
     */
    public function handle(): int
    {
        try {
            $local = Storage::disk('public');
            $cloud = Storage::disk('s3');

            $files = $local->allFiles();

            if (empty($files)) {
                $this->warn('Aucun fichier trouvé dans le stockage local.');

                return Command::SUCCESS;
            }

            $this->info('Début de la synchronisation vers Supabase Storage...');
            $this->newLine();

            $success = 0;
            $failed = 0;

            foreach ($files as $file) {
                try {
                    $content = $local->get($file);

                    $uploaded = $cloud->put($file, $content);

                    if ($uploaded) {
                        $this->info("✓ Envoyé : {$file}");
                        $success++;
                    } else {
                        $this->error("✗ Échec de l'envoi : {$file}");
                        $failed++;
                    }

                } catch (Throwable $e) {
                    $this->error("✗ Erreur sur {$file} : {$e->getMessage()}");
                    $failed++;
                }
            }

            $this->newLine();
            $this->info('========== RÉSULTAT DE LA SYNCHRONISATION ==========');
            $this->info("Fichiers trouvés : " . count($files));
            $this->info("Fichiers transférés : {$success}");
            $this->error("Fichiers échoués : {$failed}");

            if ($failed > 0) {
                return Command::FAILURE;
            }

            $this->info('Synchronisation terminée avec succès !');

            return Command::SUCCESS;

        } catch (Throwable $e) {
            $this->error('Erreur générale : ' . $e->getMessage());

            return Command::FAILURE;
        }
    }
}
