<div class="d-flex"
     style="gap: 5px">
    <button class="btn btn-info btn-sm btn-icon btn-edit"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-tags.update', $id) }}"
            title="Edit Blog Tag">
        <i class="bi bi-pencil"></i>
    </button>

    <button class="btn btn-danger btn-sm btn-icon btn-delete"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-tags.destroy', $id) }}"
            title="Delete Blog Tag">
        <i class="bi bi-trash"></i>
    </button>
</div>
