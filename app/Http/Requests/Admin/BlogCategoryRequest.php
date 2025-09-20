<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlogCategoryRequest extends FormRequest
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
        if ($this->routeIs('admin.blog-categories.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'unique:blog_categories,name'
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:blog_categories,slug'
                ],
            ];
        } else if ($this->routeIs('admin.blog-categories.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50'
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:blog_categories,slug,' . $this->id
                ],
            ];
        } else if ($this->routeIs('admin.blog-categories.massDestroy')) {
            return [
                'ids' => [
                    'required',
                    'array',
                ],
            ];
        } else {
            return [];
        }
    }

    public function prepareForValidation(): void
    {
        $this->whenFilled('name', function ($value) {
            $this->merge(['slug' => str()->slug($value)]);
        });
    }
}
