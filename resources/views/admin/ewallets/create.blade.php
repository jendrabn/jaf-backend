@extends('layouts.admin')

@section('page_title', 'Create Blog')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Blog' => route('admin.blogs.index'),
            'Create Blog' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.ewallets.store') }}"
                  enctype="multipart/form-data"
                  method="POST">
                @csrf

                <div class="card shadow-lg">
                    <div class="card-header">
                        <div class="card-tools">
                            <a class="btn btn-default"
                               href="{{ route('admin.ewallets.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                        </div>
                    </div>
                    <div class="card-body">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="required">Name</label>
                                <select autofocus
                                        class="form-control select2 {{ $errors->has('name') ? 'is-invalid' : '' }}"
                                        name="name"
                                        style="width: 100%">
                                    <option value="">---</option>
                                    @foreach (\App\Models\Ewallet::EWALLET_SELECT as $name)
                                        <option value="{{ $name }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('name'))
                                    <span class="text-danger">{{ $errors->first('name') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required">Account Name</label>
                                <input class="form-control {{ $errors->has('account_name') ? 'is-invalid' : '' }}"
                                       name="account_name"
                                       required
                                       type="text"
                                       value="{{ old('account_name', '') }}">
                                @if ($errors->has('account_name'))
                                    <span class="text-danger">{{ $errors->first('account_name') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required">Account Username</label>
                                <input class="form-control {{ $errors->has('account_username') ? 'is-invalid' : '' }}"
                                       name="account_username"
                                       required
                                       type="text"
                                       value="{{ old('account_username', '') }}">
                                @if ($errors->has('account_username'))
                                    <span class="text-danger">{{ $errors->first('account_username') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required">Phone Number</label>
                                <input class="form-control {{ $errors->has('phone') ? 'is-invalid' : '' }}"
                                       name="phone"
                                       required
                                       type="text"
                                       value="{{ old('phone', '') }}">
                                @if ($errors->has('phone'))
                                    <span class="text-danger">{{ $errors->first('phone') }}</span>
                                @endif
                            </div>

                            <div class="form-group col-md-6">
                                <label class="required">Logo</label>
                                <div class="needsclick dropzone {{ $errors->has('logo') ? 'is-invalid' : '' }}"
                                     id="logo-dropzone">
                                </div>
                                @if ($errors->has('logo'))
                                    <span class="text-danger">{{ $errors->first('logo') }}</span>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light mr-2"
                           href="{{ route('admin.ewallets.index') }}"><i class="bi bi-x-circle mr-1"></i>Cancel</a>
                        <button class="btn btn-primary"
                                type="submit"><i class="bi bi-check2-circle mr-1"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        Dropzone.options.logoDropzone = {
            url: '{{ route('admin.ewallets.storeMedia') }}',
            maxFilesize: 5, // MB
            acceptedFiles: '.jpeg,.jpg,.png',
            maxFiles: 1,
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            params: {
                size: 5,
                width: 5000,
                height: 5000
            },
            success: function(file, response) {
                $('form').find('input[name="logo"]').remove()
                $('form').append('<input type="hidden" name="logo" value="' + response.name + '">')
            },
            removedfile: function(file) {
                file.previewElement.remove()
                if (file.status !== 'error') {
                    $('form').find('input[name="logo"]').remove()
                    this.options.maxFiles = this.options.maxFiles + 1
                }
            },
            init: function() {
                @if (isset($bank) && $bank->logo)
                    let file = {!! json_encode($bank->logo) !!}
                    this.options.addedfile.call(this, file)
                    this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
                    file.previewElement.classList.add('dz-complete')
                    $('form').append('<input type="hidden" name="logo" value="' + file.file_name + '">')
                    this.options.maxFiles = this.options.maxFiles - 1
                @endif
            },
            error: function(file, response) {
                let message = '';

                if ($.type(response) === 'string') {
                    message = response //dropzone sends it's own error messages in string
                } else {
                    message = response.errors.file
                }
                file.previewElement.classList.add('dz-error')
                _ref = file.previewElement.querySelectorAll('[data-dz-errormessage]')
                _results = []
                for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                    node = _ref[_i]
                    _results.push(node.textContent = message)
                }

                toastr.error(message, 'Error');

                return _results
            }
        }
    </script>
@endsection
