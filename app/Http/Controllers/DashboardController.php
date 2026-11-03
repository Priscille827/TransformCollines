<?php

namespace App\Http\Controllers;

use App\Models\Besoin;
use App\Models\Commande;
use App\Models\Disponibilite;
use App\Models\Offre;
use App\Models\PanierItem;
use App\Models\PropositionProduit;
use App\Models\Stock;
use App\Models\Utilisateur;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = match ($user->type_compte) {
            'producteur'           => $this->dashboardProducteur($user),
            'unite_transformation' => $this->dashboardUnite($user),
            'population'           => $this->dashboardPopulation($user),
            'admin'                => $this->dashboardAdmin($user),
            default                => [],
        };

        return view('dashboard', array_merge($data, ['user' => $user]));
    }

    /**
     * Dashboard Producteur.
     */
    private function dashboardProducteur($user): array
    {
        $producteurId = $user->producteur?->id;

        if (!$producteurId) {
            return ['error' => 'Profil producteur non trouvé.'];
        }

        // Stock
        $stocks = Stock::with('uniteMesure')
            ->where('producteur_id', $producteurId)
            ->whereRaw('quantite_disponible - quantite_reservee > 0')
            ->take(5)
            ->get();

        $totalKg = Stock::where('producteur_id', $producteurId)->get()->sum(fn($s) => $s->quantite_kg);

        // Disponibilités
        $dispos = Disponibilite::where('producteur_id', $producteurId);
        $disposActives = (clone $dispos)->whereIn('statut', ['declaree', 'associee'])->count();
        $disposConfirmees = (clone $dispos)->where('statut', 'confirmee')->count();

        // Offres
        $offres = Offre::where('producteur_id', $producteurId);
        $offresActives = (clone $offres)->where('statut', 'active')->count();
        $offresTotal = (clone $offres)->count();

        // Commandes reçues
        $commandesEnAttente = Commande::where('producteur_id', $producteurId)
            ->where('statut', 'en_attente')->count();

        $commandesEnCours = Commande::where('producteur_id', $producteurId)
            ->whereIn('statut', ['acceptee', 'en_preparation', 'expediee', 'disponible_retrait'])
            ->count();

        // Besoins actifs récents (pour lui)
        $besoinsProches = Besoin::with(['produit', 'unite.utilisateur.commune'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->whereHas('produit', function ($q) use ($producteurId) {
                $q->whereIn('id', function ($sq) use ($producteurId) {
                    $sq->select('produit_id')
                        ->from('producteur_produits')
                        ->where('producteur_id', $producteurId);
                });
            })
            ->orderBy('delai')
            ->take(3)
            ->get();

        return [
            'stocks'              => $stocks,
            'totalKg'             => $totalKg,
            'disposActives'       => $disposActives,
            'disposConfirmees'    => $disposConfirmees,
            'offresActives'       => $offresActives,
            'offresTotal'         => $offresTotal,
            'commandesEnAttente'  => $commandesEnAttente,
            'commandesEnCours'    => $commandesEnCours,
            'besoinsProches'      => $besoinsProches,
        ];
    }

    /**
     * Dashboard Unité.
     */
    private function dashboardUnite($user): array
    {
        $uniteId = $user->unite?->id;

        if (!$uniteId) {
            return ['error' => 'Profil unité non trouvé.'];
        }

        // Besoins publiés
        $besoins = Besoin::where('unite_id', $uniteId);
        $besoinsActifs = (clone $besoins)->whereIn('statut', ['actif', 'partiellement_couvert'])->count();
        $besoinsCouverts = (clone $besoins)->where('statut', 'couvert')->count();
        $tauxMoyen = round((clone $besoins)->whereIn('statut', ['actif', 'partiellement_couvert'])->avg('taux_couverture') ?? 0, 1);

        // Besoins récents
        $besoinsRecents = Besoin::with(['produit', 'communes'])
            ->where('unite_id', $uniteId)
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        // Réceptions à confirmer
        $receptionsEnAttente = Disponibilite::whereHas('besoin', fn($q) => $q->where('unite_id', $uniteId))
            ->where('statut', 'associee')
            ->count();

        // Stock produits finis
        $stocks = Stock::with('uniteMesure')
            ->where('unite_id', $uniteId)
            ->whereRaw('quantite_disponible - quantite_reservee > 0')
            ->take(5)
            ->get();

        // Offres
        $offresActives = Offre::where('unite_id', $uniteId)->where('statut', 'active')->count();

        // Commandes reçues
        $commandesEnAttente = Commande::whereHas('lignes.offre', fn($q) => $q->where('unite_id', $uniteId))
            ->where('statut', 'en_attente')
            ->count();

        return [
            'besoinsActifs'         => $besoinsActifs,
            'besoinsCouverts'       => $besoinsCouverts,
            'tauxMoyen'             => $tauxMoyen,
            'besoinsRecents'        => $besoinsRecents,
            'receptionsEnAttente'   => $receptionsEnAttente,
            'stocks'                => $stocks,
            'offresActives'         => $offresActives,
            'commandesEnAttente'    => $commandesEnAttente,
        ];
    }

    /**
     * Dashboard Population.
     */
    private function dashboardPopulation($user): array
    {
        // Commandes
        $commandes = Commande::where('acheteur_id', $user->id);
        $commandesEnCours = (clone $commandes)->whereIn('statut', ['en_attente', 'acceptee', 'en_preparation', 'expediee', 'disponible_retrait'])->count();
        $commandesTerminees = (clone $commandes)->whereIn('statut', ['livree', 'terminee'])->count();

        // Commandes récentes
        $commandesRecentes = Commande::with(['producteur.utilisateur', 'lignes.produit'])
            ->where('acheteur_id', $user->id)
            ->latest()
            ->take(3)
            ->get();

        // Panier
        $panierItems = PanierItem::where('utilisateur_id', $user->id)->count();

        return [
            'commandesEnCours'    => $commandesEnCours,
            'commandesTerminees'  => $commandesTerminees,
            'commandesRecentes'   => $commandesRecentes,
            'panierItems'         => $panierItems,
        ];
    }

    /**
     * Dashboard Admin.
     */
    private function dashboardAdmin($user): array
    {
        $stats = [
            'comptes_en_attente' => Utilisateur::where('statut_validation', 'en_attente')->count(),
            'comptes_total'      => Utilisateur::count(),
            'producteurs'        => Utilisateur::where('type_compte', 'producteur')->count(),
            'unites'             => Utilisateur::where('type_compte', 'unite_transformation')->count(),
            'population'         => Utilisateur::where('type_compte', 'population')->count(),

            'besoins_actifs'     => Besoin::whereIn('statut', ['actif', 'partiellement_couvert'])->count(),
            'offres_actives'     => Offre::where('statut', 'active')->count(),
            'commandes_total'    => Commande::count(),
            'commandes_attente'  => Commande::where('statut', 'en_attente')->count(),
            'propositions'       => PropositionProduit::where('statut', 'a_verifier')->count(),
        ];

        // Derniers comptes en attente
        $derniersComptes = Utilisateur::where('statut_validation', 'en_attente')
            ->with('commune')
            ->latest()
            ->take(5)
            ->get();

        // Dernières propositions
        $dernieresPropositions = PropositionProduit::with(['proposeur', 'produit'])
            ->where('statut', 'a_verifier')
            ->latest()
            ->take(5)
            ->get();

        return [
            'stats'                 => $stats,
            'derniersComptes'       => $derniersComptes,
            'dernieresPropositions' => $dernieresPropositions,
        ];
    }
}