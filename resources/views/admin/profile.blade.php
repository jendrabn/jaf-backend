@extends('layouts.admin')

@section('page_title', 'Profile')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Profile' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.profile.update') }}"
                          enctype="multipart/form-data"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>ID</label>
                            <input class="form-control"
                                   readonly
                                   type="text"
                                   value="{{ $user->id }}">
                        </div>

                        <div class="form-group">
                            <label class="required">Name</label>
                            <input class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                   name="name"
                                   required
                                   type="text"
                                   value="{{ old('name', $user->name) }}">
                            @if ($errors->has('name'))
                                <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="required">Email</label>
                            <input class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                   name="email"
                                   required
                                   type="email"
                                   value="{{ old('email', $user->email) }}">
                            @if ($errors->has('email'))
                                <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Date & Time Verified</label>
                            <input class="form-control"
                                   readonly
                                   type="text"
                                   value="{{ $user->email_verified_at }}">
                        </div>

                        <div class="form-group">
                            <label>Roles</label>
                            <select class="form-control select2"
                                    disabled
                                    multiple
                                    style="width: 100%;">
                                @foreach ($roles as $id => $role)
                                    <option @selected(in_array($id, old('roles', [])) || $user->roles->contains($id))
                                            value="{{ $role }}">
                                        {{ $role }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Phone Number</label>
                            <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                   name="phone"
                                   type="tel"
                                   value="{{ old('phone', $user->phone) }}">
                            @if ($errors->has('phone'))
                                <span class="invalid-feedback">{{ $errors->first('phone') }}</span>
                            @endif

                        </div>
                        <div class="form-group">
                            <label>Gender</label>
                            <select class="form-control select2 {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                                    name="sex"
                                    style="width: 100%;">
                                <option disabled
                                        value>---</option>
                                @foreach (App\Models\User::SEX_SELECT as $key => $label)
                                    <option @selected(old('sex', $user->sex) === $key)
                                            value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('sex'))
                                <span class="invalid-feedback">{{ $errors->first('sex') }}</span>
                            @endif

                        </div>
                        <div class="form-group">
                            <label>Birth Date</label>
                            <input class="form-control date datetimepicker-input {{ $errors->has('birth_date') ? 'is-invalid' : '' }}"
                                   data-toggle="datetimepicker"
                                   name="birth_date"
                                   placeholder="YYYY-MM-DD"
                                   type="text"
                                   value="{{ old('birth_date', $user->birth_date) }}">
                            @if ($errors->has('birth_date'))
                                <span class="invalid-feedback">{{ $errors->first('birth_date') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label>Avatar</label>
                            <input class="custom-file {{ $errors->has('avatar') ? 'is-invalid' : '' }}"
                                   name="avatar"
                                   type="file" />
                        </div>

                        <div class="form-group">
                            <label>Date & Time Created</label>
                            <input class="form-control"
                                   readonly
                                   type="text"
                                   value="{{ $user->created_at }}" />
                        </div>

                        <div class="form-group">
                            <label>Date & Time Updated</label>
                            <input class="form-control"
                                   readonly
                                   type="text"
                                   value="{{ $user->updated_at }}" />
                        </div>

                        <button class="btn btn-primary"
                                type="submit">
                            <i class="bi bi-check2-circle mr-1"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Change Password</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.profile.update-password') }}"
                          method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="required">Current Password</label>
                            <input class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                                   name="current_password"
                                   required
                                   type="password" />
                            @if ($errors->has('current_password'))
                                <span class="invalid-feedback">{{ $errors->first('current_password') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="required">New Password</label>
                            <input class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                   name="password"
                                   required
                                   type="password" />
                            @if ($errors->has('password'))
                                <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                            @endif
                        </div>

                        <div class="form-group">
                            <label class="required">Confirm New Password</label>
                            <input class="form-control"
                                   name="password_confirmation"
                                   required
                                   type="password" />
                        </div>

                        <button class="btn btn-primary"
                                type="submit">
                            <i class="bi bi-check2-circle mr-1"></i>Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
