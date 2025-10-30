@extends('layouts.admin')

@section('page_title', 'Subscribers')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Subscribers' => null,
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
         id="subscriberModal"
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

                Swal.fire({
                    title: 'Delete selected?',
                    text: 'Selected subscribers will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3 mr-1"></i> Delete',
                    showLoaderOnConfirm: true,
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.subscribers.massDestroy') }}",
                            method: 'POST',
                            data: {
                                ids: ids,
                                _method: 'DELETE',
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                dt.ajax.reload(null, false);
                                if (window.toastr) {
                                    toastr.success(response?.message ??
                                        'Deleted successfully.');
                                }
                            },
                            error: function() {
                                if (window.toastr) {
                                    toastr.error('Delete failed.');
                                }
                            }
                        });
                    }
                });

            }
        };
    </script>

    {!! $dataTable->scripts() !!}

    <script>
        $(function() {
            const table = window.LaravelDataTables && window.LaravelDataTables['subscribers-table'] ?
                window.LaravelDataTables['subscribers-table'] :
                $('#subscribers-table').DataTable();
            const $modal = $('#subscriberModal');

            $modal.on('hidden.bs.modal', function() {
                $(this).find('.modal-content').empty();
            });

            $(document).on('click', '.buttons-create', function(e) {
                e.preventDefault();

                $.get("{{ route('admin.subscribers.create') }}", function(html) {
                    $modal.find('.modal-content').html(html);
                    $modal.modal('show');
                });
            });

            $(document).on('click', '.btn-edit-subscriber', function() {
                const url = $(this).data('url');

                $.get(url, function(html) {
                    $modal.find('.modal-content').html(html);
                    $modal.modal('show');
                });
            });

            $(document).on('submit', '#subscriberForm', function(e) {
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

            $(document).on('click', '.btn-delete-subscriber', function() {
                Swal.fire({
                    title: 'Delete?',
                    text: 'This subscriber will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3 mr-1"></i> Delete',
                    showLoaderOnConfirm: true
                }).then((result) => {
                    if (result.isConfirmed) {
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
                                    toastr.success(response?.message ??
                                        'Deleted successfully.');
                                }
                            },
                            error: function() {
                                if (window.toastr) {
                                    toastr.error('Delete failed.');
                                }
                            }
                        });
                    }
                })


            });
        });
    </script>
@endsection
