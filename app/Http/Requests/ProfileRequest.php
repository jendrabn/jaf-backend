<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->routeIs('admin.profile.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                ],
                'email' => [
                    'required',
                    'string',
                    'email',
                    'min:1',
                    'max:255',
                    'unique:users,email,'.$this->user()->id,
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
                    'string',
                    'date',
                ],
                'avatar' => [
                    'nullable',
                    'image',
                    'mimes:jpeg,jpg,png',
                    'max:1024',
                ],
            ];
        } elseif ($this->routeIs('admin.profile.update-password')) {
            return [
                'current_password' => [
                    'required',
                    'string',
                    'current_password',
                ],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'confirmed',
                ],
            ];
        } else {
            return [

            ];
        }

    }
}
