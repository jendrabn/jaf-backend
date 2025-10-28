@extends('layouts.admin')

@section('page_title', 'Campaign Detail')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Campaigns' => route('admin.campaigns.index'),
            'Campaign Detail' => null,
        ],
    ])
@endsection

@section('content')

    <div class="card shadow-lg">
        <div class="card-header border-bottom-0">
            <div class="card-tools">
                <a class="btn btn-primary"
                   href="{{ route('admin.campaigns.edit', $campaign) }}">
                    <i class="bi bi-pencil mr-1"></i> Edit
                </a>
                <button class="btn btn-success"
                        data-url="{{ route('admin.campaigns.send_all', $campaign) }}"
                        id="btn-send-all"
                        type="button">
                    <i class="bi bi-send-check mr-1"></i> Send to all
                </button>
                <button class="btn btn-info"
                        data-url="{{ route('admin.campaigns.test_send', $campaign) }}"
                        id="btn-test-send"
                        type="button">
                    <i class="bi bi-envelope-paper mr-1"></i> Test send
                </button>
                <button class="btn btn-danger"
                        data-url="{{ route('admin.campaigns.destroy', $campaign) }}"
                        id="btn-delete"
                        type="button">
                    <i class="bi bi-trash mr-1"></i> Delete
                </button>
                <a class="btn btn-default"
                   href="{{ route('admin.campaigns.index') }}">
                    <i class="bi bi-arrow-left mr-1"></i> Back to list
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive mb-3">
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>NAME</th>
                            <td>{{ $campaign->name }}</td>
                        </tr>

                        <tr>
                            <th>SUBJECT</th>
                            <td>{{ $campaign->subject }}</td>
                        </tr>

                        <tr>
                            <th>STATUS</th>
                            <td>
                                {!! badgeLabel(strtoupper($campaign->status->label()), $campaign->status->color()) !!}
                            </td>
                        </tr>

                        <tr>
                            <th>SCHEDULED AT</th>
                            <td>{{ $campaign->scheduled_at }}</td>
                        </tr>

                        <tr>
                            <th>SENT AT</th>
                            <td>{{ $campaign->sent_at }}</td>
                        </tr>

                        <tr>
                            <th>RECIPIENTS STATS</th>
                            <td>
                                @php
                                    $queued = $stats['queued'] ?? 0;
                                    $sent = $stats['sent'] ?? 0;
                                    $failed = $stats['failed'] ?? 0;
                                    $opened = $stats['opened'] ?? 0;
                                    $clicked = $stats['clicked'] ?? 0;
                                    $total = $queued + $sent + $failed + $opened + $clicked;
                                @endphp
                                <div class="d-flex flex-wrap align-items-center"
                                     style="gap: .5rem;">
                                    <span class="badge badge-secondary">Total: {{ $total }}</span>
                                    <span class="badge badge-info">Queued: {{ $queued }}</span>
                                    <span class="badge badge-success">Sent: {{ $sent }}</span>
                                    <span class="badge badge-danger">Failed: {{ $failed }}</span>
                                    <span class="badge badge-warning">Opened: {{ $opened }}</span>
                                    <span class="badge badge-primary">Clicked: {{ $clicked }}</span>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>CONTENT</th>
                            <td>
                                <div class="border rounded p-3">
                                    {!! $campaign->content !!}
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>DATE & TIME CREATED</th>
                            <td>{{ $campaign->created_at }}</td>
                        </tr>

                        <tr>
                            <th>DATE & TIME UPDATED</th>
                            <td>{{ $campaign->updated_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p class="text-muted mb-0">
                The content above is rendered as HTML and will be used for the email body.
            </p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sendAllBtn = document.getElementById('btn-send-all');
            const testSendBtn = document.getElementById('btn-test-send');
            const deleteBtn = document.getElementById('btn-delete');

            sendAllBtn.addEventListener('click', function(e) {
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
                                window.location.reload();
                            },
                        });
                    }
                });
            });

            testSendBtn.addEventListener('click', function(e) {
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

            deleteBtn.addEventListener('click', function(e) {
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
                                window.location.href =
                                    "{{ route('admin.campaigns.index') }}";
                            },
                        });
                    }
                });
            });
        });
    </script>
@endsection
