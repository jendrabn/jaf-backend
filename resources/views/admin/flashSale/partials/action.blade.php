<div class="d-flex gap-1">
    <a class="btn btn-info btn-sm btn-icon"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.flash-sales.edit', $id) }}"
       title="Edit Flash Sale">
        <i class="bi bi-pencil"></i>
    </a>
    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.flash-sales.destroy', $id) }}"
       title="Delete Flash Sale">
        <i class="bi bi-trash3"></i>
    </a>
</div>
