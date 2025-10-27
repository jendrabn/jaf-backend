@extends('layouts.admin')

@section('page_title', 'Edit User')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'User' => route('admin.users.index'),
            'Edit User' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <form action="{{ route('admin.users.update', [$user->id]) }}"
              enctype="multipart/form-data"
              method="POST">
            @method('PUT')
            @csrf
            <div class="card-header border-bottom-0">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.users.index') }}">
                        <i class="bi bi-arrow-left mr-1"></i> Back to list
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required"
                               for="_name">Name</label>
                        <input autocomplete="name"
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               id="_name"
                               name="name"
                               placeholder="e.g. John Doe"
                               required
                               type="text"
                               value="{{ old('name', $user->name) }}">
                        @if ($errors->has('name'))
                            <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required"
                               for="_email">Email</label>
                        <input autocomplete="email"
                               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               id="_email"
                               name="email"
                               placeholder="e.g. john@example.com"
                               required
                               type="email"
                               value="{{ old('email', $user->email) }}">
                        @if ($errors->has('email'))
                            <span class="invalid-feedback">{{ $errors->first('email') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label for="_password">Password</label>
                        <input autocomplete="new-password"
                               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               id="_password"
                               name="password"
                               placeholder="Leave blank to keep current"
                               type="password">
                        @if ($errors->has('password'))
                            <span class="invalid-feedback">{{ $errors->first('password') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required"
                               for="_roles">Roles</label>
                        <select class="form-control select2 {{ $errors->has('roles') ? 'is-invalid' : '' }}"
                                data-placeholder="Select roles..."
                                id="_roles"
                                multiple
                                name="roles[]"
                                required
                                style="width: 100%">
                            <option value=""></option>
                            @foreach ($roles as $id => $role)
                                <option @selected(in_array($id, old('roles', [])) || $user->roles->contains($id))
                                        value="{{ $role }}">
                                    {{ $role }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('roles'))
                            <span class="invalid-feedback">{{ $errors->first('roles') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label for="_phone">Phone Number</label>
                        <input autocomplete="tel"
                               class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                               id="_phone"
                               inputmode="tel"
                               name="phone"
                               placeholder="e.g. 0812-3456-7890"
                               type="tel"
                               value="{{ old('phone', $user->phone) }}">
                        @if ($errors->has('phone'))
                            <span class="invalid-feedback">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label for="_sex">Sex</label>
                        <select class="form-control select2 {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                                data-placeholder="Select sex..."
                                id="_sex"
                                name="sex"
                                style="width: 100%;">
                            <option disabled
                                    value>
                                ---
                            </option>
                            @foreach (App\Models\User::SEX_SELECT as $key => $label)
                                <option @selected(old('sex', $user->sex) === $key)
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
                               autocomplete="bday"
                               class="form-control  {{ $errors->has('birth_date') ? 'is-invalid' : '' }}"
                               id="_birth_date"
                               name="birth_date"
                               placeholder="YYYY-MM-DD"
                               type="date"
                               value="{{ old('birth_date', $user->birth_date) }}">
                        @if ($errors->has('birth_date'))
                            <span class="invalid-feedback">{{ $errors->first('birth_date') }}</span>
                        @endif
                    </div>
                </div>

            </div>
            <div class="card-footer border-top-0 d-flex gap-2 justify-content-end">
                <a class="btn btn-light"
                   href="{{ route('admin.users.index') }}">
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
