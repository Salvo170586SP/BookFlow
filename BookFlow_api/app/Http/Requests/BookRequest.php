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

        if (is_array($authorIds)) {
            $this->merge([
                'author_ids' => array_values(array_filter(
                    $authorIds,
                    static fn ($authorId) => $authorId !== '',
                )),
            ]);
        }
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
            'publisher_id' => ['nullable', 'integer', 'exists:publishers,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'author_ids' => ['sometimes', 'array'],
            'author_ids.*' => ['required', 'integer', 'distinct', 'exists:authors,id'],
        ];
    }
}
