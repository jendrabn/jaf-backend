@extends('layouts.admin')

@section('page_title', 'Blog')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Blog' => null,
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

    @include('admin.blogs.partials.modal-filter')
@endSection

@section('scripts')
    <script>
        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: "Delete selected",
            url: "{{ route('admin.blogs.massDestroy') }}",
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
                    toastr.warning("No rows selected", "Warning");

                    return;
                }

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
                            url: config.url,
                            data: {
                                ids: ids,
                                _method: "DELETE",
                            },
                            success: function(data) {
                                toastr.success(data.message);
                                dt.ajax.reload();
                            },
                        });
                    }
                });
            },
        };

        $.fn.dataTable.ext.buttons.filter = {
            text: "<i class='fa fa-filter'></i>",
            action: function(e, dt, node, config) {
                $("#modal-filter").modal("show");
            },
        };
    </script>

    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["blog-table"];

            table.on("click", ".btn-delete", function(e) {
                const data = table.row($(this).parents("tr")).data();

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
                            url: $(this).data("url"),
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

            table.on("change", ".check-published", function(e) {
                $.ajax({
                    headers: {
                        "x-csrf-token": _token,
                    },
                    method: "PUT",
                    url: $(this).data("url"),
                    success: function(data) {
                        toastr.success(data.message);
                    },
                });
            });

            $('#btn-reset-filter').on('click', function() {
                $('#form-filter')[0].reset();
                table.ajax.reload();
            });

            $('#btn-filter').on('click', function() {
                table.ajax.reload();
            });
        });
    </script>
@endSection
