<div class="d-flex align-items-center">
    <img alt="Product Image"
         class="theme-avatar border border-2 border-primary rounded"
         src="{{ $product?->image?->url }}">
    <div class="product-info ml-3">
        <p class="mb-2"
           style="font-weight: 600;">{{ $product->name }}</p>
        @php
            $maxStars = 5;
            $filledStars = (int) round((float) $product->rating_avg);
            $filledStars = min($maxStars, max(0, $filledStars));
        @endphp

        <div class="rating">
            @for ($i = 0; $i < $maxStars; $i++)
                <i class="bi {{ $i < $filledStars ? 'bi-star-fill' : 'bi-star' }}"></i>
            @endfor
        </div>
    </div>
</div>
