@extends('layouts.admin')

@section('page_title', 'Edit Product Brand')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Product Brand' => route('admin.product-brands.index'),
            'Edit Product Brand' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <form action="{{ route('admin.product-brands.update', [$productBrand->id]) }}"
              enctype="multipart/form-data"
              method="POST">
            @method('PUT')
            @csrf

            <div class="card-header border-bottom-0">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.product-brands.index') }}">
                        <i class="bi bi-arrow-left mr-1"></i> Back to list
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="form-group">
                    <label class="required">Brand Name</label>
                    <input autofocus
                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                           name="name"
                           required
                           type="text"
                           value="{{ old('name', $productBrand->name) }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                    @endif
                </div>

                <div class="form-group">
                    <label>Logo</label>
                    <div class="needsclick dropzone {{ $errors->has('logo') ? 'is-invalid' : '' }}"
                         id="logo-dropzone">
                    </div>
                    @if ($errors->has('logo'))
                        <span class="invalid-feedback">{{ $errors->first('logo') }}</span>
                    @endif
                </div>
            </div>

            <div class="card-footer border-top-0 d-flex gap-2 justify-content-end">
                <a class="btn btn-light"
                   href="{{ route('admin.product-brands.index') }}">
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

@section('scripts')
    <script>
        Dropzone.options.logoDropzone = {
            url: '{{ route('admin.product-brands.storeMedia') }}',
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
                @if (isset($productBrand) && $productBrand->logo)
                    let file = {!! json_encode($productBrand->logo) !!}
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
