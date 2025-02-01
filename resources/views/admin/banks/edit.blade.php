@extends('layouts.admin', ['title' => 'Edit Bank'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Bank</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.banks.index') }}">Back to list</a>

            <form action="{{ route('admin.banks.update', [$bank->id]) }}"
                  enctype="multipart/form-data"
                  method="POST">
                @method('PUT')
                @csrf

                <div class="form-group">
                    <label>Logo</label>
                    <div class="needsclick dropzone {{ $errors->has('logo') ? 'is-invalid' : '' }}"
                         id="logo-dropzone">
                    </div>
                    @if ($errors->has('logo'))
                        <span class="invalid-feedback">{{ $errors->first('logo') }}</span>
                    @endif
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required">Bank Name</label>
                        <input autofocus
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               name="name"
                               required
                               type="text"
                               value="{{ old('name', $bank->name) }}">
                        @if ($errors->has('name'))
                            <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Bank Code</label>
                        <input class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}"
                               name="code"
                               required
                               type="text"
                               value="{{ old('code', $bank->code) }}">
                        @if ($errors->has('code'))
                            <span class="invalid-feedback">{{ $errors->first('code') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Account Name</label>
                        <input class="form-control {{ $errors->has('account_name') ? 'is-invalid' : '' }}"
                               name="account_name"
                               required
                               type="text"
                               value="{{ old('account_name', $bank->account_name) }}">
                        @if ($errors->has('account_name'))
                            <span class="invalid-feedback">{{ $errors->first('account_name') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Account Number</label>
                        <input class="form-control {{ $errors->has('account_number') ? 'is-invalid' : '' }}"
                               name="account_number"
                               required
                               type="text"
                               value="{{ old('account_number', $bank->account_number) }}">
                        @if ($errors->has('account_number'))
                            <span class="invalid-feedback">{{ $errors->first('account_number') }}</span>
                        @endif
                    </div>
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        Dropzone.options.logoDropzone = {
            url: '{{ route('admin.banks.storeMedia') }}',
            maxFilesize: 1, // MB
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
