@extends('layouts.admin')

@section('page_title', 'Audit Log Detail')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Audit Logs' => route('admin.audit-logs.index'),
            'Audit Log Detail' => null,
        ],
    ])
@endsection

@php
    use Illuminate\Support\Str;
    use Illuminate\Support\Arr;

    // Event: pakai kolom event, fallback dari description "audit:*"
    $event =
        $auditLog->event ??
        Str::of($auditLog->description ?? '')
            ->after('audit:')
            ->toString();

    // pretty printer: aman untuk array/object/string JSON
    $pretty = function ($v) {
        if (is_string($v)) {
            $d = json_decode($v, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $v = $d;
            }
        }
        return json_encode($v, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    };

    $subjectType = $auditLog->subject_type ? class_basename($auditLog->subject_type) : null;

    // --- Normalisasi "changed" supaya tidak Array to string conversion ---
    $changedVal = $auditLog->changed;

    // Jika string JSON → decode
    if (is_string($changedVal)) {
        $decoded = json_decode($changedVal, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $changedVal = $decoded;
        } else {
            // bukan JSON valid → jadikan array berisi 1 string
            $changedVal = [$changedVal];
        }
    }

    // Jika object stdClass → cast ke array
    if (is_object($changedVal)) {
        $changedVal = (array) $changedVal;
    }

    // Fallback jika null/tipe lain
    if (!is_array($changedVal)) {
        $changedVal = [];
    }

    // Helper render item: scalar->string, nested->json
    $render = function ($v) {
        if (is_scalar($v) || $v === null) {
            return (string) $v;
        }
        return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    };

    // Bentuk teks final untuk "Changed Fields"
    $changedItems = [];
    if (Arr::isAssoc($changedVal)) {
        // contoh: ['ids'=>[...], 'count'=>3] → "ids:[...], count:3"
        foreach ($changedVal as $k => $v) {
            $changedItems[] = $k . ':' . $render($v);
        }
    } else {
        // contoh: ['title','slug','published_at']
        foreach ($changedVal as $v) {
            $changedItems[] = $render($v);
        }
    }
    $changedText = implode(', ', $changedItems);
    $changedCount = count($changedVal);
@endphp

@section('content')
    <div class="card shadow-lg">
        <div class="card-header border-bottom-0">
            <div class="card-tools">
                <a class="btn btn-default"
                   href="{{ route('admin.audit-logs.index') }}">
                    <i class="bi bi-arrow-left mr-1"></i> Back to list
                </a>
            </div>
        </div>
        <div class="card-body">
            <table class="table">
                <tbody>
                    <tr>
                        <th>ID</th>
                        <td>{{ $auditLog->id }}</td>
                    </tr>
                    <tr>
                        <th>Event</th>
                        <td>{{ strtoupper($event ?? 'UNKNOWN') }}</td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td>{{ $auditLog->description ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Subject</th>
                        <td><code>{{ $subjectType ?? '—' }}#{{ $auditLog->subject_id ?? '—' }}</code></td>
                    </tr>
                    <tr>
                        <th>Subject Type</th>
                        <td>{{ $auditLog->subject_type ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>User</th>
                        <td>
                            @if ($auditLog->user)
                                {{ $auditLog->user->name ?? 'User' }}
                                (ID: {{ $auditLog->user_id }}, {{ $auditLog->user->email ?? '—' }})
                            @else
                                System
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Request ID</th>
                        <td>{{ $auditLog->request_id ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Method · IP/Host</th>
                        <td>{{ strtoupper($auditLog->method ?? '—') }} ·
                            {{ $auditLog->ip ?? ($auditLog->host ?? '—') }}</td>
                    </tr>
                    <tr>
                        <th>URL</th>
                        <td class="text-break">{{ $auditLog->url ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td class="text-break">{{ $auditLog->user_agent ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Route</th>
                        <td>{{ data_get($auditLog->meta, 'route', '—') }}</td>
                    </tr>
                    <tr>
                        <th>Action</th>
                        <td>{{ data_get($auditLog->meta, 'action', '—') }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ optional($auditLog->created_at)->format('d-m-Y H:i:s') }}</td>
                    </tr>
                    <tr>
                        <th>Changed Fields</th>
                        <td>
                            @if ($changedCount > 0)
                                {{ $changedText }} ({{ $changedCount }})
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Before</th>
                        <td>
                            <pre class="mb-0 small">{{ $pretty($auditLog->before) }}</pre>
                        </td>
                    </tr>
                    <tr>
                        <th>After</th>
                        <td>
                            <pre class="mb-0 small">{{ $pretty($auditLog->after) }}</pre>
                        </td>
                    </tr>
                    <tr>
                        <th>Meta</th>
                        <td>
                            <pre class="mb-0 small">{{ $pretty($auditLog->meta) }}</pre>
                        </td>
                    </tr>
                    <tr>
                        <th>Properties (Legacy)</th>
                        <td>
                            <pre class="mb-0 small">{{ $pretty($auditLog->properties) }}</pre>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
