<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Produit;
use App\Models\PropositionProduit;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProduitController extends Controller
{
    /**
     * Affiche l'écran de gestion des produits selon le rôle.
     */
    public function choisir()
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403, 'Seuls les producteurs et unités peuvent choisir des produits.');
        }

        $isProducteur = $user->isProducteur();

        // Type attendu selon le rôle
        $typeAttendu = $isProducteur ? 'brut' : 'transforme';

        // Produits déjà sélectionnés par l'utilisateur
        $dejaSelectionnes = $isProducteur
            ? $user->producteur->produits->pluck('id')->toArray()
            : $user->unite->produits->pluck('id')->toArray();

        // Liste des produits du bon type
        $produits = Produit::actifs()
            ->where('type_produit', $typeAttendu)
            ->with('categorie')
            ->orderBy('nom')
            ->get();

        // Préparer les données pour Alpine.js
        $produitsJson = $produits->map(function ($p) {
            return [
                'id'        => $p->id,
                'nom'       => $p->nom,
                'categorie' => $p->categorie->nom ?? 'Sans catégorie',
            ];
        })->values()->toArray();

        // Propositions en attente de cet utilisateur
        $propositionsEnAttente = PropositionProduit::where('propose_par', $user->id)
            ->where('statut', 'a_verifier')
            ->orderByDesc('created_at')
            ->get();

        // Config selon le rôle
        $config = [
            'titre'      => $isProducteur ? 'Que cultivez-vous ?' : 'Que produisez-vous ?',
            'sousTitre'  => $isProducteur
                ? 'Sélectionnez les produits bruts que vous cultivez.'
                : 'Sélectionnez les produits transformés que vous fabriquez.',
            'icone'      => $isProducteur ? 'sprout' : 'factory',
            'gradient'   => $isProducteur ? 'from-green-800 to-green-600' : 'from-amber-700 to-amber-500',
            'typeLabel'  => $isProducteur ? 'brut' : 'transformé',
        ];

        return view('produits.choisir', [
            'produits'              => $produits,
            'produitsJson'          => $produitsJson,
            'dejaSelectionnes'      => $dejaSelectionnes,
            'propositionsEnAttente' => $propositionsEnAttente,
            'config'                => $config,
            'isProducteur'          => $isProducteur,
        ]);
    }

    /**
     * Enregistre la sélection des produits.
     */
    public function enregistrer(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403);
        }

        $data = $request->validate([
            'produits'   => 'nullable|array',
            'produits.*' => 'exists:produits,id',
        ]);

        DB::beginTransaction();
        try {
            $produits = $data['produits'] ?? [];

            if ($user->isProducteur()) {
                $user->producteur->produits()->sync($produits);
            } else {
                $user->unite->produits()->sync($produits);
            }

            DB::commit();

            if ($user->isEnAttente() && !$user->aUnDocument()) {
                return redirect()->route('documents.index')
                    ->with('success', 'Produits enregistrés. Il ne reste plus qu\'à fournir votre document d\'identification.');
            }

            if ($user->isEnAttente()) {
                return redirect()->route('dashboard')
                    ->with('success', 'Produits enregistrés. Votre compte est en attente de validation.');
            }

            return redirect()->route('dashboard')
                ->with('success', 'Vos produits ont été mis à jour.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Proposer un nouveau produit.
     */
    public function proposer(Request $request)
    {
        $data = $request->validate([
            'nom_saisi' => 'required|string|max:100|min:3',
        ], [
            'nom_saisi.required' => 'Indiquez le nom du produit.',
            'nom_saisi.min'      => 'Le nom doit contenir au moins 3 caractères.',
        ]);

        $user = Auth::user();

        // Vérifier si le produit existe déjà dans le catalogue
        $existe = Produit::whereRaw('LOWER(nom) = ?', [strtolower($data['nom_saisi'])])
            ->where('actif', true)
            ->first();

        if ($existe) {
            return response()->json([
                'success'  => false,
                'message'  => "Le produit « {$existe->nom} » existe déjà dans le catalogue.",
                'produit'  => [
                    'id'   => $existe->id,
                    'nom'  => $existe->nom,
                ],
            ]);
        }

        // Vérifier les doublons de proposition pour cet utilisateur
        $dejaPropose = PropositionProduit::where('propose_par', $user->id)
            ->where('statut', 'a_verifier')
            ->whereRaw('LOWER(nom_saisi) = ?', [strtolower($data['nom_saisi'])])
            ->exists();

        if ($dejaPropose) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà proposé ce produit.',
            ]);
        }

        // Limite : 10 propositions en attente max
        $nbPropositions = PropositionProduit::where('propose_par', $user->id)
            ->where('statut', 'a_verifier')
            ->count();

        if ($nbPropositions >= 10) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà 10 propositions en attente. Attendez leur traitement.',
            ]);
        }

        DB::beginTransaction();
        try {
            // Déterminer le type selon le rôle
            $typeProduit = $user->isProducteur() ? 'brut' : 'transforme';
            $role = $user->isProducteur() ? 'producteur' : 'unite_transformation';

            $proposition = PropositionProduit::create([
                'nom_saisi'         => $data['nom_saisi'],
                'type_produit'      => $typeProduit,
                'propose_par'       => $user->id,
                'propose_par_role'  => $role,
                'statut'            => 'a_verifier',
            ]);

            // Notifier tous les admins
            $admins = Utilisateur::where('type_compte', 'admin')->get();
            foreach ($admins as $admin) {
                Notification::create([
                    'utilisateur_id' => $admin->id,
                    'code'           => 'N-01',
                    'titre'          => 'Nouveau produit proposé',
                    'canal'          => 'web',
                    'contenu'        => "{$user->nom} propose le produit « {$data['nom_saisi']} » ({$typeProduit})",
                    'url'            => route('admin.propositions'),
                    'lu'             => false,
                    'created_at'     => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proposition envoyée. Un administrateur va la vérifier.',
                'proposition' => [
                    'id'        => $proposition->id,
                    'nom_saisi' => $proposition->nom_saisi,
                ],
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }
}