<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserNotificationRequest extends FormRequest
{
    /**
     * Determine if user is authorized to make this request.
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
        if ($this->routeIs('admin.user-notifications.store')) {
            return [
                'user_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'send_to_all' => [
                    'boolean',
                ],
                'title' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'body' => [
                    'required',
                    'string',
                    'min:1',
                ],
                'category' => [
                    'required',
                    'string',
                    'in:transaction,account,promo,system',
                ],
                'level' => [
                    'required',
                    'string',
                    'in:info,warning,success,error',
                ],
                'url' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
                'icon' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'meta' => [
                    'nullable',
                    'array',
                ],
            ];
        } elseif ($this->routeIs('admin.user-notifications.update')) {
            return [
                'user_id' => [
                    'nullable',
                    'integer',
                    'exists:users,id',
                ],
                'title' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
                'body' => [
                    'required',
                    'string',
                    'min:1',
                ],
                'category' => [
                    'required',
                    'string',
                    'in:transaction,account,promo,system',
                ],
                'level' => [
                    'required',
                    'string',
                    'in:info,warning,success,error',
                ],
                'url' => [
                    'nullable',
                    'string',
                    'max:500',
                ],
                'icon' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'meta' => [
                    'nullable',
                    'array',
                ],
            ];
        } else {
            return [];
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.exists' => 'The selected user is invalid.',
            'title.required' => 'The title field is required.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'body.required' => 'The message field is required.',
            'category.required' => 'The category field is required.',
            'category.in' => 'The selected category is invalid.',
            'level.required' => 'The level field is required.',
            'level.in' => 'The selected level is invalid.',
            'url.max' => 'The URL may not be greater than 500 characters.',
            'icon.max' => 'The icon may not be greater than 100 characters.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        if ($this->routeIs('admin.user-notifications.store')) {
            // If send_to_all is checked, we don't need user_id
            if ($this->boolean('send_to_all')) {
                $this->merge([
                    'user_id' => null,
                ]);
            }
        }
    }
}
