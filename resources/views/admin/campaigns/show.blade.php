@extends('layouts.admin')

@section('page_title', 'Campaign Details')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Newsletter' => null,
            'Campaigns' => route('admin.campaigns.index'),
            'Campaign Details' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title">Campaign Details</h3>
            <div class="card-tools">
                @if ($campaign->status === 'draft' || $campaign->status === 'scheduled')
                    @can('campaigns.edit')
                        <a class="btn btn-warning btn-sm"
                           href="{{ route('admin.campaigns.edit', $campaign->id) }}">
                            <i class="bi bi-pencil mr-1"></i> Edit
                        </a>
                    @endcan

                    @can('campaigns.send')
                        <button class="btn btn-success btn-sm"
                                onclick="sendCampaign({{ $campaign->id }})">
                            <i class="bi bi-send mr-1"></i> Send Now
                        </button>
                    @endcan
                @endif

                @can('campaigns.test')
                    <a class="btn btn-info btn-sm"
                       href="#"
                       onclick="testSend({{ $campaign->id }})">
                        <i class="bi bi-envelope mr-1"></i> Test Send
                    </a>
                @endcan

                <a class="btn btn-secondary btn-sm"
                   href="{{ route('admin.campaigns.index') }}">
                    <i class="bi bi-arrow-left mr-1"></i> Back
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-striped">
                        <tr>
                            <th width="150">ID:</th>
                            <td>{{ $campaign->id }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $campaign->name }}</td>
                        </tr>
                        <tr>
                            <th>Subject:</th>
                            <td>{{ $campaign->subject }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @php
                                    $badgeClass = match ($campaign->status) {
                                        'draft' => 'bg-secondary',
                                        'scheduled' => 'bg-info',
                                        'sending' => 'bg-warning',
                                        'sent' => 'bg-success',
                                        'failed' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ strtoupper($campaign->status) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped">
                        <tr>
                            <th width="150">Created At:</th>
                            <td>{{ $campaign->created_at->format('d M Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Scheduled At:</th>
                            <td>{{ $campaign->scheduled_at ? $campaign->scheduled_at->format('d M Y H:i:s') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Sent At:</th>
                            <td>{{ $campaign->sent_at ? $campaign->sent_at->format('d M Y H:i:s') : '-' }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $campaign->updated_at->format('d M Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h5>Email Content</h5>
                    <div class="border p-3 bg-light"
                         style="max-height: 300px; overflow-y: auto;">
                        {!! $campaign->content !!}
                    </div>
                </div>
            </div>

            @if ($campaign->status === 'sent')
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Campaign Statistics</h5>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3>{{ $campaign->total_sent }}</h3>
                                        <p>Total Sent</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3>{{ $campaign->total_opened }}</h3>
                                        <p>Total Opened</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3>{{ $campaign->total_clicked }}</h3>
                                        <p>Total Clicked</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h3>{{ $campaign->open_rate }}%</h3>
                                        <p>Open Rate</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function sendCampaign(campaignId) {
            Swal.fire({
                title: 'Send Campaign',
                text: 'Are you sure you want to send this campaign?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Send',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch(`{{ route('admin.campaigns.send', ':id') }}`.replace(':id', campaignId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': _token
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Failed to send campaign');
                            }
                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.value.message
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        }

        function testSend(campaignId) {
            Swal.fire({
                title: 'Test Send Campaign',
                input: 'email',
                inputLabel: 'Email Address',
                inputPlaceholder: 'Enter email to send test',
                showCancelButton: true,
                confirmButtonText: 'Send Test',
                showLoaderOnConfirm: true,
                preConfirm: (email) => {
                    return fetch(`{{ route('admin.campaigns.test', ':id') }}`.replace(':id', campaignId), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': _token
                            },
                            body: JSON.stringify({
                                email: email
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (!data.success) {
                                throw new Error(data.message || 'Failed to send test email');
                            }
                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(error.message);
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: result.value.message
                    });
                }
            });
        }
    </script>
@endsection
