<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Traite la connexion.
     */
    public function store(Request $request)
    {
        // Formater le numéro AVANT validation
        if ($request->filled('telephone')) {
            $request->merge(['telephone' => $this->formaterTelephone($request->telephone)]);
        }

        $request->validate([
            'telephone'    => 'required|string',
            'mot_de_passe' => 'required|string',
        ], [
            'telephone.required'    => 'Le numéro de téléphone est obligatoire.',
            'mot_de_passe.required' => 'Le mot de passe est obligatoire.',
        ]);

        $utilisateur = Utilisateur::where('telephone', $request->telephone)->first();

        if (!$utilisateur || !Hash::check($request->mot_de_passe, $utilisateur->mot_de_passe)) {
            return back()->withErrors([
                'telephone' => 'Numéro de téléphone ou mot de passe incorrect.',
            ])->withInput();
        }

        if ($utilisateur->statut_compte === 'suspendu') {
            return back()->withErrors([
                'telephone' => 'Ce compte est suspendu. Contactez l\'administrateur.',
            ]);
        }

        Auth::login($utilisateur, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended('/dashboard');
    }

    /**
     * Déconnexion.
     */
    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    /**
     * Nettoie et formate le numéro de téléphone au format béninois (+229...)
     */
    private function formaterTelephone(string $telephone): string
    {
        $chiffres = preg_replace('/[^0-9]/', '', $telephone);
        if (str_starts_with($chiffres, '229')) {
            $chiffres = substr($chiffres, 3);
        }
        return '+229' . $chiffres;
    }
}