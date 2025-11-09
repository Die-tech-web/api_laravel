<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'compte_id' => 'required|exists:comptes,id',
            'type' => 'required|in:DEPOT,RETRAIT,TRANSFERT',
            'montant' => 'required|numeric|min:0.01',
            'devise' => 'required|string|size:3',
            'description' => 'nullable|string|max:255',
            'validation_automatique' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'compte_id.required' => 'L\'identifiant du compte est requis',
            'compte_id.exists' => 'Le compte spécifié n\'existe pas',
            'type.required' => 'Le type de transaction est requis',
            'type.in' => 'Le type de transaction doit être DEPOT, RETRAIT ou TRANSFERT',
            'montant.required' => 'Le montant est requis',
            'montant.numeric' => 'Le montant doit être un nombre',
            'montant.min' => 'Le montant doit être supérieur à 0',
            'devise.required' => 'La devise est requise',
            'devise.size' => 'La devise doit contenir exactement 3 caractères',
            'description.max' => 'La description ne peut pas dépasser 255 caractères',
        ];
    }
}
