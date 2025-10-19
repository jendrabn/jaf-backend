<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BannerRequest extends FormRequest
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
        if ($this->routeIs('admin.banners.store')) {
            return [
                'image' => [
                    'required',
                ],
                'image_description' => [
                    'string',
                    'min:1',
                    'max:100',
                    'required',
                ],
                'url' => [
                    'string',
                    'min:1',
                    'max:255',
                    'nullable',
                ],
            ];
        } elseif ($this->routeIs('admin.banners.update')) {
            return [
                'image' => [
                    'required',
                ],
                'image_description' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                ],
                'url' => [
                    'nullable',
                    'string',
                    'min:1',
                    'max:255',
                ],
            ];
        } elseif ($this->routeIs('admin.banners.massDestroy')) {
            return [
                'ids' => 'required|array',
                'ids.*' => 'exists:banners,id',
            ];
        } else {
            return [];
        }
    }
}
