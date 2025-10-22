<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @phpstan-type CampaignPayload array{
 *   name: string,
 *   subject: string,
 *   content: string,
 *   scheduled_at?: string|null,
 *   status?: string|null
 * }
 */
class CampaignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled via route middleware (permission:backoffice.access and granular permissions).
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date'],
            // Keep in sync with App\Enums\CampaignStatus cases.
            'status' => ['nullable', 'in:draft,sending,sent'],
        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama campaign wajib diisi.',
            'subject.required' => 'Subjek email wajib diisi.',
            'content.required' => 'Konten email wajib diisi.',
        ];
    }
}
