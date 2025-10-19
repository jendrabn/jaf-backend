<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
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
                Rule::unique('users', 'email'),
            ],
            'password' => [
                'required',
                'string', Password::min(8)->mixedCase()->numbers(),
                'max:30',
                'confirmed',
            ],
        ];
    }
}
