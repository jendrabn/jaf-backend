@extends('layouts.admin')

@section('page_title', 'Flash Sales')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Flash Sales' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-bordered table-striped w-100'], false) }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: '<i class="bi bi-trash3 mr-1"></i> Delete Selected',
            action: function(e, dt, node, config) {
                const ids = $.map(dt.rows({
                    selected: true,
                }).data(), function(entry) {
                    return entry.id;
                });

                if (!ids.length) {
                    toastr.warning('No flash sale selected.', 'Warning');

                    return;
                }

                Swal.fire({
                    title: 'Delete selected events?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "<i class='bi bi-trash3'></i> Delete",
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        headers: {
                            'x-csrf-token': _token,
                        },
                        method: 'POST',
                        url: "{{ route('admin.flash-sales.massDestroy') }}",
                        data: {
                            ids: ids,
                            _method: 'DELETE',
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            dt.ajax.reload();
                        },
                    });
                });
            },
        };
    </script>

    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
    <script>
        $(function() {
            const table = window.LaravelDataTables['dataTable-flash-sales'];

            $(document).on('click', '.buttons-create', function(e) {
                e.preventDefault();
                window.location.href = "{{ route('admin.flash-sales.create') }}";
            });

            table.on('click', '.btn-delete', function(e) {
                e.preventDefault();

                const url = $(this).attr('href');

                Swal.fire({
                    title: 'Delete this flash sale?',
                    text: "This action can't be reversed.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "<i class='bi bi-trash3'></i> Delete",
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    $.ajax({
                        headers: {
                            'x-csrf-token': _token,
                        },
                        method: 'POST',
                        url: url,
                        data: {
                            _method: 'DELETE',
                        },
                        success: function(response) {
                            toastr.success(response.message);
                            table.ajax.reload();
                        },
                    });
                });
            });
        });
    </script>
@endsection
