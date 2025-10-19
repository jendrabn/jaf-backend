<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = (string) $this->input('name', '');
        $email = (string) $this->input('email', '');
        $phone = (string) $this->input('phone', '');
        $message = (string) $this->input('message', '');

        $this->merge([
            'name' => trim(strip_tags($name)),
            'email' => trim($email),
            'phone' => $phone !== '' ? trim(strip_tags($phone)) : null,
            'message' => trim(strip_tags($message)),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc,dns', 'max:191'],
            'phone' => ['nullable', 'string', 'max:30'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }
}
