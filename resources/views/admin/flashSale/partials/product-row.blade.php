@php
    $isTemplate = $isTemplate ?? false;
    $rowData = $row ?? [];
    $productIdValue = $isTemplate ? null : old("products.$index.product_id", data_get($rowData, 'product_id'));
    $flashPriceValue = $isTemplate ? null : old("products.$index.flash_price", data_get($rowData, 'flash_price'));
    $stockValue = $isTemplate ? null : old("products.$index.stock_flash", data_get($rowData, 'stock_flash'));
    $maxQtyValue = $isTemplate ? null : old("products.$index.max_qty_per_user", data_get($rowData, 'max_qty_per_user'));
@endphp
<tr data-index="{{ $index }}">
    <td style="min-width: 220px;">
        <label class="sr-only"
               for="product-{{ $index }}">Product</label>
        <select class="form-control select2 product-select"
                data-placeholder="Select product"
                id="product-{{ $index }}"
                name="products[{{ $index }}][product_id]">
            <option value="">Select product</option>
            @foreach ($productsList as $product)
                <option {{ (string) $productIdValue === (string) $product->id ? 'selected' : '' }}
                        data-id="{{ $product->id }}"
                        data-image="{{ $product->image?->url ?? asset('images/default-product.jpg') }}"
                        data-name="{{ $product->name }}"
                        data-price="{{ number_format($product->price ?? 0, 0, ',', '.') }}"
                        data-price-raw="{{ $product->price ?? 0 }}"
                        value="{{ $product->id }}">
                    {{ $product->name }} (Rp {{ number_format($product->price ?? 0, 0, ',', '.') }})
                </option>
            @endforeach
        </select>
        @unless ($isTemplate)
            @error('products.' . $index . '.product_id')
                <span class="text-danger small d-block">{{ $message }}</span>
            @enderror
        @endunless
    </td>
    <td style="min-width: 150px;">
        <label class="sr-only"
               for="flash-price-{{ $index }}">Flash Price</label>
        <div class="input-group">
            <input class="form-control flash-price-input"
                   id="flash-price-{{ $index }}"
                   name="products[{{ $index }}][flash_price]"
                   placeholder="0.00"
                   step="0.01"
                   type="number"
                   value="{{ $flashPriceValue }}">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary btn-open-price-modal"
                        type="button"
                        title="Hitung harga dari diskon">
                    <i class="bi bi-calculator"></i>
                </button>
            </div>
        </div>
        @unless ($isTemplate)
            @error('products.' . $index . '.flash_price')
                <span class="text-danger small d-block">{{ $message }}</span>
            @enderror
        @endunless
    </td>
    <td style="min-width: 130px;">
        <label class="sr-only"
               for="stock-{{ $index }}">Flash Stock</label>
        <input class="form-control"
               id="stock-{{ $index }}"
               min="1"
               name="products[{{ $index }}][stock_flash]"
               type="number"
               value="{{ $stockValue }}">
        @unless ($isTemplate)
            @error('products.' . $index . '.stock_flash')
                <span class="text-danger small d-block">{{ $message }}</span>
            @enderror
        @endunless
    </td>
    <td style="min-width: 130px;">
        <label class="sr-only"
               for="max-qty-{{ $index }}">Max/User</label>
        <input class="form-control"
               id="max-qty-{{ $index }}"
               min="0"
               name="products[{{ $index }}][max_qty_per_user]"
               placeholder="0 = unlimited"
               type="number"
               value="{{ $maxQtyValue }}">
        @unless ($isTemplate)
            @error('products.' . $index . '.max_qty_per_user')
                <span class="text-danger small d-block">{{ $message }}</span>
            @enderror
        @endunless
    </td>
    <td class="text-center align-middle">
        <button class="btn btn-outline-danger btn-sm btn-icon btn-remove-row"
                type="button">
            <i class="bi bi-x-lg"></i>
        </button>
    </td>
</tr>
