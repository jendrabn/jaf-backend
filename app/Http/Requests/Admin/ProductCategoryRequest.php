<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ProductCategoryRequest extends FormRequest
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
        if ($this->routeIs('admin.product-categories.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'unique:product_categories,name',
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                ],
            ];
        } elseif ($this->routeIs('admin.product-categories.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:1',
                    'max:100',
                    'unique:product_categories,name,'.$this->route('product_category')->id,
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:1',
                    'max:255',
                    'unique:product_categories,slug,'.$this->route('product_category')->id,
                ],
            ];
        } elseif ($this->routeIs('admin.product-categories.massDestroy')) {
            return [
                'ids' => [
                    'required',
                    'array',
                ],
                'ids.*' => [
                    'integer',
                    'exists:product_categories,id',
                ],
            ];
        } else {
            return [];
        }
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'slug' => Str::slug($this->name),
        ]);
    }
}
