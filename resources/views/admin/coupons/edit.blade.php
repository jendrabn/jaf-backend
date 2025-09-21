@extends('layouts.admin')

@section('page_title', 'Edit Coupon')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Product Coupons' => route('admin.coupons.index'),
            'Edit Coupon' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.coupons.update', $coupon->id) }}"
                  method="post">
                @csrf
                @method('PUT')

                <div class="card shadow-lg">
                    <div class="card-header">
                        <div class="card-tools">
                            <a class="btn btn-default"
                               href="{{ route('admin.coupons.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                        </div>
                    </div>
                    <div class="card-body">

                        {{-- Promo Name --}}
                        <div class="form-group">
                            <label class="required"
                                   for="name">Promo Name</label>
                            <input class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   type="text"
                                   value="{{ old('name', $coupon->name) }}">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Promo Description --}}
                        <div class="form-group">
                            <label class="required"
                                   for="description">Promo Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description"
                                      name="description">{{ old('description', $coupon->description) }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Promo Type --}}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card promo-type-card"
                                     id="limit-card">
                                    <div class="card-body text-center">
                                        <input {{ old('promo_type', $coupon->promo_type) == 'limit' ? 'checked' : '' }}
                                               hidden
                                               id="limit"
                                               name="promo_type"
                                               type="radio"
                                               value="limit">
                                        <label class="w-100"
                                               for="limit"
                                               onclick="selectPromoType('limit');">
                                            <i class="fas fa-ticket-alt fa-2x mb-2 d-block"></i>
                                            <strong class="d-block fs-6">Limit Quantity Coupon</strong>
                                            <small class="d-block">Limit the number of times a coupon can be used</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card promo-type-card"
                                     id="period-card">
                                    <div class="card-body text-center">
                                        <input {{ old('promo_type', $coupon->promo_type) == 'period' ? 'checked' : '' }}
                                               hidden
                                               id="period"
                                               name="promo_type"
                                               type="radio"
                                               value="period">
                                        <label class="w-100"
                                               for="period"
                                               onclick="selectPromoType('period');">
                                            <i class="fas fa-calendar-alt fa-2x mb-2 d-block"></i>
                                            <strong class="d-block fs-6">Limit Period Coupon</strong>
                                            <small class="d-block">Coupon is valid for a limited period</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card promo-type-card"
                                     id="product-card">
                                    <div class="card-body text-center">
                                        <input {{ old('promo_type', $coupon->promo_type) == 'product' ? 'checked' : '' }}
                                               hidden
                                               id="product"
                                               name="promo_type"
                                               type="radio"
                                               value="product">
                                        <label class="w-100"
                                               for="product"
                                               onclick="selectPromoType('product');">
                                            <i class="fas fa-gift fa-2x mb-2 d-block"></i>
                                            <strong class="d-block fs-6">Promo Event Product</strong>
                                            <small class="d-block">Discount on a specific product</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Dynamic Fields --}}
                        <div class="col-md-12"
                             id="dynamic-fields"
                             style="display: none">

                            {{-- Limit Quantity --}}
                            <div class="dynamic-field-group"
                                 id="limit-fields">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label class="required">Coupon Code</label>
                                        <div class="input-group">
                                            <input class="form-control @error('code_limit') is-invalid @enderror"
                                                   id="code-limit"
                                                   name="code_limit"
                                                   placeholder="Enter Coupon Code"
                                                   type="text"
                                                   value="{{ old('code_limit', $coupon->code_limit) }}">
                                            <button class="btn btn-outline-secondary"
                                                    onclick="generateCouponCode('#code-limit')"
                                                    type="button">
                                                <i class="fa fa-refresh"></i> Generate
                                            </button>
                                            @error('code_limit')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="discount-amount-limit">Amount Discount</label>
                                        <input class="form-control @error('discount_amount_limit') is-invalid @enderror"
                                               id="discount-amount-limit"
                                               name="discount_amount_limit"
                                               step="0.01"
                                               type="number"
                                               value="{{ old('discount_amount_limit', $coupon->discount_amount_limit) }}">
                                        @error('discount_amount_limit')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required">Discount Type</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input {{ old('discount_type_limit', $coupon->discount_type_limit) == 'fixed' ? 'checked' : '' }}
                                                       class="form-check-input"
                                                       id="fixed-limit"
                                                       name="discount_type_limit"
                                                       type="radio"
                                                       value="fixed">
                                                <label class="form-check-label"
                                                       for="fixed-limit">Fixed</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input {{ old('discount_type_limit', $coupon->discount_type_limit) == 'percentage' ? 'checked' : '' }}
                                                       class="form-check-input"
                                                       id="percentage-limit"
                                                       name="discount_type_limit"
                                                       type="radio"
                                                       value="percentage">
                                                <label class="form-check-label"
                                                       for="percentage-limit">Percentage</label>
                                            </div>
                                        </div>
                                        @error('discount_type_limit')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="usage-limit">Limit</label>
                                        <input class="form-control @error('limit') is-invalid @enderror"
                                               id="usage-limit"
                                               name="limit"
                                               step="1"
                                               type="number"
                                               value="{{ old('limit', $coupon->limit) }}">
                                        @error('limit')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="limit-per-user-limit">Limit Per User</label>
                                        <input class="form-control @error('limit_per_user_limit') is-invalid @enderror"
                                               id="limit-per-user-limit"
                                               name="limit_per_user_limit"
                                               placeholder="Enter Limit per User"
                                               step="1"
                                               type="number"
                                               value="{{ old('limit_per_user_limit', $coupon->limit_per_user_limit) }}">
                                        @error('limit_per_user_limit')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Leave empty if you don't want to limit per user
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Limit Period --}}
                            <div class="dynamic-field-group"
                                 id="period-fields">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label class="required"
                                               for="code-period">Coupon Code</label>
                                        <div class="input-group">
                                            <input class="form-control @error('code_period') is-invalid @enderror"
                                                   id="code-period"
                                                   name="code_period"
                                                   type="text"
                                                   value="{{ old('code_period', $coupon->code_period) }}">
                                            <button class="btn btn-outline-secondary"
                                                    onclick="generateCouponCode('#code-period')"
                                                    type="button">
                                                <i class="fa fa-refresh"></i> Generate
                                            </button>
                                            @error('code_period')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="discount-amount-period">Amount Discount</label>
                                        <input class="form-control @error('discount_amount_period') is-invalid @enderror"
                                               id="discount-amount-period"
                                               name="discount_amount_period"
                                               step="0.01"
                                               type="number"
                                               value="{{ old('discount_amount_period', $coupon->discount_amount_period) }}">
                                        @error('discount_amount_period')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required">Discount Type</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input {{ old('discount_type_period', $coupon->discount_type_period) == 'fixed' ? 'checked' : '' }}
                                                       class="form-check-input"
                                                       id="fixed-period"
                                                       name="discount_type_period"
                                                       type="radio"
                                                       value="fixed">
                                                <label class="form-check-label"
                                                       for="fixed-period">Fixed</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input {{ old('discount_type_period', $coupon->discount_type_period) == 'percentage' ? 'checked' : '' }}
                                                       class="form-check-input"
                                                       id="percentage-period"
                                                       name="discount_type_period"
                                                       type="radio"
                                                       value="percentage">
                                                <label class="form-check-label"
                                                       for="percentage-period">Percentage</label>
                                            </div>
                                        </div>
                                        @error('discount_type_period')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="start-date-period">Start Date</label>
                                        <input class="form-control @error('start_date_period') is-invalid @enderror"
                                               id="start-date-period"
                                               name="start_date_period"
                                               type="date"
                                               value="{{ old('start_date_period', $coupon->start_date_period) }}">
                                        @error('start_date_period')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="end-date-period">End Date</label>
                                        <input class="form-control @error('end_date_period') is-invalid @enderror"
                                               id="end-date-period"
                                               name="end_date_period"
                                               type="date"
                                               value="{{ old('end_date_period', $coupon->end_date_period) }}">
                                        @error('end_date_period')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label for="limit-per-user-period">Limit per User</label>
                                        <input class="form-control @error('limit_per_user_period') is-invalid @enderror"
                                               id="limit-per-user-period"
                                               name="limit_per_user_period"
                                               placeholder="Enter Limit Per User"
                                               step="1"
                                               type="number"
                                               value="{{ old('limit_per_user_period', $coupon->limit_per_user_period) }}">
                                        @error('limit_per_user_period')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Leave empty if you don't want to limit per user
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Event Product --}}
                            <div class="dynamic-field-group"
                                 id="product-fields">
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="start-date-product">Start Date</label>
                                        <input class="form-control @error('start_date_product') is-invalid @enderror"
                                               id="start-date-product"
                                               name="start_date_product"
                                               type="date"
                                               value="{{ old('start_date_product', $coupon->start_date_product) }}">
                                        @error('start_date_product')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="end-date-product">End Date</label>
                                        <input class="form-control @error('end_date_product') is-invalid @enderror"
                                               id="end-date-product"
                                               name="end_date_product"
                                               type="date"
                                               value="{{ old('end_date_product', $coupon->end_date_product) }}">
                                        @error('end_date_product')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required"
                                               for="discount-amount-product">Amount Discount</label>
                                        <input class="form-control @error('discount_amount_product') is-invalid @enderror"
                                               id="discount-amount-product"
                                               name="discount_amount_product"
                                               step="0.01"
                                               type="number"
                                               value="{{ old('discount_amount_product', $coupon->discount_amount_product) }}">
                                        @error('discount_amount_product')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label class="required">Discount Type</label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline">
                                                <input {{ old('discount_type_product', $coupon->discount_type_product) == 'fixed' ? 'checked' : '' }}
                                                       class="form-check-input"
                                                       id="fixed-product"
                                                       name="discount_type_product"
                                                       type="radio"
                                                       value="fixed">
                                                <label class="form-check-label"
                                                       for="fixed-product">Fixed</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input {{ old('discount_type_product', $coupon->discount_type_product) == 'percentage' ? 'checked' : '' }}
                                                       class="form-check-input"
                                                       id="percentage-product"
                                                       name="discount_type_product"
                                                       type="radio"
                                                       value="percentage">
                                                <label class="form-check-label"
                                                       for="percentage-product">Percentage</label>
                                            </div>
                                        </div>
                                        @error('discount_type_product')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label class="required"
                                               for="product-ids">Product(s)</label>
                                        <select class="form-control select2 w-100 @error('product_ids') is-invalid @enderror"
                                                id="product-ids"
                                                multiple
                                                name="product_ids[]"
                                                style="width: 100%;">
                                            @foreach ($products as $product)
                                                <option {{ collect(old('product_ids', $coupon->products->pluck('id')->toArray()))->contains($product->id) ? 'selected' : '' }}
                                                        data-id="{{ $product->id }}"
                                                        data-image="{{ $product->image?->url ?? asset('images/default-product.jpg') }}"
                                                        data-name="{{ $product->name }}"
                                                        data-price="{{ number_format($product->price, 0, ',', '.') }}"
                                                        value="{{ $product->id }}">
                                                    [{{ $product->id }}] {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('product_ids')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Pilih produk, pencarian tersedia. Setiap produk
                                            tampil ID, foto, nama, dan harga.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light mr-2"
                           href="{{ route('admin.coupons.index') }}">
                            <i class="bi bi-x-circle mr-1"></i>Cancel
                        </a>
                        <button class="btn btn-primary"
                                type="submit">
                            <i class="bi bi-check2-circle mr-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let selectedPromoType = "{{ old('promo_type', $coupon->promo_type) }}";

        function selectPromoType(type) {
            $('.promo-type-card').removeClass('selected');
            $('.dynamic-field-group')
                .hide()
                .find('input, select, textarea')
                .prop('disabled', true);

            $('#' + type).prop('checked', true);
            $('#' + type + '-card').addClass('selected');

            $('#dynamic-fields').show();
            $('#' + type + '-fields')
                .show()
                .find('input, select, textarea')
                .prop('disabled', false);
        }

        function generateCouponCode(selector) {
            const code = Math.random().toString(36).substring(2, 10).toUpperCase();
            $(selector).val(code);
        }

        $(document).ready(function() {
            $('#product-ids').select2({
                templateResult: formatProductOption,
                templateSelection: formatProductSelection,
                escapeMarkup: function(markup) {
                    return markup;
                },
                width: '100%',
                placeholder: 'Cari produk...'
            });
        });

        function formatProductOption(product) {
            if (!product.id) {
                return product.text;
            }
            let $option = $(product.element);
            let image = $option.data('image');
            let name = $option.data('name');
            let price = $option.data('price');
            let id = $option.data('id');

            return `
                <div class="d-flex align-items-center">
                    <img src="${image}" alt="${name}" style="width:40px;height:40px;object-fit:cover;border-radius:6px;margin-right:10px;">
                    <div>
                        <div><strong>[${id}] ${name}</strong></div>
                        <div class="text-muted">Rp ${price}</div>
                    </div>
                </div>
            `;
        }

        function formatProductSelection(product) {
            if (!product.id) {
                return product.text;
            }
            var $option = $(product.element);
            var name = $option.data('name');
            var id = $option.data('id');
            return `[${id}] ${name}`;
        }

        if (selectedPromoType) {
            selectPromoType(selectedPromoType);
        }
    </script>
@endpush
