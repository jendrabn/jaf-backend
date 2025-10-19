<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ProductBrandRequest extends FormRequest
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
        if ($this->routeIs('admin.product-brands.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'unique:product_brands,name',
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                    'unique:product_brands,slug',
                ],
            ];
        } elseif ($this->routeIs('admin.product-brands.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'unique:product_brands,name,'.$this->route('product_brand')->id,
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                    'unique:product_brands,slug,'.$this->id,
                ],
            ];
        } elseif ($this->routeIs('admin.product-brands.massDestroy')) {
            return [
                'ids' => [
                    'required',
                    'array',
                ],
                'ids.*' => [
                    'integer',
                    'exists:product_brands,id',
                ],
            ];
        } else {
            return [];
        }
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => str()->slug($this->name),
        ]);
    }
}
