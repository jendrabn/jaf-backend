<div class="d-flex gap-1">
    <a class="btn btn-primary btn-sm btn-icon"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.audit-logs.show', $id) }}"
       title="View Audit Log">
        <i class="bi bi-eye"></i>
    </a>

    <a class="btn btn-danger btn-sm btn-icon btn-delete"
       data-placement="top"
       data-toggle="tooltip"
       href="{{ route('admin.audit-logs.destroy', $id) }}"
       title="Delete Audit Log">
        <i class="bi bi-trash3"></i>
    </a>
</div>
