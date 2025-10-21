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
    <div class="card shadow-lg">
        <div class="card-body">
            {!! $dataTable->table(['class' => 'table table-bordered table-striped w-100'], true) !!}
        </div>
    </div>

    <div aria-hidden="true"
         class="modal fade"
         id="blogCategoryModal"
         role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-lg"
             role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: '<i class="bi bi-trash3 mr-1"></i> Delete Selected',
            action: function(e, dt) {
                const ids = $.map(dt.rows({
                    selected: true
                }).data(), function(row) {
                    return row.id;
                });

                if (!ids.length) {
                    if (window.toastr) {
                        toastr.warning('No rows selected.');
                    }

                    return;
                }

                if (!confirm('Delete selected categories?')) {
                    return;
                }

                $.ajax({
                    url: "{{ route('admin.blog-categories.massDestroy') }}",
                    method: 'POST',
                    data: {
                        ids: ids,
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        dt.ajax.reload(null, false);
                        if (window.toastr) {
                            toastr.success(response?.message ?? 'Deleted successfully.');
                        }
                    },
                    error: function() {
                        if (window.toastr) {
                            toastr.error('Delete failed.');
                        }
                    }
                });
            }
        };
    </script>

    {!! $dataTable->scripts() !!}
    <script>
        $(function() {
            const table = window.LaravelDataTables && window.LaravelDataTables['blogcategory-table'] ?
                window.LaravelDataTables['blogcategory-table'] :
                $('#blogcategory-table').DataTable();
            const $modal = $('#blogCategoryModal');

            $modal.on('hidden.bs.modal', function() {
                $(this).find('.modal-content').empty();
            });

            $(document).on('click', '.buttons-create', function(e) {
                e.preventDefault();

                $.get("{{ route('admin.blog-categories.create') }}", function(html) {
                    $modal.find('.modal-content').html(html);
                    $modal.modal('show');
                });
            });

            $(document).on('click', '.btn-edit-blog-category', function() {
                const url = $(this).data('url');

                $.get(url, function(html) {
                    $modal.find('.modal-content').html(html);
                    $modal.modal('show');
                });
            });

            $(document).on('submit', '#blogCategoryForm', function(e) {
                e.preventDefault();

                const $form = $(this);
                const action = $form.attr('action');
                const method = $form.find('input[name="_method"]').val() || 'POST';
                const data = $form.serialize();

                $form.find('.is-invalid').removeClass('is-invalid');
                $form.find('.invalid-feedback').addClass('d-none').text('');

                $.ajax({
                    url: action,
                    method: method,
                    data: data,
                    success: function(response) {
                        $modal.modal('hide');
                        table.ajax.reload(null, false);
                        if (window.toastr) {
                            toastr.success(response?.message ?? 'Saved successfully.');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;

                            Object.keys(errors).forEach(function(field) {
                                const $input = $form.find('[name="' + field + '"]');
                                $input.addClass('is-invalid');
                                $input.siblings('.invalid-feedback').removeClass(
                                    'd-none').text(errors[field][0]);
                            });
                        } else {
                            if (window.toastr) {
                                toastr.error('An error occurred.');
                            }
                        }
                    }
                });
            });

            $(document).on('click', '.btn-delete-blog-category', function() {
                if (!confirm('Delete this category?')) {
                    return;
                }

                const url = $(this).data('url');

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        table.ajax.reload(null, false);
                        if (window.toastr) {
                            toastr.success(response?.message ?? 'Deleted successfully.');
                        }
                    },
                    error: function() {
                        if (window.toastr) {
                            toastr.error('Delete failed.');
                        }
                    }
                });
            });
        });
    </script>
@endsection
