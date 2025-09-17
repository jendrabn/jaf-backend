@extends('layouts.admin')

@section('page_title', 'Create User')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'User' => route('admin.users.index'),
            'Create User' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-lg">
                <div class="card-header">
                    <div class="card-tools">
                        <a class="btn btn-default"
                           href="{{ route('admin.users.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.users.store') }}"
                          enctype="multipart/form-data"
                          method="POST">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="required">Name</label>
                                <input autofocus
                                       class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                       name="name"
                                       required
                                       type="text"
                                       value="{{ old('name', '') }}">
                                @if ($errors->has('name'))
                                    <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required">Email</label>
                                <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                       id="_email"
                                       name="email"
                                       required
                                       type="email"
                                       value="{{ old('email') }}">
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required"
                                       for="_password">Password</label>
                                <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                       id="_password"
                                       name="password"
                                       required
                                       type="password">
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required"
                                       for="_roles">Roles</label>
                                <div style="padding-bottom: 4px">
                                    <span class="btn btn-info btn-xs select-all"
                                          style="border-radius: 0">Select all</span>
                                    <span class="btn btn-info btn-xs deselect-all"
                                          style="border-radius: 0">Deselect all</span>
                                </div>
                                <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}"
                                        id="_roles"
                                        multiple
                                        name="roles[]"
                                        required>
                                    @foreach ($roles as $id => $role)
                                        <option @selected(in_array($role, old('roles', [])))
                                                value="{{ $role }}">{{ $role }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('roles'))
                                    <span class="invalid-feedback">{{ $errors->first('roles') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="_phone">Phone Number</label>
                                <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                       id="_phone"
                                       name="phone"
                                       type="text"
                                       value="{{ old('phone', '') }}">
                                @if ($errors->has('phone'))
                                    <span class="invalid-feedback">{{ $errors->first('phone') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="_sex">Sex</label>
                                <select class="form-control select2 {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                                        id="_sex"
                                        name="sex"
                                        style="width: 100%;">
                                    <option @selected(old('sex', null) === null)
                                            disabled
                                            value>---</option>
                                    @foreach (App\Models\User::SEX_SELECT as $key => $label)
                                        <option @selected(old('sex', null) === $key)
                                                value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('sex'))
                                    <span class="invalid-feedback">{{ $errors->first('sex') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label for="_birth_date">Birth Date</label>
                                <input autocomplete="off"
                                       class="form-control date datetimepicker-input {{ $errors->has('birth_date') ? 'is-invalid' : '' }}"
                                       data-toggle="datetimepicker"
                                       id="_birth_date"
                                       name="birth_date"
                                       placeholder="DD-MM-YYYY"
                                       type="text"
                                       value="{{ old('birth_date') }}">
                                @if ($errors->has('birth_date'))
                                    <span class="invalid-feedback">{{ $errors->first('birth_date') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <a class="btn btn-default mr-1"
                               href="{{ route('admin.users.index') }}"><i class="bi bi-x-circle mr-1"></i>Cancel</a>
                            <button class="btn btn-primary"
                                    type="submit"><i class="bi bi-check2-circle mr-1"></i>Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
