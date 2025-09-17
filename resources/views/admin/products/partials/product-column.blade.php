<div class="d-flex align-items-center">
    <img alt="Product Image"
         class="theme-avatar border border-2 border-primary rounded"
         src="{{ $product?->image?->url }}">
    <div class="product-info ml-3">
        <p class="mb-2"
           style="font-weight: 600;">{{ $product->name }}</p>
        <div class="rating">
            @for ($i = 0; $i < $product->rating_avg; $i++)
                <i class="bi bi-star-fill"></i>
            @endfor
            @for ($i = $product->rating_avg; $i < 5; $i++)
                <i class="bi bi-star"></i>
            @endfor
        </div>
    </div>
</div>
