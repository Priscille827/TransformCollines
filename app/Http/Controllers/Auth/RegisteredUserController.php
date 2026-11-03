<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Commune;
use App\Models\Notification;
use App\Models\Producteur;
use App\Models\UniteTransformation;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Affiche le formulaire selon le rôle choisi.
     */
    public function create(Request $request)
    {
        $role = $request->input('role');

        // Si aucun rôle → écran de choix
        if (!in_array($role, ['population', 'producteur', 'unite_transformation'])) {
            return view('auth.choose-role');
        }

        return view('auth.register', [
            'role'     => $role,
            'communes' => Commune::orderBy('nom')->get(),
        ]);
    }

    /**
     * Traite l'inscription.
     */
    public function store(Request $request)
    {
        $role = $request->input('type_compte');

        // Règles de validation selon le rôle
        $rules = $this->rulesPour($role);
        $data  = $request->validate($rules, $this->messages());

        // Statut de validation
        $statutValidation = $role === 'population' ? 'valide' : 'en_attente';

        DB::beginTransaction();
        try {
            // Créer l'utilisateur
            $utilisateur = Utilisateur::create([
                'nom'               => $data['nom'],
                'email'             => $data['email'] ?? null,
                'nom_responsable'   => $data['nom_responsable'] ?? null,
                'telephone'         => $data['telephone'],
                'whatsapp'          => $data['whatsapp'] ?? null,
                'npi'               => $data['npi'] ?? null,
                'rccm'              => $data['rccm'] ?? null,
                'ifu'               => $data['ifu'] ?? null,
                'type_compte'       => $role,
                'commune_id'        => $data['commune_id'] ?? Commune::first()->id,
                'village'           => $data['village'] ?? null,
                'statut_compte'     => 'actif',
                'statut_validation' => $statutValidation,
                'valide_le'         => $statutValidation === 'valide' ? now() : null,
                'mot_de_passe'      => Hash::make($data['mot_de_passe']),
            ]);

            // Créer le profil étendu
            if ($role === 'producteur') {
                Producteur::create(['utilisateur_id' => $utilisateur->id]);
            } elseif ($role === 'unite_transformation') {
                UniteTransformation::create(['utilisateur_id' => $utilisateur->id]);
            }

            // Notifier les admins (sauf pour Population)
            if ($role !== 'population') {
                $admins = Utilisateur::where('type_compte', 'admin')->get();
                foreach ($admins as $admin) {
                    Notification::create([
                        'utilisateur_id' => $admin->id,
                        'code'           => 'N-01',
                        'titre'          => 'Nouveau compte à valider',
                        'canal'          => 'web',
                        'contenu'        => "Nouveau compte " . $this->libelleRole($role) . " : {$utilisateur->nom}",
                        'url'            => route('admin.comptes.show', $utilisateur),
                        'lu'             => false,
                        'created_at'     => now(),
                    ]);
                }
            }

            DB::commit();
            Auth::login($utilisateur);

            // Redirection selon le rôle
            if ($role === 'population') {
                return redirect()->route('marketplace.index')
                    ->with('success', 'Bienvenue sur Transform\'Collines ! Votre compte est activé.');
            }

            // Producteur et Unité → choisir leurs produits
           return redirect()->route('produits.choisir')
    ->with('success', 'Compte créé. Sélectionnez vos produits puis fournissez votre document d\'identification.');
    
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['erreur' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Règles de validation selon le rôle.
     */
    private function rulesPour(?string $role): array
    {
        $base = [
            'nom'          => 'required|string|max:150',
            'mot_de_passe' => 'required|string|min:6|confirmed',
        ];

        return match ($role) {
            'population' => array_merge($base, [
                'email'     => 'required|email|max:150|unique:utilisateurs,email',
                'telephone' => 'required|string|max:20|unique:utilisateurs,telephone',
            ]),

            'producteur' => array_merge($base, [
                'telephone' => 'required|string|max:20|unique:utilisateurs,telephone',
                'whatsapp'  => 'nullable|string|max:20',
                'commune_id' => 'required|exists:communes,id',
                'village'   => 'nullable|string|max:150',
            ]),

            'unite_transformation' => array_merge($base, [
                'nom_responsable' => 'required|string|max:150',
                'telephone'       => 'required|string|max:20|unique:utilisateurs,telephone',
                'whatsapp'        => 'nullable|string|max:20',
                'commune_id'      => 'required|exists:communes,id',
                'rccm'            => 'nullable|string|max:50',
                'ifu'             => 'nullable|string|max:50',
            ]),

            default => $base,
        };
    }

    /**
     * Messages d'erreur personnalisés.
     */
    private function messages(): array
    {
        return [
            'nom.required'          => 'Le nom est obligatoire.',
            'email.required'        => 'L\'adresse e-mail est obligatoire.',
            'email.email'           => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique'          => 'Cette adresse e-mail est déjà utilisée.',
            'telephone.required'    => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique'      => 'Ce numéro de téléphone est déjà utilisé.',
            'commune_id.required'   => 'Sélectionnez votre commune.',
            'commune_id.exists'     => 'Cette commune n\'existe pas.',
            'nom_responsable.required' => 'Le nom du responsable est obligatoire.',
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
            'mot_de_passe.min'      => 'Le mot de passe doit contenir au moins 6 caractères.',
            'mot_de_passe.confirmed'=> 'Les deux mots de passe ne correspondent pas.',
        ];
    }

    /**
     * Libellé lisible d'un rôle.
     */
    private function libelleRole(string $role): string
    {
        return match ($role) {
            'producteur'           => 'Producteur',
            'unite_transformation' => 'Unité de transformation',
            'population'           => 'Population',
            default                => $role,
        };
    }
}