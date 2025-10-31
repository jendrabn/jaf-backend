@extends('layouts.admin')

@section('page_title', 'Edit User Notification')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'User Notifications' => route('admin.user-notifications.index'),
            'Edit User Notification' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <form action="{{ route('admin.user-notifications.update', [$userNotification->id]) }}"
              enctype="multipart/form-data"
              method="POST">
            @method('PUT')
            @csrf
            <div class="card-header border-bottom-0">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.user-notifications.index') }}">
                        <i class="bi bi-arrow-left mr-1"></i> Back to list
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="user_id">User</label>
                        <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                                data-placeholder="Select User"
                                id="user_id"
                                name="user_id"
                                style="width: 100%">
                            <option value="">All Users</option>
                            @foreach ($users as $id => $name)
                                <option @selected(old('user_id', $userNotification->user_id) == $id)
                                        value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('user_id'))
                            <span class="invalid-feedback">{{ $errors->first('user_id') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Title</label>
                        <input autocomplete="title"
                               autofocus
                               class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                               id="title"
                               name="title"
                               placeholder="e.g. Order Confirmation"
                               required
                               type="text"
                               value="{{ old('title', $userNotification->title) }}">
                        @if ($errors->has('title'))
                            <span class="invalid-feedback">{{ $errors->first('title') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-12">
                        <label class="required">Message</label>
                        <textarea class="form-control {{ $errors->has('body') ? 'is-invalid' : '' }}"
                                  id="body"
                                  name="body"
                                  placeholder="Enter notification message..."
                                  required
                                  rows="4">{{ old('body', $userNotification->body) }}</textarea>
                        @if ($errors->has('body'))
                            <span class="invalid-feedback">{{ $errors->first('body') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Category</label>
                        <select class="form-control select2 {{ $errors->has('category') ? 'is-invalid' : '' }}"
                                id="category"
                                name="category"
                                required
                                style="width: 100%">
                            <option value="">Select Category</option>
                            @foreach ($categories as $category)
                                <option @selected(old('category', $userNotification->category) == $category['value'])
                                        data-icon="{{ $category['icon'] }}"
                                        value="{{ $category['value'] }}">
                                    {{ $category['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('category'))
                            <span class="invalid-feedback">{{ $errors->first('category') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Level</label>
                        <select class="form-control select2 {{ $errors->has('level') ? 'is-invalid' : '' }}"
                                id="level"
                                name="level"
                                required
                                style="width: 100%">
                            <option value="">Select Level</option>
                            @foreach ($levels as $level)
                                <option @selected(old('level', $userNotification->level) == $level['value'])
                                        data-icon="{{ $level['icon'] }}"
                                        value="{{ $level['value'] }}">
                                    {{ $level['label'] }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('level'))
                            <span class="invalid-feedback">{{ $errors->first('level') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label for="url">URL</label>
                        <input autocomplete="url"
                               class="form-control {{ $errors->has('url') ? 'is-invalid' : '' }}"
                               id="url"
                               name="url"
                               placeholder="e.g. https://example.com/page"
                               type="url"
                               value="{{ old('url', $userNotification->url) }}">
                        @if ($errors->has('url'))
                            <span class="invalid-feedback">{{ $errors->first('url') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label for="icon">Icon</label>
                        <input autocomplete="icon"
                               class="form-control {{ $errors->has('icon') ? 'is-invalid' : '' }}"
                               id="icon"
                               name="icon"
                               placeholder="e.g. fas fa-bell"
                               type="text"
                               value="{{ old('icon', $userNotification->icon) }}">
                        @if ($errors->has('icon'))
                            <span class="invalid-feedback">{{ $errors->first('icon') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-12">
                        <label for="meta">Meta Data (JSON)</label>
                        <textarea class="form-control {{ $errors->has('meta') ? 'is-invalid' : '' }}"
                                  id="meta"
                                  name="meta"
                                  placeholder='{"key": "value"}'
                                  rows="3">{{ old('meta', $userNotification->meta ? json_encode($userNotification->meta, JSON_PRETTY_PRINT) : '') }}</textarea>
                        <small class="form-text text-muted">Enter valid JSON format for additional data</small>
                        @if ($errors->has('meta'))
                            <span class="invalid-feedback">{{ $errors->first('meta') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label>Read Status</label>
                        <div class="form-control-plaintext">
                            @if ($userNotification->read_at)
                                <span class="badge badge-success">Read</span>
                                <br><small>{{ $userNotification->read_at->format('d M Y H:i') }}</small>
                            @else
                                <span class="badge badge-warning">Unread</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Created At</label>
                        <div class="form-control-plaintext">
                            {{ $userNotification->created_at->format('d M Y H:i') }}
                        </div>
                    </div>
                </div>

            </div>
            <div class="card-footer border-top-0 d-flex gap-2 justify-content-end">
                <a class="btn btn-light"
                   href="{{ route('admin.user-notifications.index') }}">
                    <i class="bi bi-x-circle mr-1"></i> Cancel
                </a>
                <button class="btn btn-primary"
                        type="submit">
                    <i class="bi bi-save mr-1"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Auto-fill icon based on category selection
            $('#category').change(function() {
                var selectedOption = $(this).find('option:selected');
                var icon = selectedOption.data('icon');
                if (icon && !$('#icon').val()) {
                    $('#icon').val(icon);
                }
            });

            // Auto-fill icon based on level selection
            $('#level').change(function() {
                var selectedOption = $(this).find('option:selected');
                var icon = selectedOption.data('icon');
                if (icon && !$('#icon').val()) {
                    $('#icon').val(icon);
                }
            });

            // Validate JSON input for meta field
            $('#meta').blur(function() {
                var value = $(this).val().trim();
                if (value) {
                    try {
                        JSON.parse(value);
                        $(this).removeClass('is-invalid');
                    } catch (e) {
                        $(this).addClass('is-invalid');
                    }
                }
            });
        });
    </script>
@endsection
