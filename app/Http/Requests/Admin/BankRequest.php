<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BankRequest extends FormRequest
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
        if ($this->routeIs('admin.banks.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'code' => [
                    'required',
                    'string',
                    'min:3',
                    'max:3',
                ],
                'account_name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'account_number' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                    'unique:banks',
                ],
            ];
        } elseif ($this->routeIs('admin.banks.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'code' => [
                    'required',
                    'string',
                    'min:3',
                    'max:3',
                ],
                'account_name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'account_number' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                    'unique:banks,account_number,'.$this->route('bank')->id,
                ],
            ];
        } elseif ($this->routeIs('admin.banks.massDestroy')) {
            return [
                'ids' => 'required|array',
                'ids.*' => 'exists:banks,id',
            ];
        } else {
            return [];
        }
    }
}
