<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
{
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'email' => [
                'required',
                'string',
                'email',
                'min:1',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->user()->id)
            ],
            'phone' => [
                'nullable',
                'string',
                'min:10',
                'max:15',
                'starts_with:08,62,+62',
            ],
            'sex' => [
                'nullable',
                'integer',
                Rule::in([1, 2])
            ],
            'birth_date' => [
                'nullable',
                'string',
                'date',
            ],
            'avatar' => [
                'nullable',
                'file',
                'max:1024',
                'mimes:jpg,jpeg,png',
            ]
        ];
    }
}
