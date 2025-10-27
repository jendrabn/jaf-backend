@extends('layouts.admin')

@section('page_title', 'Campaigns')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Campaigns' => null,
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
    <script type="text/javascript">
        // Register DataTables custom button: bulkDelete before table initialization
        if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.buttons) {
            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: '<i class="bi bi-trash3 mr-1"></i> Delete Selected',
                className: 'btn btn-danger',
                action: function(e, dt, node, config) {
                    const rows = dt.rows({
                        selected: true
                    }).data().toArray();
                    const ids = rows.map(r => r.id);

                    if (!ids.length) {
                        toastr.warning('No campaigns selected.');
                        return;
                    }

                    Swal.fire({
                        title: 'Delete selected?',
                        text: 'Selected campaigns will be deleted.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Delete',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const destroyTemplate = "{{ route('admin.campaigns.destroy', 0) }}";
                            const requests = ids.map(id => $.ajax({
                                headers: {
                                    'x-csrf-token': _token
                                },
                                method: 'POST',
                                url: destroyTemplate.replace('/0', '/' + id),
                                data: {
                                    _method: 'DELETE'
                                },
                            }));

                            Promise.allSettled(requests).then(() => {
                                toastr.success('Selected campaigns deleted.');
                                dt.ajax.reload();
                            });
                        }
                    });
                }
            };
        }
    </script>
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script type="text/javascript">
        document.addEventListener('DOMContentLoaded', function() {
            const table = window.LaravelDataTables['campaigns-table'];

            // Bind "Create Campaign" button to route
            $(document).on('click', '.buttons-create', function() {
                window.location.href = "{{ route('admin.campaigns.create') }}";
            });

            // Bulk delete selected campaigns
            $(document).on('click', '.buttons-bulkDelete', function() {
                const rows = table.rows({
                    selected: true
                }).data().toArray();
                const ids = rows.map(r => r.id);

                if (!ids.length) {
                    toastr.warning('No campaigns selected.');
                    return;
                }

                Swal.fire({
                    title: 'Delete selected?',
                    text: 'Selected campaigns will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const destroyTemplate = "{{ route('admin.campaigns.destroy', 0) }}";
                        for (const id of ids) {
                            const url = destroyTemplate.replace('/0', '/' + id);

                            try {
                                await $.ajax({
                                    headers: {
                                        'x-csrf-token': _token
                                    },
                                    method: 'POST',
                                    url: url,
                                    data: {
                                        _method: 'DELETE'
                                    },
                                });
                            } catch (e) {
                                console.error(e);
                            }
                        }

                        toastr.success('Selected campaigns deleted.');
                        table.ajax.reload();
                    }
                });
            });

            // Delete campaign
            table.on('click', '.btn-delete', function(e) {
                e.preventDefault();

                const url = this.dataset.url;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This campaign will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: url,
                            data: {
                                _method: 'DELETE'
                            },
                            success: function(data) {
                                toastr.success(data.message ||
                                    'Campaign deleted successfully.');
                                table.ajax.reload();
                            },
                        });
                    }
                });
            });

            // Send to all subscribers (queued)
            table.on('click', '.btn-send-all', function(e) {
                e.preventDefault();

                const url = this.dataset.url;

                Swal.fire({
                    title: 'Send to all subscribers?',
                    text: 'This will queue emails for all subscribed users.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Send',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: url,
                            success: function(data) {
                                toastr.success(data.message ||
                                    'Campaign sending queued.');
                                table.ajax.reload();
                            },
                        });
                    }
                });
            });

            // Test send to a single email (queued)
            table.on('click', '.btn-test-send', function(e) {
                e.preventDefault();

                const url = this.dataset.url;

                Swal.fire({
                    title: 'Test send',
                    input: 'email',
                    inputLabel: 'Enter recipient email for test',
                    inputPlaceholder: 'email@example.com',
                    showCancelButton: true,
                    confirmButtonText: 'Queue Test Email',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Email is required';
                        }
                        return null;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: url,
                            data: {
                                email: result.value
                            },
                            success: function(data) {
                                toastr.success(data.message || 'Test email queued.');
                            },
                        });
                    }
                });
            });
        });
    </script>
@endsection
