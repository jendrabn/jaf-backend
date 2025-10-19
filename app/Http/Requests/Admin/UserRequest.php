<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
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
        if ($this->routeIs('admin.users.store')) {
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
                    'unique:users',
                ],
                'password' => [
                    'required',
                    'string',
                    Password::min(8)->mixedCase()->numbers(),
                    'max:30',
                ],
                'roles.*' => [
                    'string',
                ],
                'roles' => [
                    'required',
                    'array',
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'min:10',
                    'max:15',
                    'starts_with:62',
                ],
                'sex' => [
                    'nullable',
                    'integer',
                    'in:1,2',
                ],
                'birth_date' => [
                    'nullable',
                    'date',
                ],
            ];
        } elseif ($this->routeIs('admin.users.update')) {
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
                    'unique:users,email,'.$this->route('user')->id,
                ],
                'password' => [
                    'nullable',
                    'string',
                    Password::min(8)->mixedCase()->numbers(),
                    'max:30',
                ],
                'roles.*' => [
                    'string',
                ],
                'roles' => [
                    'required',
                    'array',
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'min:10',
                    'max:15',
                    'starts_with:62',
                ],
                'sex' => [
                    'nullable',
                    'integer',
                    'in:1,2',
                ],
                'birth_date' => [
                    'nullable',
                    'date',
                ],
            ];
        } elseif ($this->routeIs('admin.users.massDestroy')) {
            return [
                'ids' => ['required', 'array'],
                'ids.*' => ['integer', 'exists:users,id'],
            ];
        } else {
            return [];
        }

    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->routeIs('admin.users.update')) {
            if (! $this->filled('password')) {
                $this->request->remove('password');
            }
        }
    }
}
