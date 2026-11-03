<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDisponibiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seuls les producteurs (et admin) peuvent déclarer une dispo
        return in_array($this->user()->type_compte, ['producteur', 'admin']);
    }

    public function rules(): array
    {
        return [
            'produit_id'         => ['required', 'exists:produits,id'],
            'quantite'           => ['required', 'numeric', 'min:1'],   // RG-008
            'date_disponibilite' => ['required', 'date', 'after_or_equal:today'],
            'besoin_id'          => ['nullable', 'exists:besoins,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'produit_id.required'         => 'Sélectionnez un produit.',
            'quantite.required'           => 'Indiquez la quantité disponible.',
            'quantite.min'                => 'La quantité doit être strictement positive (RG-008).',
            'date_disponibilite.required' => 'Indiquez la date de disponibilité.',
            'date_disponibilite.after_or_equal' => 'La date doit être aujourd\'hui ou plus tard.',
        ];
    }
}