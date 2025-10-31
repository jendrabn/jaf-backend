@extends('layouts.admin')

@section('page_title', 'View User Notification')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'User Notifications' => route('admin.user-notifications.index'),
            'View User Notification' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-header border-bottom-0">
            <div class="card-tools">
                <a class="btn btn-default"
                   href="{{ route('admin.user-notifications.index') }}">
                    <i class="bi bi-arrow-left mr-1"></i> Back to list
                </a>
                <a class="btn btn-info"
                   href="{{ route('admin.user-notifications.edit', $userNotification->id) }}">
                    <i class="bi bi-pencil mr-1"></i> Edit
                </a>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th width="150">ID</th>
                            <td>{{ $userNotification->id }}</td>
                        </tr>
                        <tr>
                            <th>User</th>
                            <td>
                                @if ($userNotification->user)
                                    <a href="{{ route('admin.users.show', $userNotification->user->id) }}">
                                        {{ $userNotification->user->name }}
                                    </a>
                                @else
                                    All Users
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Title</th>
                            <td>{{ $userNotification->title }}</td>
                        </tr>
                        <tr>
                            <th>Category</th>
                            <td>
                                @php
                                    $category = \App\Enums\NotificationCategory::tryFrom($userNotification->category);
                                @endphp
                                @if ($category)
                                    {!! badgeLabel($category->label(), $category->color()) !!}
                                @else
                                    {{ $userNotification->category }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Level</th>
                            <td>
                                @php
                                    $level = \App\Enums\NotificationLevel::tryFrom($userNotification->level);
                                @endphp
                                @if ($level)
                                    {!! badgeLabel($level->label(), $level->color()) !!}
                                @else
                                    {{ $userNotification->level }}
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <table class="table table-bordered table-striped">
                        <tr>
                            <th width="150">URL</th>
                            <td>
                                @if ($userNotification->url)
                                    <a href="{{ $userNotification->url }}"
                                       target="_blank">
                                        {{ $userNotification->url }}
                                        <i class="fas fa-external-link-alt ml-1"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Icon</th>
                            <td>
                                @if ($userNotification->icon)
                                    <i class="{{ $userNotification->icon }}"></i> {{ $userNotification->icon }}
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Read Status</th>
                            <td>
                                @if ($userNotification->read_at)
                                    {!! badgeLabel('Read', 'success') !!}
                                    <br><small>{{ $userNotification->read_at->format('d M Y H:i') }}</small>
                                @else
                                    {!! badgeLabel('Unread', 'warning') !!}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created At</th>
                            <td>{{ $userNotification->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Updated At</th>
                            <td>{{ $userNotification->updated_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-12">
                    <h5>Message</h5>
                    <div class="card">
                        <div class="card-body">
                            <p>{{ $userNotification->body }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if ($userNotification->meta)
                <div class="row mt-4">
                    <div class="col-12">
                        <h5>Meta Data</h5>
                        <div class="card">
                            <div class="card-body">
                                <pre>{{ json_encode($userNotification->meta, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="card-footer border-top-0 d-flex gap-2 justify-content-end">
            @if ($userNotification->read_at)
                <a class="btn btn-warning"
                   data-method="PUT"
                   href="{{ route('admin.user-notifications.markAsUnread', $userNotification->id) }}">
                    <i class="bi bi-envelope mr-1"></i> Mark as Unread
                </a>
            @else
                <a class="btn btn-success"
                   data-method="PUT"
                   href="{{ route('admin.user-notifications.markAsRead', $userNotification->id) }}">
                    <i class="bi bi-envelope-open mr-1"></i> Mark as Read
                </a>
            @endif

            <a class="btn btn-info"
               href="{{ route('admin.user-notifications.edit', $userNotification->id) }}">
                <i class="bi bi-pencil mr-1"></i> Edit
            </a>

            <form action="{{ route('admin.user-notifications.destroy', $userNotification->id) }}"
                  method="POST"
                  style="display: inline;">
                @method('DELETE')
                @csrf
                <button class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete this notification?')"
                        type="submit">
                    <i class="bi bi-trash3 mr-1"></i> Delete
                </button>
            </form>
        </div>
    </div>
@endsection
