@extends('layouts.admin')

@section('page_title', 'Product Coupon')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Product Coupon' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            {{ $dataTable->table(['class' => 'table table-bordered datatable ajaxTable']) }}
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let table;

        $.fn.dataTable.ext.buttons.bulkDelete = {
            text: 'Delete selected',
            url: "{{ route('admin.coupons.massDestroy') }}",
            action: function(e, dt, button, config) {
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
                    cancelButtonText: "Cancel",
                }).then(function(result) {

                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                "x-csrf-token": _token,
                            },
                            method: "POST",
                            url: config.url,
                            data: {
                                ids: ids,
                            },
                            success: function() {
                                toastr.success('Coupons deleted successfully.');
                                table.ajax.reload();
                            },
                            error: function() {
                                toastr.error("Something went wrong", 'Error');
                            }
                        })
                    }
                });
            }
        };
    </script>

    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
    <script>
        $(function() {
            table = window.LaravelDataTables["datatable-coupon"];

            table.on('click', '.btn-delete', function(e) {
                e.preventDefault();

                const url = $(this).attr('href');

                Swal.fire({
                    title: "Are you sure?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Delete",
                    cancelButtonText: "Cancel",
                }).then(function(result) {

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
                            success: function() {
                                toastr.success('Coupons deleted successfully.');
                                table.ajax.reload();
                            },
                            error: function() {
                                toastr.error("Something went wrong", 'Error');
                            }

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
        });
    </script>
@endsection
