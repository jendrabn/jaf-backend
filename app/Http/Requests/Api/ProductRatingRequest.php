<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductRatingRequest extends FormRequest
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
        return [
            'ratings' => [
                'required',
                'array',
            ],
            'ratings.*.order_item_id' => [
                'required',
                'numeric',
                'exists:order_items,id',
            ],
            'ratings.*.rating' => [
                'required',
                'numeric',
                'min:1',
                'max:5',
            ],
            'ratings.*.comment' => [
                'nullable',
                'string',
                'min:3',
                'max:15000',
            ],
            'ratings.*.is_anonymous' => [
                'nullable',
                'boolean',
            ],
        ];
    }
}
