<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Commune;
use App\Models\Disponibilite;
use App\Models\Produit;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IvrController extends Controller
{
    public function index()
    {
        return view('ivr.simuler', [
            'producteurs' => Utilisateur::where('type_compte', 'producteur')->with('commune')->get(),
        ]);
    }

    /**
     * Menu IVR — étape 1 : détecter le producteur.
     */
    public function menu(Request $request)
    {
        $data = $request->validate([
            'telephone' => 'required|string',
        ]);

        $user = Utilisateur::where('telephone', $data['telephone'])->first();

        if (!$user || !$user->isProducteur()) {
            return response()->json([
                'etape'    => 'erreur',
                'message'  => "Numéro non reconnu. Raccrochez et réessayez.",
            ]);
        }

        // Besoins actifs du produit principal du producteur
        $produitIds = $user->producteur->produits->pluck('id');
        $besoins = Besoin::with(['produit', 'unite.utilisateur.commune'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->whereIn('produit_id', $produitIds)
            ->orderBy('delai')
            ->take(3)
            ->get();

        $options = $besoins->map(fn($b, $i) => [
            'touche'  => $i + 1,
            'id'      => $b->id,
            'label'   => sprintf(
                '%s à %s, %s kg recherchés',
                $b->produit->nom,
                $b->unite->utilisateur->commune->nom,
                number_format($b->quantite_recherchee, 0, ',', ' ')
            ),
        ])->values();

        return response()->json([
            'etape'   => 'menu',
            'message' => "Bonjour {$user->nom}. Vous avez " . $options->count() . " besoin(s) actif(s) près de chez vous.",
            'options' => $options,
        ]);
    }

    /**
     * Choix d'un besoin → demander la quantité.
     */
    public function choisir(Request $request)
    {
        $data = $request->validate([
            'besoin_id' => 'required|exists:besoins,id',
        ]);

        $besoin = Besoin::with(['produit', 'unite.utilisateur.commune'])->find($data['besoin_id']);

        return response()->json([
            'etape'   => 'quantite',
            'besoin'  => [
                'id'     => $besoin->id,
                'label'  => $besoin->produit->nom . ' — ' . $besoin->unite->utilisateur->nom,
            ],
            'message' => "Vous avez choisi : {$besoin->produit->nom}. Combien de kilogrammes pouvez-vous fournir ? Tapez le nombre puis dièse.",
        ]);
    }

    /**
     * Enregistrer la déclaration IVR.
     */
    public function enregistrer(Request $request)
    {
        $data = $request->validate([
            'telephone' => 'required|string',
            'besoin_id' => 'required|exists:besoins,id',
            'quantite'  => 'required|numeric|min:1',
        ]);

        $user = Utilisateur::where('telephone', $data['telephone'])->first();
        if (!$user || !$user->isProducteur()) {
            return response()->json(['etape' => 'erreur', 'message' => 'Numéro non reconnu.']);
        }

        $besoin = Besoin::find($data['besoin_id']);

        DB::beginTransaction();
        try {
            $dispo = Disponibilite::create([
                'producteur_id'      => $user->producteur->id,
                'produit_id'         => $besoin->produit_id,
                'besoin_id'          => $besoin->id,
                'quantite'           => $data['quantite'],
                'date_disponibilite' => now()->addDays(3),
                'date_expiration'    => now()->addDays(15),
                'statut'             => 'associee',
            ]);

            DB::commit();

            return response()->json([
                'etape'    => 'confirmation',
                'message'  => "Votre disponibilité de {$data['quantite']} kg a été enregistrée. Merci et à bientôt.",
                'dispo_id' => $dispo->id,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['etape' => 'erreur', 'message' => 'Erreur : ' . $e->getMessage()], 500);
        }
    }
}