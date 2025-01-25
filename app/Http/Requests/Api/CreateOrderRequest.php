<?php

namespace App\Http\Requests\Api;

use App\Models\Shipping;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrderRequest extends FormRequest
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
            'cart_ids' => [
                'required',
                'array',
            ],
            'cart_ids.*' => [
                'required',
                'integer',
                Rule::exists('carts', 'id')
                    ->where('user_id', $this->user()->id)
            ],
            'shipping_address.name' => [
                'required',
                'string',
                'min:1',
                'max:30',
            ],
            'shipping_address.phone' => [
                'required',
                'string',
                'starts_with:08,62,+62',
                'min:10',
                'max:15',
            ],
            'shipping_address.city_id' => [
                'required',
                'integer',
                'exists:cities,id',
            ],
            'shipping_address.district' => [
                'required',
                'string',
                'min:1',
                'max:100',
            ],
            'shipping_address.postal_code' => [
                'required',
                'string',
                'min:5',
                'max:5',
            ],
            'shipping_address.address' => [
                'required',
                'string',
                'min:1',
                'max:255',
            ],
            'shipping_courier' => [
                'required',
                'string',
                Rule::in(Shipping::COURIERS)
            ],
            'shipping_service' => [
                'required',
                'string'
            ],
            'payment_method' => [
                'required',
                'string',
                'in:bank,ewallet',
            ],
            'bank_id' => [
                'required_if:payment_method,bank',
                'integer',
                'exists:banks,id',
            ],
            'ewallet_id' => [
                'required_if:payment_method,ewallet',
                'integer',
                'exists:ewallets,id'
            ],
            'notes' => [
                'nullable',
                'string',
                'max:200',
            ]
        ];
    }
}
