@extends('layouts.admin')

@section('page_title', 'Email Campaigns')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Newsletter' => null,
            'Campaigns' => null,
        ],
    ])
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $campaigns->total() }}</h3>
                    <p>Total Campaigns</p>
                </div>
                <div class="icon">
                    <i class="bi bi-envelope-paper-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $campaigns->where('status', 'sent')->count() }}</h3>
                    <p>Sent</p>
                </div>
                <div class="icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $campaigns->where('status', 'draft')->count() }}</h3>
                    <p>Draft</p>
                </div>
                <div class="icon">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $campaigns->where('status', 'scheduled')->count() }}</h3>
                    <p>Scheduled</p>
                </div>
                <div class="icon">
                    <i class="bi bi-clock-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title">Campaigns</h3>
            <div class="card-tools">
                @can('campaigns.create')
                    <a class="btn btn-primary btn-sm"
                       href="{{ route('admin.campaigns.create') }}">
                        <i class="bi bi-plus-circle mr-1"></i> Create Campaign
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            @if ($campaigns->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Recipients</th>
                                <th>Sent</th>
                                <th>Open Rate</th>
                                <th>Click Rate</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($campaigns as $campaign)
                                <tr>
                                    <td>{{ $campaign->id }}</td>
                                    <td>{{ $campaign->name }}</td>
                                    <td>{{ $campaign->subject }}</td>
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
                                    <td>{{ count($campaign->recipients ?? []) }}</td>
                                    <td>{{ $campaign->total_sent }}</td>
                                    <td>{{ $campaign->open_rate }}%</td>
                                    <td>{{ $campaign->click_rate }}%</td>
                                    <td>{{ $campaign->created_at->format('d M Y') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            @can('campaigns.show')
                                                <a class="btn btn-sm btn-info"
                                                   href="{{ route('admin.campaigns.show', $campaign->id) }}"
                                                   title="View">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            @endcan

                                            @if ($campaign->status === 'draft' || $campaign->status === 'scheduled')
                                                @can('campaigns.edit')
                                                    <a class="btn btn-sm btn-warning"
                                                       href="{{ route('admin.campaigns.edit', $campaign->id) }}"
                                                       title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan

                                                @can('campaigns.send')
                                                    <button class="btn btn-sm btn-success"
                                                            onclick="sendCampaign({{ $campaign->id }})"
                                                            title="Send">
                                                        <i class="bi bi-send"></i>
                                                    </button>
                                                @endcan
                                            @endif

                                            @can('campaigns.test')
                                                <a class="btn btn-sm btn-info"
                                                   href="#"
                                                   onclick="testSend({{ $campaign->id }})"
                                                   title="Test Send">
                                                    <i class="bi bi-envelope"></i>
                                                </a>
                                            @endcan

                                            @if ($campaign->status === 'draft' || $campaign->status === 'scheduled' || $campaign->status === 'failed')
                                                @can('campaigns.delete')
                                                    <a class="btn btn-sm btn-danger"
                                                       href="{{ route('admin.campaigns.destroy', $campaign->id) }}"
                                                       onclick="return confirm('Are you sure you want to delete this campaign?')"
                                                       title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $campaigns->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-envelope-paper"
                       style="font-size: 4rem; color: #ccc;"></i>
                    <h4 class="mt-3">No campaigns found</h4>
                    <p class="text-muted">Create your first email campaign to get started.</p>
                    @can('campaigns.create')
                        <a class="btn btn-primary"
                           href="{{ route('admin.campaigns.create') }}">
                            <i class="bi bi-plus-circle mr-1"></i> Create Campaign
                        </a>
                    @endcan
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
