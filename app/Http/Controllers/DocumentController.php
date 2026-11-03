<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Utilisateur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Page d'upload du document.
     */
    public function index()
    {
        $user = Auth::user();

        // Seuls producteur et unité doivent uploader
        if (!$user->isProducteur() && !$user->isUnite()) {
            return redirect()->route('dashboard')
                ->with('info', 'Aucun document à fournir.');
        }

        // Déjà validé → pas besoin
        if ($user->isValide() && $user->aUnDocument()) {
            return redirect()->route('dashboard')
                ->with('info', 'Votre dossier est déjà complet.');
        }

        // Type de document attendu
        $typeDocument = $user->isProducteur() ? 'cip' : 'ifu';

        return view('documents.index', compact('user', 'typeDocument'));
    }

    /**
     * Upload du document.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isProducteur() && !$user->isUnite()) {
            abort(403);
        }

        $data = $request->validate([
            'document_type' => 'required|in:cip,passeport,ifu',
            'document'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'document.required' => 'Sélectionnez un fichier.',
            'document.file'     => 'Le fichier est invalide.',
            'document.mimes'    => 'Formats acceptés : JPG, PNG, PDF.',
            'document.max'      => 'Le fichier ne doit pas dépasser 5 Mo.',
        ]);

        // Vérifier la cohérence du type
        if ($user->isProducteur() && $data['document_type'] === 'ifu') {
            return back()->withErrors(['document_type' => 'Un producteur doit fournir une CIP ou un passeport.']);
        }
        if ($user->isUnite() && $data['document_type'] !== 'ifu') {
            return back()->withErrors(['document_type' => 'Une unité doit fournir son IFU.']);
        }

        // Supprimer l'ancien document si existe
        if ($user->document_chemin && Storage::disk('public')->exists($user->document_chemin)) {
            Storage::disk('public')->delete($user->document_chemin);
        }

        // Stocker le nouveau
        $chemin = $request->file('document')->store("documents/{$user->id}", 'public');

        $user->update([
            'document_type'    => $data['document_type'],
            'document_chemin'  => $chemin,
            'document_verifie' => false,
        ]);

        // Notifier tous les admins
        $admins = Utilisateur::where('type_compte', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'utilisateur_id' => $admin->id,
                'code'           => 'N-01',
                'titre'          => 'Nouveau dossier à examiner',
                'canal'          => 'web',
                'contenu'        => "{$user->nom} a fourni son document (" . strtoupper($data['document_type']) . ")",
                'url'            => route('admin.comptes.show', $user),
                'lu'             => false,
                'created_at'     => now(),
            ]);
        }

        return redirect()->route('dashboard')
            ->with('success', 'Document envoyé. Votre dossier sera examiné par un administrateur.');
    }

    /**
     * Admin : vérifier le document.
     */
    public function verifier(Utilisateur $utilisateur)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $utilisateur->update([
            'document_verifie' => true,
        ]);

        return back()->with('success', 'Document vérifié.');
    }

    /**
     * Admin : rejeter le document.
     */
    public function rejeter(Request $request, Utilisateur $utilisateur)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'motif_refus' => 'required|string|max:500',
        ]);

        $utilisateur->update([
            'document_verifie' => false,
            'motif_refus'      => $data['motif_refus'],
        ]);

        // Notifier l'utilisateur
        Notification::create([
            'utilisateur_id' => $utilisateur->id,
            'code'           => 'N-04',
            'titre'          => 'Document refusé',
            'canal'          => 'web',
            'contenu'        => "Votre document a été refusé. Motif : {$data['motif_refus']}",
            'url'            => route('documents.index'),
            'lu'             => false,
            'created_at'     => now(),
        ]);

        return back()->with('success', 'Document rejeté.');
    }
}