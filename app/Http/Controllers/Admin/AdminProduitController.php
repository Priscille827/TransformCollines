<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use App\Models\Notification;
use App\Models\Produit;
use App\Models\PropositionProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminProduitController extends Controller
{
    /**
     * Liste des produits (catalogue complet).
     */
    public function index(Request $request)
    {
        $query = Produit::with(['categorie', 'createur'])
            ->withCount(['offres', 'propositions', 'producteurs', 'unites']);

        if ($request->filled('categorie_id')) {
            $query->where('categorie_id', $request->categorie_id);
        }
        if ($request->filled('type_produit')) {
            $query->where('type_produit', $request->type_produit);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('q')) {
            $query->where('nom', 'LIKE', '%' . $request->q . '%');
        }

        $produits   = $query->orderBy('nom')->paginate(30)->withQueryString();
        $categories = Categorie::orderBy('ordre')->get();

       $stats = [
    'total'       => Produit::count(),
    'actifs'      => Produit::where('statut', 'actif')->count(),
    'inactifs'    => Produit::where('actif', false)->count(),
    'en_attente'  => Produit::where('statut', 'en_attente')->count(),
    'bruts'       => Produit::where('type_produit', 'brut')->count(),
    'transformes' => Produit::where('type_produit', 'transforme')->count(),
    'intrants'    => Produit::where('type_produit', 'intrant')->count(),
    'materiels'   => Produit::where('type_produit', 'materiel')->count(),
];

        return view('admin.produits.index', compact('produits', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom'          => 'required|string|max:100|unique:produits,nom',
            'categorie_id' => 'required|exists:categories,id',
            'type_produit' => 'required|in:brut,transforme,intrant,materiel',
            'description'  => 'nullable|string|max:1000',
        ]);

        $data['actif']    = true;
        $data['statut']   = 'actif';
        $data['cree_par'] = Auth::id();

        Produit::create($data);

        return back()->with('success', 'Produit créé.');
    }

    public function update(Request $request, Produit $produit)
    {
        $data = $request->validate([
            'nom'          => 'required|string|max:100|unique:produits,nom,' . $produit->id,
            'categorie_id' => 'required|exists:categories,id',
            'type_produit' => 'required|in:brut,transforme,intrant,materiel',
            'description'  => 'nullable|string|max:1000',
        ]);

        $produit->update($data);

        return back()->with('success', 'Produit mis à jour.');
    }

    public function toggleActif(Produit $produit)
    {
        $produit->update(['actif' => !$produit->actif]);
        $statut = $produit->actif ? 'activé' : 'désactivé';

        return back()->with('success', "Produit « {$produit->nom} » {$statut}.");
    }

    public function destroy(Produit $produit)
    {
        if ($produit->offres()->where('statut', 'active')->exists()) {
            return back()->with('error', 'Impossible : des offres actives utilisent ce produit.');
        }

        $produit->update(['actif' => false]);

        return back()->with('success', "Produit « {$produit->nom} » désactivé.");
    }

    // ═══════════════════════════════════════════════════════════
    // PROPOSITIONS
    // ═══════════════════════════════════════════════════════════

    /**
     * Liste des propositions à vérifier.
     */
    public function propositions(Request $request)
    {
        $query = PropositionProduit::with(['proposeur.commune', 'produit']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        } else {
            $query->where('statut', 'a_verifier');
        }

        if ($request->filled('role')) {
            $query->where('propose_par_role', $request->role);
        }

        $propositions = $query->latest()->paginate(20)->withQueryString();

        // Traitées récemment
        $traitees = PropositionProduit::with(['proposeur', 'produit', 'traitePar'])
            ->whereIn('statut', ['valide', 'refuse'])
            ->latest('traite_le')
            ->take(10)
            ->get();

        // Stats
        $stats = [
            'a_verifier' => PropositionProduit::where('statut', 'a_verifier')->count(),
            'validees'   => PropositionProduit::where('statut', 'valide')->count(),
            'refusees'   => PropositionProduit::where('statut', 'refuse')->count(),
            'producteur' => PropositionProduit::where('propose_par_role', 'producteur')->count(),
            'unite'      => PropositionProduit::where('propose_par_role', 'unite_transformation')->count(),
        ];

        $categories = Categorie::orderBy('ordre')->get();

        return view('admin.propositions.index', compact('propositions', 'traitees', 'stats', 'categories'));
    }

    /**
     * Valider une proposition → créer le produit.
     */
    public function validerProposition(Request $request, PropositionProduit $proposition)
    {
        $data = $request->validate([
            'nom_corrige'  => 'required|string|max:100|unique:produits,nom',
            'categorie_id' => 'required|exists:categories,id',
            'type_produit' => 'required|in:brut,transforme,intrant,materiel',
        ], [
            'nom_corrige.unique' => 'Ce nom de produit existe déjà.',
        ]);

        DB::beginTransaction();
        try {
            $produit = Produit::create([
                'nom'          => $data['nom_corrige'],
                'categorie_id' => $data['categorie_id'],
                'type_produit' => $data['type_produit'],
                'actif'        => true,
                'statut'       => 'actif',
                'cree_par'     => Auth::id(),
            ]);

            $proposition->update([
                'statut'       => 'valide',
                'nom_corrige'  => $data['nom_corrige'],
                'produit_id'   => $produit->id,
                'traite_par'   => Auth::id(),
                'traite_le'    => now(),
            ]);

            // Notifier le proposeur
            Notification::create([
                'utilisateur_id' => $proposition->propose_par,
                'code'           => 'N-04',
                'titre'          => 'Produit validé',
                'canal'          => 'web',
                'contenu'        => "Votre proposition « {$proposition->nom_saisi} » a été validée sous « {$data['nom_corrige']} ».",
                'url'            => route('produits.choisir'),
                'lu'             => false,
                'created_at'     => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Proposition validée, produit ajouté au catalogue.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    /**
     * Refuser une proposition.
     */
    public function refuserProposition(Request $request, PropositionProduit $proposition)
    {
        $data = $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $proposition->update([
            'statut'      => 'refuse',
            'motif_refus' => $data['motif_refus'],
            'traite_par'  => Auth::id(),
            'traite_le'   => now(),
        ]);

        Notification::create([
            'utilisateur_id' => $proposition->propose_par,
            'code'           => 'N-04',
            'titre'          => 'Produit refusé',
            'canal'          => 'web',
            'contenu'        => "Votre proposition « {$proposition->nom_saisi} » a été refusée. Motif : {$data['motif_refus']}",
            'url'            => route('produits.choisir'),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return back()->with('success', 'Proposition refusée.');
    }
}