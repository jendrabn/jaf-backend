<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1', 'max:100'],
            'size' => ['sometimes', 'integer', 'min:1', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'q.required' => 'Query parameter q is required.',
            'q.string' => 'Query must be a string.',
            'q.min' => 'Query must be at least :min character.',
            'q.max' => 'Query may not be greater than :max characters.',
            'size.integer' => 'Size must be an integer.',
            'size.min' => 'Size must be at least :min.',
            'size.max' => 'Size may not be greater than :max.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $q = (string) ($this->input('q') ?? '');
        $this->merge([
            'q' => trim($q),
        ]);
    }
}
