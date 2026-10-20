<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $authorIds = $this->input('author_ids');
        $customNames = [];

        if (is_array($authorIds)) {
            $this->merge([
                'author_ids' => array_values(array_filter(
                    $authorIds,
                    static fn ($authorId) => $authorId !== '',
                )),
            ]);
        }

        foreach (['new_author_name', 'new_publisher_name'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $customNames[$field] = trim($value) ?: null;
            }
        }

        $this->merge($customNames);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'title' => ['required', 'string', 'max:255'],
            'isbn' => [
                'required',
                'string',
                'max:17',
                Rule::unique('books', 'isbn')->ignore($this->route('book')),
            ],
            'description' => ['nullable', 'string'],
            'published_year' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'publisher_id' => [
                'nullable',
                'integer',
                'exists:publishers,id',
                'prohibits:new_publisher_name',
            ],
            'new_publisher_name' => [
                'nullable',
                'string',
                'max:255',
                'unique:publishers,name',
                'prohibits:publisher_id',
            ],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'author_ids' => ['sometimes', 'array'],
            'author_ids.*' => ['required', 'integer', 'distinct', 'exists:authors,id'],
            'new_author_name' => ['nullable', 'string', 'max:255', 'unique:authors,name'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'new_author_name.unique' => 'Questo autore esiste già. Selezionalo dall’elenco.',
            'new_publisher_name.unique' => 'Questo editore esiste già. Selezionalo dall’elenco.',
            'publisher_id.prohibits' => 'Seleziona un editore dall’elenco oppure inseriscine uno nuovo.',
            'new_publisher_name.prohibits' => 'Seleziona un editore dall’elenco oppure inseriscine uno nuovo.',
        ];
    }
}
