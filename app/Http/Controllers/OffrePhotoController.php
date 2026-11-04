<?php

namespace App\Http\Controllers;

use App\Models\Offre;
use App\Models\OffrePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class OffrePhotoController extends Controller
{
    public function store(Request $request, Offre $offre)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $offre)) {
            abort(403);
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ], [
            'photo.required' => 'Sélectionnez une image.',
            'photo.image'    => 'Le fichier doit être une image.',
            'photo.mimes'    => 'Formats acceptés : JPG, PNG, WebP.',
            'photo.max'      => 'L\'image ne doit pas dépasser 3 Mo.',
        ]);

        $chemin = $request->file('photo')->store("offres/{$offre->id}");

        $estPremiere = $offre->photos()->count() === 0;

        OffrePhoto::create([
            'offre_id' => $offre->id,
            'chemin'   => $chemin,
            'ordre'    => $offre->photos()->count(),
        ]);

        if ($estPremiere) {
            $offre->update(['photo_principale' => $chemin]);
        }

        return back()->with('success', 'Photo ajoutée.');
    }

    public function destroy(OffrePhoto $photo)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $photo->offre)) {
            abort(403);
        }

        if (Storage::exists($photo->chemin)) {
        Storage::delete($photo->chemin);
        }

        $etaitPrincipale = $photo->offre->photo_principale === $photo->chemin;
        $offre = $photo->offre;
        $photo->delete();

        if ($etaitPrincipale) {
            $nouvelle = $offre->photos()->first();
            $offre->update(['photo_principale' => $nouvelle?->chemin]);
        }

        return back()->with('success', 'Photo supprimée.');
    }

    public function definirPrincipale(OffrePhoto $photo)
    {
        $user = Auth::user();

        if (!$this->peutModifier($user, $photo->offre)) {
            abort(403);
        }

        $photo->offre->update(['photo_principale' => $photo->chemin]);

        return back()->with('success', 'Photo principale mise à jour.');
    }

    private function peutModifier($user, Offre $offre): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->isProducteur() && $offre->producteur_id === $user->producteur->id) return true;
        if ($user->isUnite() && $offre->unite_id === $user->unite->id) return true;
        return false;
    }
}