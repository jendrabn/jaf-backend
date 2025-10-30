<div class="d-flex gap-1">
    <a class="btn btn-danger btn-sm btn-icon btn-delete-rating"
       data-placement="top"
       data-toggle="tooltip"
       data-url="{{ route('admin.products.ratings.destroy', [$product->id, $rating->id]) }}"
       href="#"
       title="Delete Rating">
        <i class="bi bi-trash3"></i>
    </a>
</div>
