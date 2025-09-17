@extends('layouts.admin')

@section('page_title', 'Blog Category')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Blog Category' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-bordered datatable ajaxTable']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.blogCategories.partials.modal-create')
    @include('admin.blogCategories.partials.modal-edit')
@endSection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(document).ready(function() {
            const table = window.LaravelDataTables["blogcategory-table"];

            $.fn.dataTable.ext.buttons.create = {
                text: "Create",
                action: function(e, dt, node, config) {
                    $('#modal-create form input[name=name]').val('');
                    $('#modal-create').modal('show')
                },
            }

            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: "Delete selected",
                url: "{{ route('admin.blog-categories.massDestroy') }}",
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
                        confirmButtonText: "Delete",
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

            table.on('click', '.btn-edit', function(e) {
                const data = table.row($(this).parents('tr')).data();

                $('#modal-edit form').attr('action', $(this).data('url'));
                $('#modal-edit form input[name=name]').val(data.name);
                $('#modal-edit').modal('show');
            });

            table.on('click', '.btn-delete', function(e) {
                const data = table.row($(this).parents('tr')).data();

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                "x-csrf-token": _token,
                            },
                            method: "POST",
                            url: $(this).data('url'),
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

            $('#modal-create form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    headers: {
                        "x-csrf-token": _token,
                    },
                    method: "POST",
                    url: "{{ route('admin.blog-categories.store') }}",
                    data: $(this).serialize(),
                    success: function(data) {
                        $('#modal-create').modal('hide');
                        toastr.success(data.message);
                        table.ajax.reload();
                    },
                });
            });

            $('#modal-edit form').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    headers: {
                        "x-csrf-token": _token,
                    },
                    method: "PUT",
                    url: $(this).attr('action'),
                    data: $(this).serialize(),
                    success: function(data) {
                        $('#modal-edit').modal('hide');
                        toastr.success(data.message);
                        table.ajax.reload();
                    },
                });
            });
        })
    </script>
@endSection
