<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CopyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // L'autorizzazione al ruolo admin viene verificata nell'endpoint addCopy.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Limita a una quantità intera positiva, con un massimo per richiesta.
            'quantity' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }
}
