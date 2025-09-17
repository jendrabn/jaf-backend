<div class="d-flex"
     style="gap: 5px">
    <a class="btn btn-info btn-sm btn-icon btn-edit"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.ewallets.edit', $id) }}"
       title="Edit Ewallet">
        <i class="bi bi-pencil"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.ewallets.destroy', $id) }}"
       title="Delete Ewallet">
        <i class="bi bi-trash"></i>
    </a>
</div>
