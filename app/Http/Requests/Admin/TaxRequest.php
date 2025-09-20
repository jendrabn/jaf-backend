<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        if ($this->routeIs('admin.taxes.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('taxes', 'name'),
                ],
                'rate' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
            ];
        }

        if ($this->routeIs('admin.taxes.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('taxes', 'name')->ignore($this->route('tax')?->id),
                ],
                'rate' => [
                    'required',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter the tax name.',
            'name.unique' => 'The tax name has already been taken.',
            'rate.required' => 'Please enter the tax rate.',
            'rate.max' => 'The rate may not be greater than 100%.',
        ];
    }
}
