@extends('layouts.admin')

@section('page_title', 'Add Newsletter Subscriber')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Newsletter' => null,
            'Subscribers' => route('admin.newsletters.index'),
            'Add Subscriber' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-header">
            <h3 class="card-title">Add New Subscriber</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.newsletters.store') }}"
                  id="newsletterForm"
                  method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   required
                                   type="email"
                                   value="{{ old('email') }}">
                            @error('email')
                                <span class="invalid-feedback"
                                      role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input class="form-control @error('name') is-invalid @enderror"
                                   id="name"
                                   name="name"
                                   placeholder="Optional"
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
                </div>
                <div class="form-group">
                    <button class="btn btn-primary"
                            type="submit">
                        <i class="bi bi-plus-circle mr-1"></i> Add Subscriber
                    </button>
                    <a class="btn btn-secondary"
                       href="{{ route('admin.newsletters.index') }}">
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
            $('#newsletterForm').on('submit', function(e) {
                e.preventDefault();

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
                                "{{ route('admin.newsletters.index') }}";
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
