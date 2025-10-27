<div class="d-flex gap-1">
    <button class="btn btn-info btn-sm btn-edit-blog-category"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-categories.edit', $id) }}"
            title="Edit Blog Category"
            type="button">
        <i class="bi bi-pencil"></i>
    </button>
    <button class="btn btn-danger btn-sm btn-delete-blog-category"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-categories.destroy', $id) }}"
            title="Delete Blog Category"
            type="button">
        <i class="bi bi-trash3"></i>
    </button>
</div>
