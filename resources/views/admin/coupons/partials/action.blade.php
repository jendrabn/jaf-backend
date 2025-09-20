<div class="d-flex gap-1">
    <a class="btn btn-primary btn-sm btn-icon btn-view"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.coupons.show', $id) }}"
       title="View Coupon">
        <i class="bi bi-eye"></i>
    </a>

    <a class="btn btn-info btn-sm btn-icon btn-edit"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.coupons.edit', $id) }}"
       title="Edit Coupon">
        <i class="bi bi-pencil"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       href="{{ route('admin.coupons.destroy', $id) }}">
        <i class="bi bi-trash3"></i>
    </a>
</div>
