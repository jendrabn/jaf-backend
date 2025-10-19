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
                <form class="form-inline"
                      id="filters">
                    <div class="form-group mr-2 mb-2">
                        <label class="mr-2 small text-muted text-uppercase"
                               for="status">Status</label>
                        <select class="form-control form-control-sm"
                                id="status">
                            <option value="">All</option>
                            <option value="new">New</option>
                            <option value="in_progress">In Progress</option>
                            <option value="resolved">Resolved</option>
                            <option value="spam">Spam</option>
                        </select>
                    </div>

                    <div class="form-group mr-2 mb-2">
                        <label class="mr-2 small text-muted text-uppercase"
                               for="date_from">Date From</label>
                        <input class="form-control form-control-sm"
                               id="date_from"
                               type="date" />
                    </div>

                    <div class="form-group mr-2 mb-2">
                        <label class="mr-2 small text-muted text-uppercase"
                               for="date_to">Date To</label>
                        <input class="form-control form-control-sm"
                               id="date_to"
                               type="date" />
                    </div>

                    <button class="btn btn-sm btn-outline-primary mb-2"
                            id="btn-filter"
                            type="button">
                        Apply
                    </button>
                    <button class="btn btn-sm btn-outline-secondary mb-2 ml-1"
                            id="btn-reset"
                            type="button">
                        Reset
                    </button>
                </form>
            </div>
        </div>

        <div class="card-body">
            {!! $dataTable->table(['class' => 'table table-striped table-bordered w-100', 'style' => 'width:100%']) !!}
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
    {!! $dataTable->scripts() !!}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var table = window.LaravelDataTables ? window.LaravelDataTables['contact-messages-table'] : null;

            function attachFilters() {
                if (!table) return;

                // Inject filter params before each request
                table.on('preXhr.dt', function(e, settings, data) {
                    data.status = document.getElementById('status').value || '';
                    data.date_from = document.getElementById('date_from').value || '';
                    data.date_to = document.getElementById('date_to').value || '';
                });
            }

            attachFilters();

            document.getElementById('btn-filter').addEventListener('click', function() {
                if (table) table.draw();
            });

            document.getElementById('btn-reset').addEventListener('click', function() {
                document.getElementById('status').value = '';
                document.getElementById('date_from').value = '';
                document.getElementById('date_to').value = '';
                if (table) table.draw();
            });
        });
    </script>
@endsection
