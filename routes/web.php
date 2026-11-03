<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BesoinController;
use App\Http\Controllers\CarteController;
use App\Http\Controllers\DisponibiliteController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OffreController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReceptionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCompteController;
use App\Http\Controllers\Admin\AdminProduitController;
use App\Http\Controllers\OffrePhotoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\CommandesRecuesController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminUniteMesureController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuiviController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PaiementController;
use App\Http\Controllers\Admin\AdminPayoutController;
use App\Http\Controllers\ChatController;


Route::post('/api/kkiapay/webhook', [PaiementController::class, 'webhook'])->name('kkiapay.webhook');
// dans le groupe middleware('auth') :
Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Comptes
    Route::get('/comptes', [AdminCompteController::class, 'index'])->name('comptes');
    Route::get('/comptes/{compte}', [AdminCompteController::class, 'show'])->name('comptes.show');
    Route::post('/comptes/{compte}/valider', [AdminCompteController::class, 'valider'])->name('comptes.valider');
    Route::post('/comptes/{compte}/refuser', [AdminCompteController::class, 'refuser'])->name('comptes.refuser');
    Route::post('/comptes/{compte}/suspendre', [AdminCompteController::class, 'suspendre'])->name('comptes.suspendre');
    Route::post('/comptes/{compte}/reactiver', [AdminCompteController::class, 'reactiver'])->name('comptes.reactiver');

    // Produits
    Route::get('/produits', [AdminProduitController::class, 'index'])->name('produits');
    Route::post('/produits', [AdminProduitController::class, 'store'])->name('produits.store');
    Route::patch('/produits/{produit}', [AdminProduitController::class, 'update'])->name('produits.update');
    Route::delete('/produits/{produit}', [AdminProduitController::class, 'destroy'])->name('produits.destroy');
    Route::patch('/produits/{produit}/toggle', [AdminProduitController::class, 'toggleActif'])->name('produits.toggle');
    // Propositions
    Route::get('/propositions', [AdminProduitController::class, 'propositions'])->name('propositions');
    Route::post('/propositions/{proposition}/valider', [AdminProduitController::class, 'validerProposition'])->name('propositions.valider');
    Route::post('/propositions/{proposition}/refuser', [AdminProduitController::class, 'refuserProposition'])->name('propositions.refuser');

    Route::get('/unites-mesure', [AdminUniteMesureController::class, 'index'])->name('unites-mesure');
Route::post('/unites-mesure', [AdminUniteMesureController::class, 'store'])->name('unites-mesure.store');
Route::patch('/unites-mesure/{unite}', [AdminUniteMesureController::class, 'update'])->name('unites-mesure.update');
Route::patch('/unites-mesure/{unite}/toggle', [AdminUniteMesureController::class, 'toggleActif'])->name('unites-mesure.toggle');
Route::delete('/unites-mesure/{unite}', [AdminUniteMesureController::class, 'destroy'])->name('unites-mesure.destroy');
    
Route::post('/comptes/{utilisateur}/verifier-document', [DocumentController::class, 'verifier'])->name('comptes.verifier-document');
Route::post('/comptes/{utilisateur}/rejeter-document', [DocumentController::class, 'rejeter'])->name('comptes.rejeter-document');

Route::get('/payouts', [AdminPayoutController::class, 'index'])->name('payouts');
Route::get('/payouts/historique', [AdminPayoutController::class, 'historique'])->name('payouts.historique');
Route::get('/payouts/{payout}', [AdminPayoutController::class, 'show'])->name('payouts.show');
Route::post('/payouts/{payout}/marquer-paye', [AdminPayoutController::class, 'marquerPaye'])->name('payouts.marquer-paye');

});
/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => view('welcome'))->name('home');

// Marketplace public — accessible sans compte
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/offre/{offre}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/marketplace/vendeur/{type}/{id}', [MarketplaceController::class, 'vendeur'])->name('marketplace.vendeur');

/*
|--------------------------------------------------------------------------
| Auth — invités
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Auth — connectés
|--------------------------------------------------------------------------
*/


Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Notifications
   

Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('/notifications/{notification}/marquer-lu', [NotificationController::class, 'marquerLu'])->name('notifications.lu');
Route::post('/notifications/tout-marquer-lu', [NotificationController::class, 'toutMarquerLu'])->name('notifications.tout-lu');
Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // ─── PANIER ─────────────────────────────────────────────
    Route::get('/panier', [PanierController::class, 'index'])->name('panier.index');
    Route::post('/panier/offre/{offre}', [PanierController::class, 'ajouter'])->name('panier.ajouter');
    Route::patch('/panier/item/{item}', [PanierController::class, 'modifier'])->name('panier.modifier');
    Route::delete('/panier/item/{item}', [PanierController::class, 'retirer'])->name('panier.retirer');

    // ─── MES OFFRES (vendeur) ───────────────────────────────
    Route::resource('offres', OffreController::class);

    Route::get('/mes-produits', function () {
    return view('produits.choisir');
})->name('produits.choisir');

Route::get('/mes-produits', [ProduitController::class, 'choisir'])->name('produits.choisir');
Route::post('/mes-produits', [ProduitController::class, 'enregistrer'])->name('produits.enregistrer');
Route::post('/mes-produits/proposer', [ProduitController::class, 'proposer'])->name('produits.proposer');

    // ─── BESOINS ────────────────────────────────────────────
    Route::get('/besoins/actifs', [BesoinController::class, 'publics'])->name('besoins.publics');
    Route::resource('besoins', BesoinController::class)->except(['edit', 'update', 'destroy']);

    // ─── DISPONIBILITÉS ─────────────────────────────────────
    Route::get('/disponibilites', [DisponibiliteController::class, 'index'])->name('disponibilites.index');
    Route::get('/disponibilites/creer', [DisponibiliteController::class, 'create'])->name('disponibilites.create');
    Route::post('/disponibilites', [DisponibiliteController::class, 'store'])->name('disponibilites.store');
    Route::delete('/disponibilites/{disponibilite}', [DisponibiliteController::class, 'destroy'])->name('disponibilites.destroy');

    // ─── RÉCEPTIONS ─────────────────────────────────────────
    Route::get('/receptions', [ReceptionController::class, 'index'])->name('receptions.index');
    Route::post('/receptions/{disponibilite}/refuser', [ReceptionController::class, 'refuser'])->name('receptions.refuser');    
    Route::get('/receptions/historique', [ReceptionController::class, 'historique'])->name('receptions.historique');
    Route::post('/receptions/{disponibilite}/accepter-payer', [ReceptionController::class, 'accepterEtPayer'])->name('receptions.accepter-payer');
    // ─── CARTE ──────────────────────────────────────────────
    Route::get('/carte', [CarteController::class, 'index'])->name('carte');
    Route::get('/api/carte/data', [CarteController::class, 'data'])->name('carte.data');

   Route::post('/offres/{offre}/photos', [OffrePhotoController::class, 'store'])->name('offres.photos.store');
Route::delete('/offres/photos/{photo}', [OffrePhotoController::class, 'destroy'])->name('offres.photos.destroy');
Route::post('/offres/photos/{photo}/principale', [OffrePhotoController::class, 'definirPrincipale'])->name('offres.photos.principale');

Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
Route::get('/stocks/creer', [StockController::class, 'create'])->name('stocks.create');
Route::post('/stocks', [StockController::class, 'store'])->name('stocks.store');
Route::get('/stocks/{stock}/modifier', [StockController::class, 'edit'])->name('stocks.edit');
Route::patch('/stocks/{stock}', [StockController::class, 'update'])->name('stocks.update');
Route::delete('/stocks/{stock}', [StockController::class, 'destroy'])->name('stocks.destroy');

// ─── CHECKOUT / COMMANDES (acheteur) ────────────────────────
Route::get('/commander', [CommandeController::class, 'checkout'])->name('commandes.checkout');
Route::post('/commandes', [CommandeController::class, 'store'])->name('commandes.store');
Route::get('/mes-commandes', [CommandeController::class, 'index'])->name('commandes.index');
Route::get('/commandes/{commande}', [CommandeController::class, 'show'])->name('commandes.show');
Route::post('/commandes/{commande}/annuler', [CommandeController::class, 'annuler'])->name('commandes.annuler');
Route::post('/commandes/{commande}/confirmer-reception', [CommandeController::class, 'confirmerReception'])->name('commandes.confirmer-reception');

// ─── COMMANDES REÇUES (vendeur) ─────────────────────────────
Route::get('/commandes-recues', [CommandesRecuesController::class, 'index'])->name('commandes.recues.index');
Route::get('/commandes-recues/{commande}', [CommandesRecuesController::class, 'show'])->name('commandes.recues.show');
Route::post('/commandes-recues/{commande}/accepter', [CommandesRecuesController::class, 'accepter'])->name('commandes.recues.accepter');
Route::post('/commandes-recues/{commande}/refuser', [CommandesRecuesController::class, 'refuser'])->name('commandes.recues.refuser');
Route::post('/commandes-recues/{commande}/statut', [CommandesRecuesController::class, 'changerStatut'])->name('commandes.recues.statut');
Route::post('/commandes-recues/{commande}/confirmer-livraison', [CommandesRecuesController::class, 'confirmerLivraison'])->name('commandes.recues.confirmer-livraison');

Route::get('/suivi', [SuiviController::class, 'index'])->name('suivi.index');
// Upload documents
Route::get('/mon-dossier', [DocumentController::class, 'index'])->name('documents.index');
Route::post('/mon-dossier', [DocumentController::class, 'store'])->name('documents.store');

Route::get('/paiement/callback', [PaiementController::class, 'callback'])->name('paiement.callback');
Route::get('/paiement/{commande}', [PaiementController::class, 'payer'])->name('paiement.checkout');
Route::get('/paiement/{commande}/succes', [PaiementController::class, 'succes'])->name('paiement.succes');
Route::get('/paiement/{commande}/echec', [PaiementController::class, 'echec'])->name('paiement.echec');

// Vendeur
Route::get('/suivi/payouts', [SuiviController::class, 'payouts'])->name('suivi.payouts');
Route::get('/suivi/payouts/historique', [SuiviController::class, 'historiquePayouts'])->name('suivi.payouts.historique');
Route::post('/suivi/payouts/{payout}/confirmer', [SuiviController::class, 'confirmerPayout'])->name('suivi.payouts.confirmer');


// ─── CHAT ────────────────────────────────────────────────
Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
Route::get('/chat/recherche', [ChatController::class, 'rechercher'])->name('chat.rechercher');
Route::post('/chat/demarrer/{utilisateur}', [ChatController::class, 'demarrer'])->name('chat.demarrer');
Route::get('/chat/{conversation}', [ChatController::class, 'show'])->name('chat.show');
Route::post('/chat/{conversation}/envoyer', [ChatController::class, 'envoyer'])->name('chat.envoyer');
Route::get('/chat/{conversation}/nouveaux', [ChatController::class, 'nouveaux'])->name('chat.nouveaux');
});