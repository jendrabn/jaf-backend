<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class EwalletRequest extends FormRequest
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
        if ($this->routeIs('admin.ewallets.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'account_name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'account_username' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'phone' => [
                    'required',
                    'string',
                    'min:10',
                    'max:15',
                    'starts_with:08,62',
                ],
            ];
        } elseif ($this->routeIs('admin.ewallets.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'account_name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'account_username' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'phone' => [
                    'required',
                    'string',
                    'min:10',
                    'max:15',
                    'starts_with:08,62',
                ],
            ];
        } elseif ($this->routeIs('admin.ewallets.massDestroy')) {
            return [
                'ids' => [
                    'required',
                    'array',
                ],
                'ids.*' => [
                    'exists:ewallets,id',
                ],
            ];
        } else {
            return [];
        }
    }
}
