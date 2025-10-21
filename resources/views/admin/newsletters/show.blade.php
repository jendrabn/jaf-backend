@extends('layouts.admin')

@section('page_title', 'Newsletter Subscriber Details')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Newsletter' => null,
            'Subscribers' => route('admin.newsletters.index'),
            'Subscriber Details' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title">Subscriber Details</h3>
            <div class="card-tools">
                <a class="btn btn-primary btn-sm"
                   href="{{ route('admin.newsletters.edit', $newsletter->id) }}">
                    <i class="bi bi-pencil mr-1"></i> Edit
                </a>
                <a class="btn btn-secondary btn-sm"
                   href="{{ route('admin.newsletters.index') }}">
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
                            <td>{{ $newsletter->id }}</td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>
                                {{ $newsletter->email }}
                                <a class="ml-2 btn btn-sm btn-outline-info"
                                   href="mailto:{{ $newsletter->email }}">
                                    <i class="bi bi-envelope"></i> Send Email
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $newsletter->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @php
                                    $badgeClass = match ($newsletter->status) {
                                        'pending' => 'bg-warning',
                                        'confirmed' => 'bg-success',
                                        'unsubscribed' => 'bg-danger',
                                        'bounced' => 'bg-secondary',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">{{ strtoupper($newsletter->status) }}</span>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-striped">
                        <tr>
                            <th width="150">Subscribe Date:</th>
                            <td>{{ $newsletter->subscribed_at ? $newsletter->subscribed_at->format('d M Y H:i:s') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Unsubscribe Date:</th>
                            <td>{{ $newsletter->unsubscribed_at ? $newsletter->unsubscribed_at->format('d M Y H:i:s') : '-' }}
                            </td>
                        </tr>
                        <tr>
                            <th>Created At:</th>
                            <td>{{ $newsletter->created_at->format('d M Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At:</th>
                            <td>{{ $newsletter->updated_at->format('d M Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h5>Unsubscribe Token</h5>
                    <div class="input-group">
                        <input class="form-control"
                               readonly
                               type="text"
                               value="{{ $newsletter->unsubscribe_token }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary"
                                    onclick="copyToken()"
                                    type="button">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        Unsubscribe Link: {{ url('/newsletter/unsubscribe/' . $newsletter->unsubscribe_token) }}
                    </small>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        function copyToken() {
            var tokenInput = document.querySelector('input[value="{{ $newsletter->unsubscribe_token }}"]');
            tokenInput.select();
            document.execCommand('copy');
            toastr.success('Token copied to clipboard');
        }
    </script>
@endsection
