<?php

namespace App\Http\Controllers;

use App\Models\Commune;
use App\Models\Disponibilite;
use App\Models\Produit;
use App\Models\SignalementSms;
use App\Models\Utilisateur;
use App\Services\SmsParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SmsController extends Controller
{
    public function __construct(private SmsParser $parser) {}

    /**
     * Page de simulation — joue le rôle du téléphone.
     */
    public function simuler()
    {
        return view('sms.simuler', [
            'producteurs' => Utilisateur::where('type_compte', 'producteur')
                ->with('commune')
                ->orderBy('nom')
                ->get(),
            'produits' => Produit::orderBy('nom')->get(),
            'communes' => Commune::orderBy('nom')->get(),
        ]);
    }

    /**
     * Reçoit un SMS (simulé par le formulaire).
     */
    public function recevoir(Request $request)
    {
        $data = $request->validate([
            'telephone' => 'required|string',
            'message'   => 'required|string|max:160',
        ]);

        // Enregistrer le SMS brut
        $sms = SignalementSms::create([
            'telephone'         => $data['telephone'],
            'message_brut'      => $data['message'],
            'statut_traitement' => 'erreur_format',
        ]);

        // Trouver l'utilisateur
        $utilisateur = Utilisateur::where('telephone', $data['telephone'])->first();

        if (!$utilisateur || !$utilisateur->isProducteur()) {
            return response()->json([
                'success' => false,
                'reponse' => "Ce numéro n'est pas enregistré comme producteur.",
                'sms_id'  => $sms->id,
            ]);
        }

        // Parser le message
        $parsed = $this->parser->parse($data['message']);

        if (!$parsed) {
            $sms->update(['statut_traitement' => 'erreur_format']);

            return response()->json([
                'success' => false,
                'reponse' => $this->parser->aideFormat(),
                'sms_id'  => $sms->id,
            ]);
        }

        // Créer la disponibilité
        DB::beginTransaction();
        try {
            $dispo = Disponibilite::create([
                'producteur_id'      => $utilisateur->producteur->id,
                'produit_id'         => $parsed['produit_id'],
                'besoin_id'          => null, // Déclaration libre
                'quantite'           => $parsed['quantite'],
                'date_disponibilite' => now(),
                'date_expiration'    => now()->addDays(15),
                'statut'             => 'declaree',
            ]);

            $sms->update([
                'produit_id'        => $parsed['produit_id'],
                'quantite'          => $parsed['quantite'],
                'commune_id'        => $parsed['commune_id'],
                'disponibilite_id'  => $dispo->id,
                'statut_traitement' => 'traite',
            ]);

            DB::commit();

            $reponse = "✅ Disponibilité enregistrée : {$parsed['quantite']} kg de {$parsed['produit_nom']} à {$parsed['commune_nom']}. Visible par les unités proches.";

            return response()->json([
                'success' => true,
                'reponse' => $reponse,
                'sms_id'  => $sms->id,
                'dispo_id' => $dispo->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'reponse' => 'Erreur serveur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Historique des SMS reçus.
     */
    public function journal()
    {
        $sms = SignalementSms::with(['produit', 'commune', 'disponibilite'])
            ->latest('id')
            ->paginate(30);

        return view('sms.journal', compact('sms'));
    }

    /**
     * Simulation d'une alerte SMS envoyée à un producteur (N-01 / N-03).
     */
    public function envoyerAlerte(Request $request)
    {
        $data = $request->validate([
            'utilisateur_id' => 'required|exists:utilisateurs,id',
            'contenu'        => 'required|string|max:160',
        ]);

        $notif = \App\Models\Notification::create([
            'utilisateur_id' => $data['utilisateur_id'],
            'code'           => 'N-03',
            'canal'          => 'sms',
            'contenu'        => $data['contenu'],
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return response()->json([
            'success' => true,
            'notif_id' => $notif->id,
            'message' => "SMS envoyé au {$notif->utilisateur->telephone}",
        ]);
    }
}