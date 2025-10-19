<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CouponRequest extends FormRequest
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
        if ($this->routeIs('admin.coupons.store')) {
            return [
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'description' => ['required', 'string', 'min:3', 'max:10000'],
                'promo_type' => ['required', 'string', 'in:limit,period,product'],
                'code_limit' => ['nullable', 'required_if:promo_type,limit', 'string', 'min:3', 'max:255', 'unique:coupons,code'],
                'code_period' => ['nullable', 'required_if:promo_type,period', 'string', 'min:3', 'max:255', 'unique:coupons,code'],
                'discount_type_limit' => ['nullable', 'required_if:promo_type,limit', 'string', 'in:fixed,percentage'],
                'discount_type_period' => ['nullable', 'required_if:promo_type,period', 'string', 'in:fixed,percentage'],
                'discount_type_product' => ['nullable', 'required_if:promo_type,product', 'string', 'in:fixed,percentage'],
                'discount_amount_limit' => ['nullable', 'required_if:promo_type,limit', 'numeric', 'min:1', Rule::when($this->input('discount_type_limit') == 'percentage', 'max:100')],
                'discount_amount_period' => ['nullable', 'required_if:promo_type,period', 'numeric', 'min:1', Rule::when($this->input('discount_type_period') == 'percentage', 'max:100')],
                'discount_amount_product' => ['nullable', 'required_if:promo_type,product', 'numeric', 'min:1', Rule::when($this->input('discount_type_product') == 'percentage', 'max:100')],
                'limit' => ['nullable', 'required_if:promo_type,limit', 'integer', 'min:1'],
                'limit_per_user_limit' => ['nullable', 'integer', 'min:1'],
                'limit_per_user_period' => ['nullable', 'integer', 'min:1'],
                'start_date_period' => ['nullable', 'required_if:promo_type,period', 'date', 'before:end_date_period'],
                'start_date_product' => ['nullable', 'required_if:promo_type,product', 'date', 'before:end_date_product'],
                'end_date_period' => ['nullable', 'required_if:promo_type,period', 'date', 'after:start_date_period'],
                'end_date_product' => ['nullable', 'required_if:promo_type,product', 'date', 'after:start_date_product'],
                'is_active' => ['nullable', 'boolean'],
                'product_ids' => ['nullable', 'required_if:promo_type,product', 'array', 'exists:products,id'],
            ];
        } elseif ($this->routeIs('admin.coupons.update')) {
            return [
                'name' => ['required', 'string', 'min:3', 'max:255'],
                'description' => ['required', 'string', 'min:3', 'max:10000'],
                'promo_type' => ['required', 'string', 'in:limit,period,product'],
                'code_limit' => ['nullable', 'required_if:promo_type,limit', 'string', 'min:3', 'max:255', 'unique:coupons,code,'.$this->route('coupon')->id],
                'code_period' => ['nullable', 'required_if:promo_type,period', 'string', 'min:3', 'max:255', 'unique:coupons,code,'.$this->route('coupon')->id],
                'discount_type_limit' => ['nullable', 'required_if:promo_type,limit', 'string', 'in:fixed,percentage'],
                'discount_type_period' => ['nullable', 'required_if:promo_type,period', 'string', 'in:fixed,percentage'],
                'discount_type_product' => ['nullable', 'required_if:promo_type,product', 'string', 'in:fixed,percentage'],
                'discount_amount_limit' => ['nullable', 'required_if:promo_type,limit', 'numeric', 'min:1', Rule::when($this->input('discount_type_limit') == 'percentage', 'max:100')],
                'discount_amount_period' => ['nullable', 'required_if:promo_type,period', 'numeric', 'min:1', Rule::when($this->input('discount_type_period') == 'percentage', 'max:100')],
                'discount_amount_product' => ['nullable', 'required_if:promo_type,product', 'numeric', 'min:1', Rule::when($this->input('discount_type_product') == 'percentage', 'max:100')],
                'limit' => ['nullable', 'required_if:promo_type,limit', 'integer', 'min:1'],
                'limit_per_user_limit' => ['nullable', 'integer', 'min:1'],
                'limit_per_user_period' => ['nullable', 'integer', 'min:1'],
                'start_date_period' => ['nullable', 'required_if:promo_type,period', 'date', 'before:end_date_period'],
                'start_date_product' => ['nullable', 'required_if:promo_type,product', 'date', 'before:end_date_product'],
                'end_date_period' => ['nullable', 'required_if:promo_type,period', 'date', 'after:start_date_period'],
                'end_date_product' => ['nullable', 'required_if:promo_type,product', 'date', 'after:start_date_product'],
                'is_active' => ['nullable', 'boolean'],
                'product_ids' => ['nullable', 'required_if:promo_type,product', 'array', 'exists:products,id'],
            ];
        } else {
            return [];
        }
    }

    public function attributes()
    {
        return [
            'name' => 'name',
            'description' => 'description',
            'promo_type' => 'promo type',
            'code_limit' => 'code limit',
            'code_period' => 'code period',
            'discount_type_limit' => 'discount type limit',
            'discount_type_period' => 'discount type period',
            'discount_type_product' => 'discount type product',
            'discount_amount_limit' => 'discount amount limit',
            'discount_amount_period' => 'discount amount period',
            'discount_amount_product' => 'discount amount product',
            'limit' => 'usage limit',
            'start_date_period' => 'start date period',
            'start_date_product' => 'start date product',
            'end_date_period' => 'end date period',
            'end_date_product' => 'end date product',
            'is_active' => 'is active',
            'product_ids' => 'product ids',
        ];
    }
}
