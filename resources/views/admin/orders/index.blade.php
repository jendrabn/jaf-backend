@extends('layouts.admin')

@section('page_title', 'Order')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Order' => null,
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

    @include('admin.orders.partials.modal-filter')
@endsection

@section('scripts')
    <script>
        // Define helpers & custom buttons in global scope BEFORE DataTables initialization
        function getSelectedIds(dt) {
            return $.map(dt.rows({
                selected: true
            }).data(), function(entry) {
                return entry.id;
            });
        }

        $.fn.dataTable.ext.buttons.filter = {
            text: '<i class="bi bi-filter"></i> Filter',
            action: function(e, dt) {
                $('#modal-filter').modal('show');
            },
        };

        $.fn.dataTable.ext.buttons.printInvoice = {
            text: '<i class="bi bi-receipt"></i> Invoice',
            url: "{{ route('admin.orders.invoices') }}",
            action: function(e, dt, node, config) {
                const ids = getSelectedIds(dt);
                if (!ids.length) {
                    toastr.warning('No rows selected', 'Warning');
                    return;
                }

                $.ajax({
                    headers: {
                        'x-csrf-token': _token
                    },
                    method: 'POST',
                    url: config.url,
                    data: {
                        ids
                    },
                    beforeSend: function() {
                        $(node).prop('disabled', true);
                    },
                    success: function(resp) {
                        const base64pdf = resp.data;
                        const binary = atob(base64pdf);
                        const array = Uint8Array.from(binary, c => c.charCodeAt(0));
                        const blob = new Blob([array], {
                            type: 'application/pdf'
                        });

                        const link = document.createElement('a');
                        link.href = URL.createObjectURL(blob);
                        link.download = resp.filename;
                        link.click();
                    },
                    complete: function() {
                        $(node).prop('disabled', false);
                    },
                });
            },
        };

        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: 'Delete selected',
            url: "{{ route('admin.orders.massDestroy') }}",
            action: function(e, dt, node, config) {
                const ids = getSelectedIds(dt);
                if (!ids.length) {
                    toastr.warning('No rows selected', 'Warning');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3"></i> Delete',
                }).then(function(result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: config.url,
                            data: {
                                ids,
                                _method: 'DELETE'
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
            // Track visible columns for header search alignment
            let visibleColumnsIndexes = null;

            // Date range picker
            const $dr = $('input[name="daterange"]');
            if ($dr.length) {
                $dr.daterangepicker({
                    opens: 'left',
                    maxDate: moment(),
                    autoUpdateInput: false,
                    ranges: {
                        Today: [moment(), moment()],
                        Yesterday: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [
                            moment().subtract(1, 'month').startOf('month'),
                            moment().subtract(1, 'month').endOf('month'),
                        ],
                    },
                }, function(start, end) {
                    $dr.val(start.format('YYYY-MM-DD') + ' - ' + end.format('YYYY-MM-DD'));
                });
            }

            // Column header search
            $('.datatable thead').on('input', '.search', function() {
                const strict = $(this).attr('strict') || false;
                const value = strict && this.value ? '^' + this.value + '$' : this.value;

                let index = $(this).parent().index();
                if (visibleColumnsIndexes !== null) {
                    index = visibleColumnsIndexes[index];
                }
                const dt = $('#dataTable-orders').DataTable();
                dt.column(index).search(value, strict).draw();
            });

            // Delete action (delegated, single handler)
            $('#dataTable-orders').on('click', '.btn-delete', function(e) {
                e.preventDefault();
                const url = $(this).attr('href');
                const dt = $('#dataTable-orders').DataTable();

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-trash3"></i> Delete',
                }).then(function(result) {
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
                                toastr.success(data.message);
                                dt.ajax.reload();
                            },
                        });
                    }
                });
            });

            // Maintain visible columns map
            $('#dataTable-orders').on('column-visibility.dt', function() {
                const dt = $('#dataTable-orders').DataTable();
                visibleColumnsIndexes = [];
                dt.columns(':visible').every(function(colIdx) {
                    visibleColumnsIndexes.push(colIdx);
                });
            });

            // Filter buttons
            $('#btn-filter').on('click', function() {
                const dt = $('#dataTable-orders').DataTable();
                dt.ajax.reload();
            });

            $('#btn-reset-filter').on('click', function() {
                $('#form-filter')[0].reset();
                const dt = $('#dataTable-orders').DataTable();
                dt.ajax.reload();
            });
        });
    </script>
@endsection
