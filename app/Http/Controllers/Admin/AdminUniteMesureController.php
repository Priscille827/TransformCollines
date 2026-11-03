<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UniteMesure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUniteMesureController extends Controller
{
    /**
     * Liste des unités avec 2 onglets (mesures / contenants).
     */
    public function index(Request $request)
    {
        $type = $request->input('type', 'mesure'); // 'mesure' ou 'contenant'

        $query = UniteMesure::withCount(['stocks', 'offres']);

        if (in_array($type, ['mesure', 'contenant'])) {
            $query->where('type', $type);
        }

        if ($request->filled('q')) {
            $query->where(function ($sq) use ($request) {
                $sq->where('nom', 'LIKE', '%' . $request->q . '%')
                   ->orWhere('code', 'LIKE', '%' . $request->q . '%');
            });
        }

        if ($request->filled('actif')) {
            $query->where('actif', $request->actif === '1');
        }

        $unites = $query->orderBy('est_reference', 'desc')
            ->orderBy('nom')
            ->paginate(30)
            ->withQueryString();

        // Stats par type
        $stats = [
            'total'           => UniteMesure::count(),
            'mesures_total'   => UniteMesure::where('type', 'mesure')->count(),
            'contenants_total'=> UniteMesure::where('type', 'contenant')->count(),
            'actives'         => UniteMesure::where('actif', true)->count(),
            'inactives'       => UniteMesure::where('actif', false)->count(),
            'reference'       => UniteMesure::where('est_reference', true)->count(),
        ];

        return view('admin.unites-mesure.index', compact('unites', 'stats', 'type'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'    => 'required|string|max:20|unique:unites_mesure,code|regex:/^[a-z0-9_]+$/',
            'nom'     => 'required|string|max:50',
            'symbole' => 'nullable|string|max:10',
            'type'    => 'required|in:mesure,contenant',
        ], [
            'code.required' => 'Le code est obligatoire.',
            'code.unique'   => 'Ce code est déjà utilisé.',
            'code.regex'    => 'Lettres minuscules, chiffres et underscores uniquement.',
            'nom.required'  => 'Le nom est obligatoire.',
        ]);

        $data['est_reference'] = false;
        $data['actif']         = true;

        UniteMesure::create($data);

        return back()->with('success', "Unité « {$data['nom']} » créée avec succès.");
    }

    public function update(Request $request, UniteMesure $unite)
    {
        $data = $request->validate([
            'nom'     => 'required|string|max:50',
            'symbole' => 'nullable|string|max:10',
        ]);

        $unite->update($data);

        return back()->with('success', "Unité « {$unite->nom} » mise à jour.");
    }

    public function toggleActif(UniteMesure $unite)
    {
        if ($unite->est_reference) {
            return back()->with('error', 'L\'unité de référence (kg) ne peut pas être désactivée.');
        }

        $unite->update(['actif' => !$unite->actif]);
        $statut = $unite->actif ? 'activée' : 'désactivée';

        return back()->with('success', "Unité « {$unite->nom} » {$statut}.");
    }

    public function destroy(UniteMesure $unite)
    {
        if ($unite->est_reference) {
            return back()->with('error', 'L\'unité de référence ne peut pas être supprimée.');
        }

        $usageStocks = $unite->stocks()->count();
        $usageOffres = $unite->offres()->count();

        if ($usageStocks > 0 || $usageOffres > 0) {
            return back()->with('error',
                "Impossible de supprimer « {$unite->nom} » : utilisée par " .
                "{$usageStocks} stock(s), {$usageOffres} offre(s). Désactivez-la plutôt."
            );
        }

        $nom = $unite->nom;
        $unite->delete();

        return back()->with('success', "Unité « {$nom} » supprimée.");
    }
}