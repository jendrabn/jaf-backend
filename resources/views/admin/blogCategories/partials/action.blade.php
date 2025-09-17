<div class="d-flex"
     style="gap: 5px">
    <button class="btn btn-info btn-sm btn-icon btn-edit"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-categories.update', $id) }}"
            title="Edit Blog Category">
        <i class="bi bi-pencil"></i>
    </button>

    <button data-placement="top"
            data-toggle="tooltip"class="btn btn-danger btn-sm btn-icon btn-delete"
            data-url="{{ route('admin.blog-categories.destroy', $id) }}"
            title="Delete Blog Category">
        <i class="bi bi-trash"></i>
    </button>
</div>
