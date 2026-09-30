<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Producteur;
use App\Models\Produit;
use App\Models\UniteTransformation;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register', [
            'communes' => Commune::orderBy('nom')->get(),
            'produits' => Produit::orderBy('nom')->get(),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nom'             => 'required|string|max:150',
            'telephone'       => 'required|string|unique:utilisateurs,telephone',
            'type_compte'     => 'required|in:producteur,unite_transformation',
            'commune_id'      => 'required|exists:communes,id',
            'village'         => 'nullable|string|max:150',
            'mot_de_passe'    => 'required|string|min:6|confirmed',
            'produits'        => 'required|array|min:1',
            'produits.*'      => 'exists:produits,id',
            'est_cooperative' => 'nullable|boolean',
            'nombre_membres'  => 'nullable|integer|min:1',
            'capacite'        => 'nullable|numeric|min:0',
        ], [
            'telephone.unique' => 'Ce numéro est déjà utilisé.',
            'produits.required' => 'Sélectionnez au moins un produit.',
        ]);

        DB::beginTransaction();
        try {
            $utilisateur = Utilisateur::create([
                'nom'           => $data['nom'],
                'telephone'     => $data['telephone'],
                'type_compte'   => $data['type_compte'],
                'commune_id'    => $data['commune_id'],
                'village'       => $data['village'] ?? null,
                'statut_compte' => 'actif',
                'mot_de_passe'  => Hash::make($data['mot_de_passe']),
            ]);

            if ($data['type_compte'] === 'producteur') {
                $producteur = Producteur::create([
                    'utilisateur_id'        => $utilisateur->id,
                    'est_cooperative'       => $request->boolean('est_cooperative'),
                    'nombre_membres_approx' => $request->integer('nombre_membres') ?: null,
                    'score_fiabilite'       => 'nouveau',
                ]);
                $producteur->produits()->attach($data['produits']);
            } else {
                $unite = UniteTransformation::create([
                    'utilisateur_id'             => $utilisateur->id,
                    'capacite_traitement_approx' => $data['capacite'] ?? null,
                    'score_fiabilite'            => 'nouveau',
                ]);
                $unite->produits()->attach($data['produits']);
            }

            DB::commit();

            Auth::login($utilisateur);
            return redirect('/dashboard')->with('success', 'Compte créé avec succès !');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => 'Erreur : ' . $e->getMessage()])->withInput();
        }
    }
}