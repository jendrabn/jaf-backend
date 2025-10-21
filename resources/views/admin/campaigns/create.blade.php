@extends('layouts.admin')

@section('page_title', 'Create Email Campaign')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Newsletter' => null,
            'Campaigns' => route('admin.campaigns.index'),
            'Create Campaign' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title">Create New Campaign</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.campaigns.store') }}"
                  id="campaignForm"
                  method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Campaign Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   required
                                   type="text"
                                   value="{{ old('name') }}">
                            @error('name')
                                <span class="invalid-feedback"
                                      role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="subject">Email Subject <span class="text-danger">*</span></label>
                            <input class="form-control @error('subject') is-invalid @enderror"
                                   id="subject"
                                   name="subject"
                                   required
                                   type="text"
                                   value="{{ old('subject') }}">
                            @error('subject')
                                <span class="invalid-feedback"
                                      role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="content">Email Content <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('content') is-invalid @enderror"
                              id="content"
                              name="content"
                              required
                              rows="10">{{ old('content') }}</textarea>
                    @error('content')
                        <span class="invalid-feedback"
                              role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">You can use HTML tags for formatting.</small>
                </div>

                <div class="form-group">
                    <label>Recipients <span class="text-danger">*</span></label>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive"
                                 style="max-height: 300px;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th width="30">
                                                <input class="form-check-input"
                                                       id="selectAll"
                                                       type="checkbox">
                                            </th>
                                            <th>Email</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($confirmedSubscribers as $subscriber)
                                            <tr>
                                                <td>
                                                    <input class="form-check-input recipient-checkbox"
                                                           name="recipients[]"
                                                           type="checkbox"
                                                           value="{{ $subscriber->id }}">
                                                </td>
                                                <td>{{ $subscriber->email }}</td>
                                                <td>{{ $subscriber->name ?? '-' }}</td>
                                                <td>
                                                    <span
                                                          class="badge bg-success">{{ strtoupper($subscriber->status) }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @error('recipients')
                                <span class="invalid-feedback d-block"
                                      role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                            <small class="form-text text-muted">Only confirmed subscribers are shown.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="scheduled_at">Schedule (Optional)</label>
                    <input class="form-control @error('scheduled_at') is-invalid @enderror"
                           id="scheduled_at"
                           name="scheduled_at"
                           type="datetime-local"
                           value="{{ old('scheduled_at') }}">
                    @error('scheduled_at')
                        <span class="invalid-feedback"
                              role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    <small class="form-text text-muted">Leave empty to send immediately.</small>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary"
                            type="submit">
                        <i class="bi bi-save mr-1"></i> Create Campaign
                    </button>
                    <a class="btn btn-secondary"
                       href="{{ route('admin.campaigns.index') }}">
                        <i class="bi bi-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Select all functionality
            $('#selectAll').on('change', function() {
                $('.recipient-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Update select all checkbox when individual checkboxes change
            $('.recipient-checkbox').on('change', function() {
                var allChecked = $('.recipient-checkbox:checked').length === $('.recipient-checkbox')
                .length;
                $('#selectAll').prop('checked', allChecked);
            });

            // Form submission
            $('#campaignForm').on('submit', function(e) {
                e.preventDefault();

                // Check if at least one recipient is selected
                if ($('.recipient-checkbox:checked').length === 0) {
                    toastr.error('Please select at least one recipient');
                    return;
                }

                var form = $(this);
                var url = form.attr('action');
                var method = 'POST';

                $.ajax({
                    headers: {
                        'x-csrf-token': _token
                    },
                    method: method,
                    url: url,
                    data: form.serialize(),
                    success: function(data) {
                        toastr.success(data.message);
                        setTimeout(function() {
                            window.location.href =
                                "{{ route('admin.campaigns.index') }}";
                        }, 1000);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorHtml =
                                '<div class="alert alert-danger"><ul class="list-unstyled mb-0">';

                            $.each(errors, function(key, value) {
                                errorHtml += '<li>' + value[0] + '</li>';
                            });

                            errorHtml += '</ul></div>';

                            form.find('.alert').remove();
                            form.prepend(errorHtml);
                        } else {
                            toastr.error(xhr.responseJSON.message || 'An error occurred');
                        }
                    }
                });
            });
        });
    </script>
@endsection
