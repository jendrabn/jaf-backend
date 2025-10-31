@extends('layouts.admin')

@section('page_title', 'Create User Notification')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'User Notifications' => route('admin.user-notifications.index'),
            'Create User Notification' => null,
        ],
    ])
@endsection

@section('content')

    <div class="card shadow-lg">
        <form action="{{ route('admin.user-notifications.store') }}"
              enctype="multipart/form-data"
              method="POST">
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
                    <div class="form-group col-md-12">
                        <div class="custom-control custom-checkbox">
                            <input @if (old('send_to_all')) checked @endif
                                   class="custom-control-input"
                                   id="send_to_all"
                                   name="send_to_all"
                                   type="checkbox"
                                   value="1">
                            <label class="custom-control-label"
                                   for="send_to_all">
                                Send to all users
                            </label>
                        </div>
                    </div>

                    <div class="form-group col-md-6"
                         id="user_selection">
                        <label for="user_id">User</label>
                        <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                                data-placeholder="Select User (leave empty for all users)"
                                id="user_id"
                                name="user_id"
                                style="width: 100%">
                            <option value="">All Users</option>
                            @foreach ($users as $id => $name)
                                <option @selected(old('user_id') == $id)
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
                               value="{{ old('title', '') }}">
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
                                  rows="4">{{ old('body', '') }}</textarea>
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
                                <option @selected(old('category') == $category['value'])
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
                                <option @selected(old('level') == $level['value'])
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
                               value="{{ old('url', '') }}">
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
                               value="{{ old('icon', '') }}">
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
                                  rows="3">{{ old('meta', '') }}</textarea>
                        <small class="form-text text-muted">Enter valid JSON format for additional data</small>
                        @if ($errors->has('meta'))
                            <span class="invalid-feedback">{{ $errors->first('meta') }}</span>
                        @endif
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
                    <i class="bi bi-save mr-1"></i> Save
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Handle send to all checkbox
            $('#send_to_all').change(function() {
                if ($(this).is(':checked')) {
                    $('#user_selection').hide();
                    $('#user_id').prop('disabled', true);
                } else {
                    $('#user_selection').show();
                    $('#user_id').prop('disabled', false);
                }
            });

            // Initialize state
            if ($('#send_to_all').is(':checked')) {
                $('#user_selection').hide();
                $('#user_id').prop('disabled', true);
            }

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
