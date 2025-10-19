<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BlogTagRequest extends FormRequest
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
        if ($this->routeIs('admin.blog-tags.store')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'unique:blog_tags,name',
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:blog_tags,slug',
                ],
            ];
        } elseif ($this->routeIs('admin.blog-tags.update')) {
            return [
                'name' => [
                    'required',
                    'string',
                    'min:3',
                    'max:50',
                    'unique:blog_tags,name,'.$this->id,
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:blog_tags,slug,'.$this->id,
                ],
            ];
        } elseif ($this->routeIs('admin.blog-tags.massDestroy')) {
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
