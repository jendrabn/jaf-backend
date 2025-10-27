<div class="d-flex gap-1">
    <a class="btn btn-primary btn-sm btn-icon"
       data-toggle="tooltip"
       href="{{ route('admin.campaigns.show', $id) }}"
       title="DETAIL">
        <i class="bi bi-eye"></i>
    </a>

    <a class="btn btn-info btn-sm btn-icon"
       data-toggle="tooltip"
       href="{{ route('admin.campaigns.edit', $id) }}"
       title="EDIT">
        <i class="bi bi-pencil"></i>
    </a>

    <button class="btn btn-success btn-sm btn-icon btn-send-all"
            data-toggle="tooltip"
            data-url="{{ route('admin.campaigns.send_all', $id) }}"
            title="SEND TO ALL"
            type="button">
        <i class="bi bi-send-check"></i>
    </button>

    <button class="btn btn-info btn-sm btn-icon btn-test-send"
            data-toggle="tooltip"
            data-url="{{ route('admin.campaigns.test_send', $id) }}"
            title="TEST SEND"
            type="button">
        <i class="bi bi-envelope-paper"></i>
    </button>

    <button class="btn btn-danger btn-sm btn-icon btn-delete"
            data-toggle="tooltip"
            data-url="{{ route('admin.campaigns.destroy', $id) }}"
            title="DELETE"
            type="button">
        <i class="bi bi-trash"></i>
    </button>
</div>
