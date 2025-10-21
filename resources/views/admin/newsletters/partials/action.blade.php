@canany(['newsletters.edit', 'newsletters.show', 'newsletters.delete'])
    <div class="btn-group">
        @can('newsletters.show')
            <a class="btn btn-sm btn-info"
               href="{{ route('admin.newsletters.show', $id) }}"
               title="View">
                <i class="bi bi-eye"></i>
            </a>
        @endcan

        @can('newsletters.edit')
            <a class="btn btn-sm btn-warning"
               href="{{ route('admin.newsletters.edit', $id) }}"
               title="Edit">
                <i class="bi bi-pencil"></i>
            </a>
        @endcan

        @can('newsletters.delete')
            <a class="btn btn-sm btn-danger btn-delete"
               href="{{ route('admin.newsletters.destroy', $id) }}"
               title="Delete">
                <i class="bi bi-trash"></i>
            </a>
        @endcan
    </div>
@endcanany
