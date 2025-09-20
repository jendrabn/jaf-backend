<div class="d-flex gap-1">
    <button class="btn btn-info btn-sm btn-edit-blog-tag"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-tags.edit', $id) }}"
            title="Edit Blog Tag"
            type="button">
        <i class="bi bi-pencil"></i>
    </button>
    <button class="btn btn-danger btn-sm btn-delete-blog-tag"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.blog-tags.destroy', $id) }}"
            title="Delete Blog Tag"
            type="button">
        <i class="bi bi-trash3"></i>
    </button>
</div>
