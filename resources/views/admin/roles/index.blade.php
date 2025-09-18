@extends('layouts.admin')

@section('page_title', 'Role & Permission')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Role & Permission' => null,
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
@endsection

@section('scripts')
    {{ $dataTable->scripts(attributes: ['type' => 'text/javascript']) }}

    <script>
        $(function() {
            const table = window.LaravelDataTables["role-datatable"];

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
                                toastr.success('Role deleted successfully.');
                                table.ajax.reload();
                            },
                        });
                    }
                });
            });
        })
    </script>
@endsection
