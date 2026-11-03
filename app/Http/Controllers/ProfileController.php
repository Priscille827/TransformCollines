<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Commune;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Affiche la page de profil.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $communes = Commune::orderBy('nom')->get();

        // Propositions en attente
        $propositionsEnAttente = $user->propositionsProduits()
            ->where('statut', 'a_verifier')
            ->get();

        return view('profile.edit', [
            'user'                  => $user,
            'communes'              => $communes,
            'propositionsEnAttente' => $propositionsEnAttente,
        ]);
    }

    /**
     * Met à jour les informations du profil.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'nom'        => 'required|string|max:150',
            'email'      => 'nullable|email|max:150|unique:utilisateurs,email,' . $user->id,
            'whatsapp'   => 'nullable|string|max:20',
            'commune_id' => 'required|exists:communes,id',
            'village'    => 'nullable|string|max:150',
        ], [
            'nom.required'        => 'Le nom est obligatoire.',
            'email.email'         => 'L\'email n\'est pas valide.',
            'email.unique'        => 'Cet email est déjà utilisé.',
            'commune_id.required' => 'Sélectionnez une commune.',
        ]);

        $user->update($data);

        return back()->with('success', 'Vos informations ont été mises à jour.');
    }

    /**
     * Change le mot de passe.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Indiquez votre mot de passe actuel.',
            'password.required'         => 'Indiquez un nouveau mot de passe.',
            'password.min'              => 'Le mot de passe doit faire au moins 6 caractères.',
            'password.confirmed'        => 'Les deux mots de passe ne correspondent pas.',
        ]);

        $user = $request->user();

        if (!Hash::check($data['current_password'], $user->mot_de_passe)) {
            return back()->withErrors(['current_password' => 'Mot de passe actuel incorrect.']);
        }

        $user->update([
            'mot_de_passe' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Mot de passe mis à jour.');
    }

    /**
     * Suppression du compte.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->mot_de_passe)) {
            return back()->withErrors(['password' => 'Mot de passe incorrect.']);
        }

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}