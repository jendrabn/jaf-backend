<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->routeIs('admin.messages.massDestroy')) {
            return [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:contact_messages,id'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Tidak ada pesan yang dipilih.',
            'ids.array' => 'Format data tidak valid.',
            'ids.min' => 'Pilih setidaknya satu pesan.',
            'ids.*.integer' => 'ID pesan harus berupa angka.',
            'ids.*.exists' => 'Pesan tidak ditemukan.',
        ];
    }
}
