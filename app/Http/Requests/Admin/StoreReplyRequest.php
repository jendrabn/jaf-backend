<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $subject = (string) $this->input('subject', '');
        $body = (string) $this->input('body', '');

        $this->merge([
            'subject' => trim(strip_tags($subject)),
            'body' => trim($body),
        ]);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:191'],
            'body' => ['required', 'string', 'min:3'],
        ];
    }
}
