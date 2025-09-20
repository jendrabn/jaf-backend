<div class="d-flex gap-1">
    <a class="btn btn-info btn-sm btn-icon btn-edit"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.roles.edit', $id) }}"
       title="Edit Role">
        <i class="bi bi-pencil"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.roles.destroy', $id) }}"
       title="Delete Role">
        <i class="bi bi-trash3"></i>
    </a>

</div>
