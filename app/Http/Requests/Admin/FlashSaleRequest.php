<?php

namespace App\Http\Requests\Admin;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class FlashSaleRequest extends FormRequest
{
    protected ?int $currentId = null;

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
        if ($this->routeIs('admin.flash-sales.massDestroy')) {
            return [
                'ids' => ['required', 'array', 'min:1'],
                'ids.*' => ['integer', 'exists:flash_sales,id'],
            ];
        }

        $this->currentId = $this->route('flashSale')?->id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'start_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'end_at' => ['required', 'date_format:Y-m-d H:i:s', 'after:start_at'],
            'is_active' => ['nullable', 'boolean'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'distinct', 'exists:products,id'],
            'products.*.flash_price' => ['required', 'numeric', 'min:0'],
            'products.*.stock_flash' => ['required', 'integer', 'min:1'],
            'products.*.max_qty_per_user' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function withValidator(\Illuminate\Contracts\Validation\Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->routeIs('admin.flash-sales.massDestroy')) {
                return;
            }

            $startAt = $this->input('start_at');
            $endAt = $this->input('end_at');

            if (! $startAt || ! $endAt) {
                return;
            }

            $start = Carbon::parse($startAt);
            $end = Carbon::parse($endAt);
            $minStart = Carbon::now()->addMinutes(10);

            if ($start->lt($minStart)) {
                $validator->errors()->add('start_at', 'Start time must be at least 10 minutes from now.');
            }

            $overlaps = \App\Models\FlashSale::query()
                ->when($this->currentId, fn ($query, $id) => $query->where('id', '!=', $id))
                ->where(function ($query) use ($startAt, $endAt) {
                    $query->whereBetween('start_at', [$startAt, $endAt])
                        ->orWhereBetween('end_at', [$startAt, $endAt])
                        ->orWhere(function ($query) use ($startAt, $endAt) {
                            $query->where('start_at', '<=', $startAt)
                                ->where('end_at', '>=', $endAt);
                        });
                })
                ->exists();

            if ($overlaps) {
                $validator->errors()->add('start_at', 'Schedule overlaps with existing flash sale.');
                $validator->errors()->add('end_at', 'Schedule overlaps with existing flash sale.');
            }
        });
    }
}
