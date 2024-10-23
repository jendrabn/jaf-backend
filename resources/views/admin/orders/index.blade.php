@extends('layouts.admin', ['title' => 'Order List'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order List</h3>
        </div>

        <div class="card-body">
            <ul class="nav nav-pills nav-fill mb-3 border-bottom"
                id="nav-pills-status">
                <li class="nav-item">
                    <a class="nav-link active"
                       data-status=""
                       href="#">All </a>
                </li>
                @foreach (\App\Models\Order::STATUSES as $key => $status)
                    <li class="nav-item">
                        <a class="nav-link"
                           data-status="{{ $key }}"
                           href="#">{{ $status['label'] }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-sm table-striped table-bordered datatable ajaxTable']) }}
            </div>
        </div>
    </div>

    @include('admin.orders.partials.modal-filter')
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css"
          rel="stylesheet"
          type="text/css" />
@endsection

@section('scripts')
    <script type="text/javascript"
            src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
    <script>
        $(function() {
            $('input[name="daterange"]').daterangepicker({
                    opens: "left",
                    maxDate: moment(new Date()),
                    autoUpdateInput: false,
                    ranges: {
                        Today: [moment(), moment()],
                        Yesterday: [
                            moment().subtract(1, "days"),
                            moment().subtract(1, "days"),
                        ],
                        "Last 7 Days": [moment().subtract(6, "days"), moment()],
                        "Last 30 Days": [moment().subtract(29, "days"), moment()],
                        "This Month": [
                            moment().startOf("month"),
                            moment().endOf("month"),
                        ],
                        "Last Month": [
                            moment().subtract(1, "month").startOf("month"),
                            moment().subtract(1, "month").endOf("month"),
                        ],
                    },
                },
                function(start, end, label) {
                    $('input[name="daterange"]').val(
                        start.format("YYYY-MM-DD") +
                        " - " +
                        end.format("YYYY-MM-DD")
                    );
                }
            );

            $("#nav-pills-status .nav-link").on("click", function(e) {
                e.preventDefault();

                $("#nav-pills-status .nav-link").removeClass("active");

                $(this).addClass("active");

                table.ajax.reload();
            });

            $.fn.dataTable.ext.buttons.filter = {
                text: '<i class="fas fa-filter"></i> Filter',
                action: function(e, dt, node, config) {
                    $("#modal-filter").modal("show");
                },
            };

            $.fn.dataTable.ext.buttons.printInvoice = {
                text: '<i class="fa-solid fa-file-invoice"></i> Invoice',
                url: "{{ route('admin.orders.invoices') }}",
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

                    $.ajax({
                        headers: {
                            "x-csrf-token": _token,
                        },
                        method: "POST",
                        url: config.url,
                        data: {
                            ids: ids,
                        },
                        beforeSend: function() {
                            $(node).attr("disabled", true);
                        },
                        success: function(data) {
                            const base64pdf = data.data;
                            const binary = atob(base64pdf);
                            const array = Uint8Array.from(binary, (char) =>
                                char.charCodeAt(0)
                            );
                            const blob = new Blob([array], {
                                type: "application/pdf",
                            });

                            const link = document.createElement("a");
                            link.href = URL.createObjectURL(blob);
                            link.download = data.filename;
                            link.click();
                        },
                        complete: function() {
                            $(node).attr("disabled", false);
                        },
                    });
                },
            };

            $.fn.dataTable.ext.buttons.bulkDelete = {
                text: "Delete selected",
                url: "{{ route('admin.orders.massDestroy') }}",
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

            const table = LaravelDataTables["dataTable-orders"];

            table.on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let url = $(this).attr("href");

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

            table.on("click", ".btn-delete", function(e) {
                e.preventDefault();

                let url = $(this).attr("href");

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

            table.on("column-visibility.dt", function(e, settings, column, state) {
                visibleColumnsIndexes = [];
                table.columns(":visible").every(function(colIdx) {
                    visibleColumnsIndexes.push(colIdx);
                });
            });

            $("#btn-filter").on("click", function() {
                table.ajax.reload();
            });

            $("#btn-reset-filter").on("click", function() {
                $("#form-filter")[0].reset();
                table.ajax.reload();
            });
        });
    </script>
@endsection
