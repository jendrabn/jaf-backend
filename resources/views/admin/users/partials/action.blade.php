<div class="d-flex gap-1">
    <a class="btn btn-primary btn-sm btn-icon btn-view"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.users.show', $id) }}"
       title="View User">
        <i class="bi bi-eye"></i>
    </a>

    <a class="btn btn-info btn-sm btn-icon btn-edit"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.users.edit', $id) }}"
       title="Edit User">
        <i class="bi bi-pencil"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.users.destroy', $id) }}"
       title="Delete User">
        <i class="bi bi-trash3"></i>
    </a>
</div>
