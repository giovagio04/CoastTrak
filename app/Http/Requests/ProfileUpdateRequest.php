<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255', 'regex:/^[\p{L}\s\'\-]+$/u'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:filter',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'date_of_birth' => ['nullable', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d'), 'after_or_equal:' . now()->subYears(100)->format('Y-m-d')],
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048'], 
            'remove_profile_photo' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome e cognome è obbligatorio.',
            'name.string' => 'Il nome e cognome deve essere una stringa valida.',
            'name.min' => 'Il nome e cognome deve essere lungo almeno :min caratteri.',
            'name.max' => 'Il nome e cognome non può superare i :max caratteri.',
            'name.regex' => 'Il nome e cognome può contenere solo lettere, spazi, apostrofi o trattini.',
            
            'email.required' => 'L\'indirizzo email è obbligatorio.',
            'email.string' => 'L\'indirizzo email deve essere una stringa.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.max' => 'L\'email non può superare i :max caratteri.',
            'email.unique' => 'Questo indirizzo email è già registrato.',

            'date_of_birth.date'            => 'La data di nascita deve essere una data valida.',
            'date_of_birth.before_or_equal' => 'Devi avere almeno 18 anni per partecipare.',
            'date_of_birth.after_or_equal'  => 'La data di nascita non è valida (massimo 100 anni).',

            'bio.max' => 'La descrizione non può superare i :max caratteri.',

            'profile_photo.image' => 'Il file caricato deve essere un\'immagine.',
            'profile_photo.max' => 'L\'immagine del profilo non può superare i 2 MB.',
        ];
    }
}
