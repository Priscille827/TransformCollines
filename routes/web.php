<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BesoinController;
use App\Http\Controllers\DisponibiliteController;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\ReceptionController;
use App\Http\Controllers\NotationController;
use App\Http\Controllers\FiabiliteController;
use App\Http\Controllers\CooperativeVirtuelleController;
use App\Http\Controllers\IvrController;

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/api/ivr/menu', [IvrController::class, 'menu'])->name('ivr.menu');
Route::post('/api/ivr/choisir', [IvrController::class, 'choisir'])->name('ivr.choisir');
Route::post('/api/ivr/enregistrer', [IvrController::class, 'enregistrer'])->name('ivr.enregistrer');
Route::post('/api/sms/recevoir', [SmsController::class, 'recevoir'])->name('sms.recevoir');

/*
|--------------------------------------------------------------------------
| Auth — invités seulement
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'register']);
});

/*
|--------------------------------------------------------------------------
| Auth — utilisateurs connectés
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    // Déconnexion
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();

        $besoinsQuery = \App\Models\Besoin::query();
        $dispoQuery   = \App\Models\Disponibilite::query();

        if ($user->isUnite()) {
            $besoinsQuery->where('unite_id', $user->unite->id);
            $dispoQuery->whereHas('besoin.unite', fn($q) => $q->where('unite_id', $user->unite->id));
        } elseif ($user->isProducteur()) {
            $dispoQuery->where('producteur_id', $user->producteur->id);
        }

        $besoinsActifs = $besoinsQuery->clone()->whereIn('statut', ['actif', 'partiellement_couvert'])->count();
        $dispos        = $dispoQuery->clone()->count();
        $tauxMoyen     = round($besoinsQuery->clone()->avg('taux_couverture') ?? 0, 1);

        $besoinsRecents = \App\Models\Besoin::with(['produit', 'unite.utilisateur.commune'])
            ->whereIn('statut', ['actif', 'partiellement_couvert'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'stats' => [
                'besoins_actifs' => $besoinsActifs,
                'disponibilites' => $dispos,
                'taux_moyen'     => $tauxMoyen,
                'transactions'   => 0,
            ],
            'besoinsRecents' => $besoinsRecents,
        ]);
    })->name('dashboard');

    /*
    |----------------------------------------------------------------------
    | Routes placeholder (pages à venir)
    |----------------------------------------------------------------------
    */
  
    Route::resource('besoins', BesoinController::class)->except(['edit', 'update', 'destroy']);
Route::get('/besoins-actifs', [BesoinController::class, 'publics'])->name('besoins.publics');
  
Route::get('/disponibilites', [DisponibiliteController::class, 'index'])->name('disponibilites.index');
Route::get('/disponibilites/creer', [DisponibiliteController::class, 'create'])->name('disponibilites.create');
Route::post('/disponibilites', [DisponibiliteController::class, 'store'])->name('disponibilites.store');
Route::delete('/disponibilites/{disponibilite}', [DisponibiliteController::class, 'destroy'])->name('disponibilites.destroy');
   
Route::get('/notifications', function () {
    $notifs = auth()->user()->notifications()->latest('id')->paginate(20);
    return view('notifications.index', compact('notifs'));
})->name('notifications.index');

Route::post('/notifications/{id}/marquer-lu', function ($id) {
    $notif = auth()->user()->notifications()->findOrFail($id);
    $notif->update(['lu' => true]);
    return back();
})->name('notifications.lu');

Route::get('/carte', [CarteController::class, 'index'])->name('carte');
Route::get('/api/carte/data', [CarteController::class, 'data'])->name('carte.data');

    
Route::get('/sms/simuler', [SmsController::class, 'simuler'])->name('sms.simuler');
Route::get('/sms/journal', [SmsController::class, 'journal'])->name('sms.journal');

   Route::middleware('institution')->group(function () {
    Route::get('/institution', [InstitutionController::class, 'dashboard'])->name('institution.dashboard');
    Route::get('/institution/export', [InstitutionController::class, 'export'])->name('institution.export');
});

Route::get('/receptions', [ReceptionController::class, 'index'])->name('receptions.index');
Route::get('/receptions/{disponibilite}/confirmer', [ReceptionController::class, 'create'])->name('receptions.create');
Route::post('/receptions/{disponibilite}', [ReceptionController::class, 'store'])->name('receptions.store');

// Notations (producteur + unité)
Route::get('/notations', [NotationController::class, 'index'])->name('notations.index');
Route::post('/notations/{disponibilite}/qualite', [NotationController::class, 'noterQualite'])->name('notations.qualite');
Route::post('/notations/{disponibilite}/paiement', [NotationController::class, 'noterPaiement'])->name('notations.paiement');

Route::get('/ma-fiabilite', [FiabiliteController::class, 'maFiabilite'])->name('fiabilite.moi');
Route::get('/fiabilite/producteur/{producteur}', [FiabiliteController::class, 'producteur'])->name('fiabilite.producteur');
Route::get('/fiabilite/unite/{unite}', [FiabiliteController::class, 'unite'])->name('fiabilite.unite');

Route::get('/cooperative', [CooperativeVirtuelleController::class, 'index'])->name('cooperative.index');
Route::get('/cooperative/{besoin}/rejoindre', function ($besoin) {
    $b = \App\Models\Besoin::with(['produit', 'unite.utilisateur'])->findOrFail($besoin);
    return view('cooperative.rejoindre', ['besoin' => $b]);
})->name('cooperative.rejoindre.form');
Route::post('/cooperative/{besoin}/rejoindre', [CooperativeVirtuelleController::class, 'rejoindre'])->name('cooperative.rejoindre');
Route::get('/cooperative/mes-participations', [CooperativeVirtuelleController::class, 'mesParticipations'])->name('cooperative.mes');

Route::get('/ivr', [IvrController::class, 'index'])->name('ivr.simuler');

});
