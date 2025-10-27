@extends('layouts.admin')

@section('page_title', 'Newsletter Subscribers')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Newsletter' => null,
            'Subscribers' => null,
        ],
    ])
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $statistics['total'] }}</h3>
                    <p>Total Subscribers</p>
                </div>
                <div class="icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $statistics['confirmed'] }}</h3>
                    <p>Confirmed</p>
                </div>
                <div class="icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $statistics['pending'] }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon">
                    <i class="bi bi-clock-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $statistics['unsubscribed'] }}</h3>
                    <p>Unsubscribed</p>
                </div>
                <div class="icon">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg">
        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-bordered datatable ajaxTable mt-3']) }}
            </div>
        </div>
    </div>

    <!-- Create Subscriber Modal -->
    <div class="modal fade"
         id="createSubscriberModal"
         tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.newsletters.store') }}"
                      id="createSubscriberForm"
                      method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Subscriber</h5>
                        <button class="btn-close"
                                data-bs-dismiss="modal"
                                type="button"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label"
                                   for="email">Email Address</label>
                            <input class="form-control"
                                   id="email"
                                   name="email"
                                   required
                                   type="email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label"
                                   for="name">Name (Optional)</label>
                            <input class="form-control"
                                   id="name"
                                   name="name"
                                   type="text">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary"
                                data-bs-dismiss="modal"
                                type="button">Cancel</button>
                        <button class="btn btn-success"
                                type="submit">Add Subscriber</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: "Delete selected",
            url: "{{ route('admin.newsletters.massDestroy') }}",
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
                    toastr.warning("No rows selected");

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
    </script>

    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(function() {
            const table = window.LaravelDataTables["dataTable-newsletters"];

            table.on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let url = $(this).attr("href");

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

            $('a[data-toggle="tab"]').on("shown.bs.tab click", function(e) {
                $($.fn.dataTable.tables(true)).DataTable().columns.adjust();
            });

            let visibleColumnsIndexes = null;

            $(".datatable thead").on("input", ".search", function() {
                let strict = $(this).attr("strict") || false;
                let value =
                    strict && this.value ? "^" + this.value + "$" : this.value;

                let index = $(this).parent().index();
                if (visibleColumnsIndexes !== null) {
                    index = visibleColumnsIndexes[index];
                }

                table.column(index).search(value, strict).draw();
            });

            table.on("column-visibility.dt", function(e, settings, column, state) {
                visibleColumnsIndexes = [];

                table.columns(":visible").every(function(colIdx) {
                    visibleColumnsIndexes.push(colIdx);
                });
            });

            // Handle Create Subscriber Form
            $('#createSubscriberForm').on('submit', function(e) {
                e.preventDefault();

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        toastr.success(response.message);
                        $('#createSubscriberModal').modal('hide');
                        $('#createSubscriberForm')[0].reset();
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = '';
                        for (let key in errors) {
                            errorMessages += errors[key] + '\n';
                        }
                        toastr.error(errorMessages);
                    }
                });
            });

            // Override the create button to open modal
            table.button().add(0, {
                text: '<i class="bi bi-plus-circle mr-1"></i> Add Subscriber',
                className: 'btn btn-success',
                action: function(e, dt, node, config) {
                    $('#createSubscriberModal').modal('show');
                }
            });
        });
    </script>
@endsection
