<?php

namespace App\Http\Controllers;

use App\Models\HistoriqueFiabilite;
use App\Models\Producteur;
use App\Models\UniteTransformation;
use Illuminate\Support\Facades\Auth;

class FiabiliteController extends Controller
{
    /**
     * Historique du compte connecté.
     */
    public function maFiabilite()
    {
        $user = Auth::user();

        if ($user->isProducteur()) {
            return $this->showProducteur($user->producteur, true);
        }

        if ($user->isUnite()) {
            return $this->showUnite($user->unite, true);
        }

        abort(403, 'Cette page est réservée aux producteurs et unités.');
    }

    /**
     * Historique public d'un producteur.
     */
    public function producteur(Producteur $producteur)
    {
        return $this->showProducteur($producteur, false);
    }

    /**
     * Historique public d'une unité.
     */
    public function unite(UniteTransformation $unite)
    {
        return $this->showUnite($unite, false);
    }

    /**
     * Vue producteur.
     */
    private function showProducteur(Producteur $producteur, bool $estMonProfil)
    {
        $producteur->load('utilisateur.commune');

        $historique = HistoriqueFiabilite::with(['disponibilite.produit', 'disponibilite.besoin.unite.utilisateur'])
            ->where('type_acteur', 'producteur')
            ->where('producteur_id', $producteur->id)
            ->where('type_evenement', 'livraison')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = $this->calculerStatsProducteur($historique);

        return view('fiabilite.producteur', compact('producteur', 'historique', 'stats', 'estMonProfil'));
    }

    /**
     * Vue unité.
     */
    private function showUnite(UniteTransformation $unite, bool $estMonProfil)
    {
        $unite->load('utilisateur.commune');

        $historique = HistoriqueFiabilite::with(['disponibilite.produit', 'disponibilite.producteur.utilisateur'])
            ->where('type_acteur', 'unite_transformation')
            ->where('unite_id', $unite->id)
            ->where('type_evenement', 'paiement')
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = $this->calculerStatsUnite($historique);

        return view('fiabilite.unite', compact('unite', 'historique', 'stats', 'estMonProfil'));
    }

    /**
     * Stats producteur.
     */
    private function calculerStatsProducteur($historique): array
    {
        $total = $historique->count();
        $tenus = $historique->where('resultat', 'tenu')->count();
        $partiels = $historique->where('resultat', 'partiellement_tenu')->count();
        $nonTenus = $historique->where('resultat', 'non_tenu')->count();

        $taux = $total > 0 ? round(($tenus / $total) * 100, 1) : 0;

        // Volume total livré
        $volumeLivre = $historique->sum(fn($h) => $h->disponibilite?->quantite_livree ?? 0);
        $volumePromis = $historique->sum(fn($h) => $h->disponibilite?->quantite ?? 0);

        return [
            'total'         => $total,
            'tenus'         => $tenus,
            'partiels'      => $partiels,
            'non_tenus'     => $nonTenus,
            'taux'          => $taux,
            'volume_livre'  => $volumeLivre,
            'volume_promis' => $volumePromis,
        ];
    }

    /**
     * Stats unité.
     */
    private function calculerStatsUnite($historique): array
    {
        $total = $historique->count();
        $aTemps = $historique->where('resultat', 'a_temps')->count();
        $enRetard = $historique->where('resultat', 'en_retard')->count();

        $taux = $total > 0 ? round(($aTemps / $total) * 100, 1) : 0;

        return [
            'total'     => $total,
            'a_temps'   => $aTemps,
            'en_retard' => $enRetard,
            'taux'      => $taux,
        ];
    }
}