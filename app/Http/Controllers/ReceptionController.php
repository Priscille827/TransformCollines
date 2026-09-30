<?php

namespace App\Http\Controllers;

use App\Models\Disponibilite;
use App\Models\HistoriqueFiabilite;
use App\Services\FiabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceptionController extends Controller
{
    public function __construct(private FiabiliteService $fiabilite) {}

    /**
     * Liste des livraisons en attente de confirmation (pour l'unité).
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->isUnite() && $user->type_compte !== 'admin') {
            abort(403);
        }

        $dispos = Disponibilite::with(['produit', 'producteur.utilisateur', 'besoin'])
            ->whereHas('besoin', fn($q) => $q->where('unite_id', $user->unite->id))
            ->whereIn('statut', ['associee'])
            ->orderBy('date_disponibilite')
            ->paginate(20);

        return view('receptions.index', compact('dispos'));
    }

    /**
     * Formulaire de confirmation.
     */
    public function create(Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if ($disponibilite->besoin->unite_id !== $user->unite->id) {
            abort(403, 'Cette livraison ne vous concerne pas.');
        }

        if ($disponibilite->statut !== 'associee') {
            return redirect()->route('receptions.index')
                ->with('error', 'Cette livraison a déjà été traitée.');
        }

        return view('receptions.create', compact('disponibilite'));
    }

    /**
     * Confirmer la réception (F-10).
     */
    public function store(Request $request, Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if ($disponibilite->besoin->unite_id !== $user->unite->id) {
            abort(403);
        }

        $data = $request->validate([
            'quantite_livree' => ['required', 'numeric', 'min:0'],
        ], [
            'quantite_livree.required' => 'Indiquez la quantité réellement reçue.',
            'quantite_livree.min'      => 'La quantité doit être ≥ 0.',
        ]);

        DB::beginTransaction();
        try {
            $disponibilite->update([
                'quantite_livree' => $data['quantite_livree'],
                'statut'          => 'confirmee',
            ]);

            // Historique fiabilité producteur (RG-016)
            $promis = (float) $disponibilite->quantite;
            $livre  = (float) $data['quantite_livree'];
            $ratio  = $promis > 0 ? $livre / $promis : 0;

            $resultat = match (true) {
                $ratio >= 0.95 => 'tenu',
                $ratio >= 0.5  => 'partiellement_tenu',
                default        => 'non_tenu',
            };

            HistoriqueFiabilite::create([
                'type_acteur'      => 'producteur',
                'producteur_id'    => $disponibilite->producteur_id,
                'unite_id'         => null,
                'disponibilite_id' => $disponibilite->id,
                'type_evenement'   => 'livraison',
                'resultat'         => $resultat,
                'commentaire'      => "Promis : {$promis} kg, livré : {$livre} kg",
            ]);

            DB::commit();

            // Recalculer le score de fiabilité
            $this->fiabilite->recalculerProducteur($disponibilite->producteur);

            return redirect()->route('receptions.index')
                ->with('success', 'Réception confirmée. Historique de fiabilité mis à jour.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }
}