<div class="d-flex"
     style="gap: 5px">
    <button class="btn btn-info btn-sm btn-icon btn-edit-tax"
            data-url="{{ route('admin.taxes.edit', $id) }}"
            type="button">
        <i class="bi bi-pencil"></i>
    </button>
    <button class="btn btn-danger btn-sm btn-icon btn-delete-tax"
            data-url="{{ route('admin.taxes.destroy', $id) }}"
            type="button">
        <i class="bi bi-trash3"></i>
    </button>
</div>
