@extends('layouts.admin')

@section('page_title', 'Courier')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Courier' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                {{ $dataTable->table(['class' => 'table table-bordered datatable ajaxTable']) }}
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}
    <script>
        $(function() {
            const table = window.LaravelDataTables["courier-table"];

            // update is_active
            table.on('draw', function() {
                $('.toggle-status').change(function() {
                    let isActive = $(this).is(':checked') ? 'active' : 'inactive';
                    let courierId = $(this).data('id');

                    $.ajax({
                        headers: {
                            'x-csrf-token': _token
                        },
                        method: 'POST',
                        url: `/admin/couriers/${courierId}`,
                        data: {
                            is_active: isActive,
                            _method: 'PUT'
                        },
                        success: function() {
                            toastr.success('Status updated successfully', 'Success');

                            table.ajax.reload(null, false);
                        },
                        error: function() {
                            toastr.error('Failed to update status', 'Error');
                        }
                    });
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
