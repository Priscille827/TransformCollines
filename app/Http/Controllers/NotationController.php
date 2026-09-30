<?php

namespace App\Http\Controllers;

use App\Models\Disponibilite;
use App\Models\HistoriqueFiabilite;
use App\Models\Notation;
use App\Services\FiabiliteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotationController extends Controller
{
    public function __construct(private FiabiliteService $fiabilite) {}

    /**
     * Liste des livraisons à noter (pour le producteur ou l'unité).
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isUnite()) {
            // L'unité note la qualité des lots reçus
            $dispos = Disponibilite::with(['produit', 'producteur.utilisateur'])
                ->whereHas('besoin', fn($q) => $q->where('unite_id', $user->unite->id))
                ->where('statut', 'confirmee')
                ->whereDoesntHave('notation', fn($q) => $q->whereNotNull('note_qualite'))
                ->orderBy('updated_at', 'desc')
                ->paginate(15);

            return view('notations.index', ['dispos' => $dispos, 'role' => 'unite']);
        }

        if ($user->isProducteur()) {
            // Le producteur note le paiement
            $dispos = Disponibilite::with(['produit', 'besoin.unite.utilisateur'])
                ->where('producteur_id', $user->producteur->id)
                ->where('statut', 'confirmee')
                ->whereDoesntHave('notation', fn($q) => $q->whereNotNull('note_paiement'))
                ->orderBy('updated_at', 'desc')
                ->paginate(15);

            return view('notations.index', ['dispos' => $dispos, 'role' => 'producteur']);
        }

        abort(403);
    }

    /**
     * Note la qualité du lot (unité).
     */
    public function noterQualite(Request $request, Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if (!$user->isUnite() || $disponibilite->besoin->unite_id !== $user->unite->id) {
            abort(403);
        }

        $data = $request->validate([
            'note_qualite' => ['required', 'in:bon,moyen,faible'],
        ]);

        DB::beginTransaction();
        try {
            $notation = Notation::firstOrCreate(
                ['disponibilite_id' => $disponibilite->id]
            );
            $notation->update(['note_qualite' => $data['note_qualite']]);

            // Historique qualité
            HistoriqueFiabilite::create([
                'type_acteur'      => 'producteur',
                'producteur_id'    => $disponibilite->producteur_id,
                'disponibilite_id' => $disponibilite->id,
                'type_evenement'   => 'livraison',
                'resultat'         => $data['note_qualite'] === 'bon' ? 'tenu' : 'partiellement_tenu',
                'commentaire'      => "Qualité : {$data['note_qualite']}",
            ]);

            DB::commit();

            return back()->with('success', 'Note qualité enregistrée.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()]);
        }
    }

    /**
     * Note le paiement (producteur).
     */
    public function noterPaiement(Request $request, Disponibilite $disponibilite)
    {
        $user = Auth::user();

        if (!$user->isProducteur() || $disponibilite->producteur_id !== $user->producteur->id) {
            abort(403);
        }

        $data = $request->validate([
            'note_paiement' => ['required', 'in:a_temps,en_retard'],
        ]);

        DB::beginTransaction();
        try {
            $notation = Notation::firstOrCreate(
                ['disponibilite_id' => $disponibilite->id]
            );
            $notation->update(['note_paiement' => $data['note_paiement']]);

            // Historique fiabilité unité (RG-017)
            HistoriqueFiabilite::create([
                'type_acteur'      => 'unite_transformation',
                'unite_id'         => $disponibilite->besoin->unite_id,
                'disponibilite_id' => $disponibilite->id,
                'type_evenement'   => 'paiement',
                'resultat'         => $data['note_paiement'],
                'commentaire'      => "Paiement : {$data['note_paiement']}",
            ]);

            DB::commit();

            // Recalculer le score de l'unité
            $this->fiabilite->recalculerUnite($disponibilite->besoin->unite);

            return back()->with('success', 'Note de paiement enregistrée.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()]);
        }
    }
}