<input @checked($rating->is_publish)
       class="check-rating-published"
       data-url="{{ route('admin.products.ratings.publish', [$product->id, $rating->id]) }}"
       type="checkbox" />
