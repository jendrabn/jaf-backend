@extends('layouts.admin')

@section('page_title', 'Audit Log')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Audit Log' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-bordered datatable ajaxTable mt-3']) }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let table;

        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: "Delete selected",
            url: "{{ route('admin.audit-logs.massDestroy') }}",
            className: "btn-danger",
            action: function(e, dt, node, config) {
                let ids = $.map(
                    dt
                    .rows({
                        selected: true,
                    })
                    .data(),
                    function(entry) {
                        return entry.id;
                    }
                );

                if (ids.length === 0) {
                    toastr.warning("No rows selected", 'Warning');

                    return;
                }

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "<i class='bi bi-trash3'></i> Delete",
                    cancelButtonText: "Cancel",
                    reverseButtons: true,
                }).then(function(result) {
                    if (result.isConfirmed) {

                        $.ajax({
                            method: 'POST',
                            url: config.url,
                            data: {
                                ids: ids,
                                _token: "{{ csrf_token() }}",
                            },
                        }).done(function() {
                            toastr.success("Deleted successfully", 'Success');

                            table.ajax.reload();
                        });

                    }
                });
            }
        };
    </script>

    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(function() {
            table = window.LaravelDataTables["auditlog-datatable"];

            table.on('click', '.btn-delete', function(e) {
                e.preventDefault();

                let url = $(this).attr('href');

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "<i class='bi bi-trash3'></i> Delete",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                "x-csrf-token": _token,
                            },
                            method: "POST",
                            url: url,
                            data: {
                                _method: "DELETE",
                            },
                            success: function(data) {
                                toastr.success(data.message);
                                table.ajax.reload();
                            },
                        });
                    }
                });
            });

        });
    </script>
@endsection
