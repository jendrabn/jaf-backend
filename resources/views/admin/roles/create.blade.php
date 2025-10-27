@extends('layouts.admin')

@section('page_title', 'Create Role')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Role' => route('admin.roles.index'),
            'Create Role' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <form action="{{ route('admin.roles.store') }}"
              method="POST">
            @csrf

            <div class="card-header border-bottom-0">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.roles.index') }}"><i class="bi bi-arrow-left mr-1"></i> Back to list</a>
                </div>
            </div>

            <div class="card-body">
                {{-- Role name --}}
                <div class="form-group">
                    <label class="required"
                           for="role-name">Role Name</label>
                    <input class="form-control @error('name') is-invalid @enderror"
                           id="role-name"
                           name="name"
                           placeholder="e.g. staff_orders"
                           required
                           type="text"
                           value="{{ old('name') }}">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">
                        Use lowercase snake_case without spaces (e.g., <code>staff_orders</code>).
                    </small>
                </div>

                {{-- Matrix: Module × Permissions --}}
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width:240px">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input"
                                               id="check-all"
                                               type="checkbox">
                                        <label class="custom-control-label"
                                               for="check-all">Module</label>
                                    </div>
                                </th>
                                <th>Permissions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $human = function (string $module, string $action) {
                                    $a = str()->headline($action);
                                    $m = str()->headline(str()->singular($module)); // blogs -> Blog
                                    return trim("$a $m"); // "Upload Ckeditor Blog"
                                };
                            @endphp

                            @foreach ($permissions as $module => $actions)
                                @php $mod = $module; @endphp
                                <tr>
                                    <td class="align-middle">
                                        <div class="custom-control custom-checkbox mb-0">
                                            {{-- Row toggle: backoffice & dashboard must checked + disabled --}}
                                            @if (in_array($module, ['backoffice', 'dashboard']))
                                                <input checked
                                                       class="custom-control-input row-toggle"
                                                       data-row="{{ $mod }}"
                                                       disabled
                                                       id="mod-{{ $mod }}"
                                                       type="checkbox">
                                            @else
                                                <input class="custom-control-input row-toggle"
                                                       data-row="{{ $mod }}"
                                                       id="mod-{{ $mod }}"
                                                       type="checkbox">
                                            @endif
                                            <label class="custom-control-label text-capitalize"
                                                   for="mod-{{ $mod }}">
                                                {{ str_replace('_', ' ', $module) }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap">
                                            @foreach ($actions as $action)
                                                @php
                                                    $perm = "$module.$action";
                                                    $id = "perm-{$mod}-{$action}";
                                                    $checked = in_array($perm, old('permissions', [])) ? 'checked' : '';
                                                @endphp

                                                {{-- Must ON: backoffice.access --}}
                                                @if ($module === 'backoffice' && $action === 'access')
                                                    <div class="custom-control custom-checkbox mr-3 mb-2">
                                                        <input checked
                                                               class="custom-control-input perm-{{ $mod }}"
                                                               disabled
                                                               id="{{ $id }}"
                                                               type="checkbox">

                                                        <input name="permissions[]"
                                                               type="hidden"
                                                               value="backoffice.access">
                                                        <label class="custom-control-label perm-chip"
                                                               for="{{ $id }}">
                                                            {{ $human($module, $action) }}
                                                        </label>
                                                    </div>

                                                    {{-- Must ON: dashboard.view --}}
                                                @elseif ($module === 'dashboard' && $action === 'view')
                                                    <div class="custom-control custom-checkbox mr-3 mb-2">
                                                        <input checked
                                                               class="custom-control-input perm-{{ $mod }}"
                                                               disabled
                                                               id="{{ $id }}"
                                                               type="checkbox">

                                                        <input name="permissions[]"
                                                               type="hidden"
                                                               value="dashboard.view">
                                                        <label class="custom-control-label perm-chip"
                                                               for="{{ $id }}">
                                                            {{ $human($module, $action) }}
                                                        </label>
                                                    </div>

                                                    {{-- Default --}}
                                                @else
                                                    <div class="custom-control custom-checkbox mr-3 mb-2">
                                                        <input {{ $checked }}
                                                               class="custom-control-input perm-{{ $mod }}"
                                                               id="{{ $id }}"
                                                               name="permissions[]"
                                                               type="checkbox"
                                                               value="{{ $perm }}">
                                                        <label class="custom-control-label perm-chip"
                                                               for="{{ $id }}">
                                                            {{ $human($module, $action) }}
                                                        </label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="card-footer border-top-0 d-flex gap-2 justify-content-end">
                <a class="btn btn-light"
                   href="{{ route('admin.roles.index') }}">
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

@section('styles')
    <style>
        .custom-control-label {
            cursor: pointer;
        }

        .perm-chip {
            font-weight: 600;
        }

        .table td {
            vertical-align: top;
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Sinkronisasi: jika semua permission modul dicentang, row-toggle ikut aktif
            function syncRow(mod) {
                const $boxes = $('input.perm-' + mod);
                const $enabled = $boxes.filter(':not(:disabled)');
                const allOn = $enabled.length ? $enabled.filter(':checked').length === $enabled.length : true;
                $('#mod-' + mod).prop('checked', allOn);
            }

            // Sinkronisasi master toggle (check-all) dengan state saat ini
            function syncAll() {
                const $allPerms = $('input[class*="perm-"]').filter(':not(:disabled)');
                const $allRows = $('.row-toggle').filter(':not(:disabled)');

                const allPermsOn = $allPerms.length ? $allPerms.filter(':checked').length === $allPerms.length :
                    true;
                const allRowsOn = $allRows.length ? $allRows.filter(':checked').length === $allRows.length : true;

                const isAllOn = allPermsOn && allRowsOn;
                const someOn = ($allPerms.filter(':checked').length > 0) || ($allRows.filter(':checked').length >
                    0);

                $('#check-all').prop('checked', isAllOn)
                    .prop('indeterminate', !isAllOn && someOn);
            }

            // Master toggle
            $('#check-all').on('change', function() {
                const on = $(this).is(':checked');

                // Toggle row checkboxes kecuali yang disabled
                $('.row-toggle').not(':disabled').prop('checked', on).trigger('change');

                // Toggle permission checkboxes kecuali yang disabled
                $('input[class*="perm-"]').not(':disabled').prop('checked', on);

                @foreach (array_keys($permissions) as $mod)
                    syncRow('{{ $mod }}');
                @endforeach
                syncAll();
            });

            // Toggle per module baris
            $('.row-toggle').on('change', function() {
                const mod = $(this).data('row');
                const on = $(this).is(':checked');

                $('input.perm-' + mod).not(':disabled').prop('checked', on);
                syncRow(mod);
                syncAll();
            });

            // Per-permission change sync
            @foreach (array_keys($permissions) as $mod)
                $('input.perm-{{ $mod }}').on('change', function() {
                    syncRow('{{ $mod }}');
                    syncAll();
                });
                // initial sync saat load (backoffice & dashboard auto-checked)
                syncRow('{{ $mod }}');
            @endforeach

            // initial master sync
            syncAll();
        });
    </script>
@endsection
