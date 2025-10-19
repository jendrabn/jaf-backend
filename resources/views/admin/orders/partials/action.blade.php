<div class="d-flex gap-1">
    <a class="btn btn-primary btn-sm btn-icon btn-view"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.orders.show', $id) }}"
       title="View Coupon">
        <i class="bi bi-eye"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       href="{{ route('admin.orders.destroy', $id) }}">
        <i class="bi bi-trash3"></i>
    </a>
</div>
