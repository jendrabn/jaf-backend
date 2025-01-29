<?php

namespace App\Http\Requests\Api;

use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmPaymentRequest extends FormRequest
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
        $paymentMethod = $this->route('order')->invoice->payment->method;

        // Payment method bank
        if ($paymentMethod === Payment::METHOD_BANK) {
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
                'account_number' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ]
            ];
        } else if ($paymentMethod === Payment::METHOD_EWALLET) {
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
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ],
                'phone' => [
                    'required',
                    'string',
                    'min:1',
                    'max:50',
                ]
            ];
        } else {
            return [];
        }



    }
}
