<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Disponibilite;
use App\Models\Notification;
use App\Services\KkiapayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    public function __construct(private KkiapayService $kkiapay) {}

    /**
     * Page de paiement pour une commande acceptée.
     */
    public function payer(Commande $commande)
    {
        $user = Auth::user();

        if ($commande->acheteur_id !== $user->id) {
            abort(403);
        }

        if ($commande->statut_paiement === 'paye') {
            return redirect()->route('commandes.show', $commande)
                ->with('info', 'Cette commande est déjà payée.');
        }

        if ($commande->statut !== 'acceptee') {
            return redirect()->route('commandes.show', $commande)
                ->with('error', 'Cette commande n\'est pas en attente de paiement.');
        }

        // Mémoriser la commande en cours de paiement pour le callback
        session(['paiement_commande_id' => $commande->id]);

        return view('paiement.checkout', compact('commande'));
    }

    /**
     * Webhook Kkiapay — reçoit la confirmation de paiement.
     */
    public function webhook(Request $request)
    {
        $payload = $request->all();
        Log::info('Kkiapay webhook reçu', $payload);

        // Ignorer les pings
        if (($payload['event'] ?? '') === 'transaction.ping') {
            return response()->json(['success' => true, 'message' => 'Ping acknowledged']);
        }

        $transactionId = $payload['transactionId'] ?? null;
        $isSuccess     = ($payload['isPaymentSucces'] ?? false) === true;
        $stateData     = $payload['stateData'] ?? null;

        if (!$transactionId) {
            Log::warning('Kkiapay webhook: transactionId manquant');
            return response()->json(['error' => 'Missing transactionId'], 400);
        }

        [$commandeId, $reference] = $this->parserStateData($stateData);

        Log::info('Kkiapay webhook: stateData parsé', [
            'commandeId' => $commandeId,
            'reference'  => $reference,
        ]);

        $commande = $this->trouverCommande($commandeId, $reference, $transactionId);

        if (!$commande) {
            Log::error('Kkiapay webhook: COMMANDE INTROUVABLE', [
                'transactionId' => $transactionId,
                'stateData'     => $stateData,
                'payload'       => $payload,
            ]);
            return response()->json(['error' => 'Commande introuvable'], 404);
        }

        // Idempotence
        if ($commande->statut_paiement === 'paye') {
            return response()->json(['success' => true, 'message' => 'Already processed']);
        }

        if ($isSuccess) {
            $this->marquerPayee($commande, $transactionId);
            Log::info("Kkiapay: commande {$commande->reference} marquée comme payée");
            return response()->json(['success' => true]);
        }

        $commande->update(['statut_paiement' => 'echoue']);
        return response()->json(['error' => 'Paiement non confirmé'], 400);
    }

    /**
     * Retour du client après le widget Kkiapay.
     */
    public function callback(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $commandeId    = session('paiement_commande_id');

        Log::info('Kkiapay callback reçu', [
            'transaction_id' => $transactionId,
            'commande_id'    => $commandeId,
            'all_query'      => $request->query(),
        ]);

        if (!$transactionId) {
            return $commandeId
                ? redirect()->route('paiement.echec', $commandeId)
                : redirect()->route('commandes.index')->with('error', 'Paiement annulé.');
        }

        // 1. Par transaction_id (si le webhook est déjà passé)
        $commande = Commande::where('transaction_id', $transactionId)->first();

        // 2. Par la commande mémorisée en session
        if (!$commande && $commandeId) {
            $commande = Commande::where('id', $commandeId)
                ->where('acheteur_id', Auth::id())
                ->first();
        }

        if (!$commande) {
            Log::error('Kkiapay callback: commande introuvable', [
                'transaction_id' => $transactionId,
            ]);

            return redirect()->route('commandes.index')
                ->with('error', 'Paiement reçu mais commande introuvable. Contactez le support avec la référence ' . $transactionId);
        }

        if ($commande->acheteur_id !== Auth::id()) {
            abort(403);
        }

        // Déjà traité par le webhook
        if ($commande->statut_paiement === 'paye') {
            session()->forget('paiement_commande_id');
            return redirect()->route('paiement.succes', $commande);
        }

        // Vérification côté serveur auprès de Kkiapay
        $transaction = $this->kkiapay->verifierTransaction($transactionId);
        Log::info('Kkiapay verif', ['transaction' => $transaction]);

        $status = is_array($transaction) ? ($transaction['status'] ?? null) : null;

        if ($status === 'SUCCESS') {
            $this->marquerPayee($commande, $transactionId);
            session()->forget('paiement_commande_id');
            return redirect()->route('paiement.succes', $commande);
        }

        if ($status === 'FAILED') {
            $commande->update(['statut_paiement' => 'echoue']);
            return redirect()->route('paiement.echec', $commande);
        }

        return redirect()->route('commandes.show', $commande)
            ->with('info', 'Paiement en cours de confirmation. Actualisez dans quelques secondes.');
    }

    /**
     * Page de succès.
     */
    public function succes(Commande $commande)
    {
        $user = Auth::user();

        if ($commande->acheteur_id !== $user->id) {
            abort(403);
        }

        return view('paiement.succes', compact('commande'));
    }

    /**
     * Page d'échec.
     */
    public function echec(Commande $commande)
    {
        $user = Auth::user();

        if ($commande->acheteur_id !== $user->id) {
            abort(403);
        }

        return view('paiement.echec', compact('commande'));
    }

    // ─── Helpers privés ─────────────────────────────────────────

    /**
     * Marque la commande payée, passe la dispo à "confirmee"
     * et notifie le vendeur. Une seule fois.
     */
    private function marquerPayee(Commande $commande, string $transactionId): bool
    {
        return DB::transaction(function () use ($commande, $transactionId) {
            $verrouillee = Commande::where('id', $commande->id)->lockForUpdate()->first();

            if (!$verrouillee || $verrouillee->statut_paiement === 'paye') {
                return false;
            }

            $verrouillee->update([
                'transaction_id'  => $transactionId,
                'statut_paiement' => 'paye',
                'paye_le'         => now(),
            ]);

            // ⚠️ Si la commande vient d'un besoin, marquer la dispo comme confirmée
            if ($verrouillee->origine === 'besoin' && $verrouillee->disponibilite_id) {
                $disponibilite = Disponibilite::find($verrouillee->disponibilite_id);
                if ($disponibilite && $disponibilite->statut === 'en_paiement') {
                    $disponibilite->update(['statut' => 'confirmee']);
                    Log::info("Kkiapay: dispo #{$disponibilite->id} marquée comme confirmée (paiement reçu)");
                }
            }

            $vendeurId = $this->vendeurUtilisateurId($verrouillee);

            if ($vendeurId) {
                Notification::create([
                    'utilisateur_id' => $vendeurId,
                    'code'           => 'N-05',
                    'titre'          => 'Paiement reçu',
                    'canal'          => 'web',
                    'contenu'        => "Le client a payé la commande {$verrouillee->reference}. Vous pouvez préparer la livraison.",
                    'url'            => route('commandes.recues.show', $verrouillee),
                    'lu'             => false,
                    'created_at'     => now(),
                ]);
            }

            return true;
        });
    }

    /**
     * Utilisateur vendeur : producteur, ou unité si pas de producteur.
     */
    private function vendeurUtilisateurId(Commande $commande): ?int
    {
        if ($commande->producteur_id) {
            return $commande->producteur?->utilisateur_id;
        }

        $commande->loadMissing('lignes.offre.unite');

        return $commande->lignes->first()?->offre?->unite?->utilisateur_id;
    }

    /**
     * Parse stateData (tableau, JSON ou simple chaîne).
     *
     * @return array{0: int|string|null, 1: string|null}
     */
    private function parserStateData(mixed $stateData): array
    {
        $commandeId = null;
        $reference  = null;

        if (!$stateData) {
            return [$commandeId, $reference];
        }

        if (is_array($stateData)) {
            return [$stateData['commande_id'] ?? null, $stateData['reference'] ?? null];
        }

        $parsed = json_decode($stateData, true);

        if (is_array($parsed)) {
            return [$parsed['commande_id'] ?? null, $parsed['reference'] ?? null];
        }

        return [null, trim((string) $stateData)];
    }

    /**
     * Retrouve la commande par ID, puis référence, puis transaction_id.
     */
    private function trouverCommande($commandeId, ?string $reference, string $transactionId): ?Commande
    {
        if ($commandeId && is_numeric($commandeId)) {
            $commande = Commande::find((int) $commandeId);
            if ($commande) {
                return $commande;
            }
        }

        if ($reference) {
            $commande = Commande::where('reference', $reference)->first();
            if ($commande) {
                return $commande;
            }
        }

        return Commande::where('transaction_id', $transactionId)->first();
    }
}