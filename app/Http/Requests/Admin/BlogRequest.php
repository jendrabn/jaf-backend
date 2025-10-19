<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class BlogRequest extends FormRequest
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
        if ($this->routeIs('admin.blogs.store')) {
            return [
                'title' => [
                    'required',
                    'string',
                    'min:3',
                    'max:200',
                    'not_in:categories,tags',
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:blogs,slug',
                ],
                'content' => [
                    'required',
                    'string',
                    'min:3',
                ],
                'min_read' => [
                    'required',
                    'integer',
                ],
                'featured_image_description' => [
                    'nullable',
                    'string',
                    'min:3',
                    'max:500',
                ],
                'is_publish' => [
                    'required',
                    'boolean',
                ],
                'blog_category_id' => [
                    'required',
                    'numeric',
                    'exists:blog_categories,id',
                ],
                'user_id' => [
                    'required',
                    'numeric',
                    'exists:users,id',
                ],
                'tag_ids' => [
                    'nullable',
                    'array',
                ],
            ];
        } elseif ($this->routeIs('admin.blogs.update')) {
            return [
                'title' => [
                    'required',
                    'string',
                    'min:3',
                    'max:200',
                    'not_in:categories,tags',
                ],
                'slug' => [
                    'required',
                    'string',
                    'min:3',
                    'max:255',
                    'unique:blogs,slug,'.$this->id,
                ],
                'content' => [
                    'required',
                    'string',
                    'min:3',
                ],
                'min_read' => [
                    'required',
                    'integer',
                ],
                'featured_image_description' => [
                    'nullable',
                    'string',
                    'min:3',
                    'max:500',
                ],
                'is_publish' => [
                    'required',
                    'boolean',
                ],
                'blog_category_id' => [
                    'required',
                    'numeric',
                    'exists:blog_categories,id',
                ],
                'user_id' => [
                    'required',
                    'numeric',
                    'exists:users,id',
                ],
                'tag_ids' => [
                    'nullable',
                    'array',
                ],
            ];
        } elseif ($this->routeIs('admin.blogs.massDestroy')) {
            return [
                'ids' => [
                    'required',
                    'array',
                ],
                'ids.*' => [
                    'numeric',
                    'exists:blogs,id',
                ],
            ];
        } else {
            return [];
        }
    }

    public function prepareForValidation(): void
    {
        $this->whenHas('title', function ($value) {
            $this->merge(['slug' => Str::slug($value.'-'.Str::random(3))]);
        });

        $this->merge([
            'is_publish' => $this->boolean('is_publish'),
        ]);

        $this->whenHas('content', function ($value) {
            $minRead = round(str_word_count(strip_tags($value)) / 200);

            $this->merge([
                'min_read' => (int) max(1, $minRead),
            ]);
        });

    }
}
