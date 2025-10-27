@extends('layouts.admin')

@section('page_title', 'Support - Contact Messages')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Support' => null,
            'Contact Messages' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h3 class="card-title mb-0">Contact Messages</h3>

            <div class="card-tools">
                <!-- Filters moved to the DataTable "Filter" button which opens the modal below -->
            </div>
        </div>

        <div class="card-body">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'style' => 'width:100%']) !!}
        </div>
    </div>

    <!-- Modal Filter -->
    <div aria-hidden="true"
         aria-labelledby="modalFilterLabel"
         class="modal fade"
         id="modal-filter"
         role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-md"
             role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="modalFilterLabel"><i class="bi bi-filter mr-1"></i> Filter Contact Messages</h5>
                    <button aria-label="Close"
                            class="close"
                            data-dismiss="modal"
                            title="Close"
                            type="button">
                        <span aria-hidden="true"><i class="bi bi-x-lg"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-filter">
                        <div class="form-group">
                            <label class="small text-muted text-uppercase"
                                   for="filter_status">Status</label>
                            <select class="form-control form-control-sm"
                                    id="filter_status">
                                <option value="">All</option>
                                <option value="new">New</option>
                                <option value="in_progress">In Progress</option>
                                <option value="resolved">Resolved</option>
                                <option value="spam">Spam</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="small text-muted text-uppercase"
                                   for="filter_date_from">Date From</label>
                            <input class="form-control form-control-sm"
                                   id="filter_date_from"
                                   type="date" />
                        </div>

                        <div class="form-group mb-0">
                            <label class="small text-muted text-uppercase"
                                   for="filter_date_to">Date To</label>
                            <input class="form-control form-control-sm"
                                   id="filter_date_to"
                                   type="date" />
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary"
                            id="btn-filter-reset"
                            type="button">Reset</button>
                    <button class="btn btn-primary"
                            id="btn-filter-apply"
                            type="button">Apply</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        #filters .form-group label {
            letter-spacing: .06em;
            font-size: .75rem;
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 0;
        }
    </style>
@endsection

@section('scripts')
    <script>
        // Define Bulk Delete button extension
        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: "Delete selected",
            url: "{{ route('admin.messages.massDestroy') }}",
            action: function(e, dt, node, config) {
                let ids = $.map(
                    dt
                    .rows({
                        selected: true
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
                                "x-csrf-token": _token
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

        // Define Filter button extension to open modal
        $.fn.dataTable.ext.buttons.filter = {
            text: "<i class='bi bi-filter mr-1'></i> Filter",
            action: function(e, dt, node, config) {
                $('#modal-filter').modal('show');
            },
        };
    </script>

    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var table = window.LaravelDataTables ? window.LaravelDataTables['contact-messages-table'] : null;

            function attachFilters() {
                if (!table) return;

                // Inject filter params before each request based on modal fields
                table.on('preXhr.dt', function(e, settings, data) {
                    data.status = document.getElementById('filter_status').value || '';
                    data.date_from = document.getElementById('filter_date_from').value || '';
                    data.date_to = document.getElementById('filter_date_to').value || '';
                });
            }

            attachFilters();

            // Apply filter from modal
            document.getElementById('btn-filter-apply').addEventListener('click', function() {
                if (table) {
                    $('#modal-filter').modal('hide');
                    table.draw();
                }
            });

            // Reset filter from modal
            document.getElementById('btn-filter-reset').addEventListener('click', function() {
                document.getElementById('filter_status').value = '';
                document.getElementById('filter_date_from').value = '';
                document.getElementById('filter_date_to').value = '';
                if (table) table.draw();
            });
        });
    </script>
@endsection
