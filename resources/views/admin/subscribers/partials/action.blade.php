<div class="d-flex gap-1">
    <button class="btn btn-default btn-sm btn-edit-subscriber"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.subscribers.edit', $id) }}"
            title="Edit Subscriber"
            type="button">
        <i class="bi bi-pencil"></i>
    </button>
    <button class="btn btn-danger btn-sm btn-delete-subscriber"
            data-placement="top"
            data-toggle="tooltip"
            data-url="{{ route('admin.subscribers.destroy', $id) }}"
            title="Delete Subscriber"
            type="button">
        <i class="bi bi-trash3"></i>
    </button>
</div>
