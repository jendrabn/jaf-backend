<div class="d-flex gap-1">
    <a class="btn btn-info btn-sm btn-icon btn-edit"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.product-brands.edit', $id) }}"
       title="Edit Product Brand">
        <i class="bi bi-pencil"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.product-brands.destroy', $id) }}"
       title="Delete Product Brand">
        <i class="bi bi-trash3"></i>
    </a>
</div>
