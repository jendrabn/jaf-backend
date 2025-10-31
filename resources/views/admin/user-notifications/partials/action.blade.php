<div class="d-flex gap-1">
    <a class="btn btn-primary btn-sm btn-icon btn-view"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.user-notifications.show', $id) }}"
       title="View Notification">
        <i class="bi bi-eye"></i>
    </a>

    <a class="btn btn-info btn-sm btn-icon btn-edit"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.user-notifications.edit', $id) }}"
       title="Edit Notification">
        <i class="bi bi-pencil"></i>
    </a>

    @if ($read_at)
        <a class="btn btn-warning btn-sm btn-icon btn-mark-unread"
           data-placement="top"
           data-toggle="tooltip"
           href="{{ route('admin.user-notifications.markAsUnread', $id) }}"
           title="Mark as Unread">
            <i class="bi bi-envelope"></i>
        </a>
    @else
        <a class="btn btn-success btn-sm btn-icon btn-mark-read"
           data-placement="top"
           data-toggle="tooltip"
           href="{{ route('admin.user-notifications.markAsRead', $id) }}"
           title="Mark as Read">
            <i class="bi bi-envelope-open"></i>
        </a>
    @endif

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.user-notifications.destroy', $id) }}"
       title="Delete Notification">
        <i class="bi bi-trash3"></i>
    </a>
</div>
