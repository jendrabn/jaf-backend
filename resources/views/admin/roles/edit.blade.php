{{-- resources/views/admin/roles/edit.blade.php --}}
@extends('layouts.admin')

@section('page_title', 'Edit Role')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Role' => route('admin.roles.index'),
            'Edit Role' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">

            <form action="{{ route('admin.roles.update', $role) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-lg">
                    <div class="card-body">
                        {{-- Role name --}}
                        <div class="form-group">
                            <label for="role-name">Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('name') is-invalid @enderror"
                                   id="role-name"
                                   name="name"
                                   placeholder="e.g. staff_orders"
                                   required
                                   type="text"
                                   value="{{ old('name', $role->name) }}">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">
                                Gunakan <b>snake_case</b> tanpa spasi (contoh: <code>staff_orders</code>).
                            </small>
                        </div>

                        {{-- Matrix: Module × Permissions --}}
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="thead-light">
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
                                        use Illuminate\Support\Str;
                                        $owned = $role->permissions->pluck('name')->toArray();
                                        $human = function (string $module, string $action) {
                                            $a = Str::headline($action);
                                            $m = Str::headline(Str::singular($module)); // blogs -> Blog
                                            return trim("$a $m"); // "Upload Ckeditor Blog"
                                        };
                                    @endphp

                                    @foreach ($permissions as $module => $actions)
                                        @php $mod = $module; @endphp
                                        <tr>
                                            <td class="align-middle">
                                                <div class="custom-control custom-checkbox mb-0">
                                                    {{-- Row toggle: backoffice & dashboard wajib checked + disabled --}}
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
                                                            $checked = in_array($perm, old('permissions', $owned))
                                                                ? 'checked'
                                                                : '';
                                                        @endphp

                                                        {{-- Wajib ON: backoffice.access (selalu tercentang + disabled + hidden input) --}}
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

                                                            {{-- Wajib ON: dashboard.view (selalu tercentang + disabled + hidden input) --}}
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

                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light mr-2"
                           href="{{ route('admin.roles.index') }}">
                            <i class="bi bi-x-circle mr-1"></i>Cancel
                        </a>
                        <button class="btn btn-primary">
                            <i class="bi bi-check2-circle mr-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </form>

        </div>
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
        (function() {
            // Select All
            const checkAll = document.getElementById('check-all');
            if (checkAll) {
                checkAll.addEventListener('change', function(e) {
                    // Toggle row checkboxes kecuali yang disabled
                    document.querySelectorAll('.row-toggle').forEach(cb => {
                        if (!cb.disabled) cb.checked = e.target.checked;
                    });
                    // Toggle permission checkboxes kecuali yang disabled
                    document.querySelectorAll('[class^="perm-"]').forEach(cb => {
                        if (!cb.disabled) cb.checked = e.target.checked;
                    });
                });
            }

            // Toggle per module baris
            document.querySelectorAll('.row-toggle').forEach(t => {
                t.addEventListener('change', function(e) {
                    const mod = e.target.dataset.row;
                    const on = e.target.checked;
                    document.querySelectorAll('.perm-' + mod).forEach(cb => {
                        if (!cb.disabled) cb.checked = on; // jangan ubah yang disabled
                    });
                });
            });

            // Sinkronisasi: jika semua permission modul dicentang, row-toggle ikut aktif
            function syncRow(mod) {
                const boxes = Array.from(document.querySelectorAll('.perm-' + mod));
                const enabled = boxes.filter(cb => !cb.disabled);

                // Jika tidak ada yang enabled (semua disabled & checked), anggap sudah all-on
                const allOn = enabled.length ? enabled.every(cb => cb.checked) : true;

                const row = document.getElementById('mod-' + mod);
                if (row) row.checked = allOn;
            }

            @foreach (array_keys($permissions) as $mod)
                document.querySelectorAll('.perm-{{ $mod }}').forEach(cb => {
                    cb.addEventListener('change', () => syncRow('{{ $mod }}'));
                });
                // initial sync saat load (backoffice & dashboard auto-checked)
                syncRow('{{ $mod }}');
            @endforeach
        })();
    </script>
@endsection
