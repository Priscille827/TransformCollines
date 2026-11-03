<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBesoinRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Seules les unités (et admin) peuvent publier un besoin
        return in_array($this->user()->type_compte, ['unite_transformation', 'admin']);
    }

    public function rules(): array
    {
        return [
            'produit_id'          => ['required', 'exists:produits,id'],
            'quantite_recherchee' => ['required', 'numeric', 'min:1'],   // RG-001
            'delai'               => ['required', 'date', 'after:today'], // RG-002
            'mode_zone'           => ['required', 'in:rayon,communes'],
            'rayon_km'            => ['nullable', 'numeric', 'min:1', 'max:500', 'required_if:mode_zone,rayon'],
            'communes'            => ['nullable', 'array', 'required_if:mode_zone,communes'],
            'communes.*'          => ['exists:communes,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'produit_id.required'          => 'Sélectionnez un produit.',
            'produit_id.exists'            => 'Ce produit n\'existe pas.',
            'quantite_recherchee.required' => 'Indiquez la quantité recherchée.',
            'quantite_recherchee.numeric'  => 'La quantité doit être un nombre.',
            'quantite_recherchee.min'      => 'La quantité doit être strictement positive (RG-001).',
            'delai.required'               => 'Indiquez une date limite.',
            'delai.after'                  => 'Le délai doit être postérieur à aujourd\'hui (RG-002).',
            'mode_zone.required'           => 'Choisissez un mode de zone de collecte.',
            'rayon_km.required_if'         => 'Indiquez un rayon en kilomètres.',
            'communes.required_if'         => 'Sélectionnez au moins une commune.',
        ];
    }
}