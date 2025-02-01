@extends('layouts.admin', ['title' => 'Edit Banner'])

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Banner</h3>
        </div>

        <div class="card-body">
            <a class="btn btn-default mb-3"
               href="{{ route('admin.banners.index') }}">Back to list</a>

            <form action="{{ route('admin.banners.update', [$banner->id]) }}"
                  enctype="multipart/form-data"
                  method="POST">
                @method('PUT')
                @csrf

                <div class="form-group">
                    <label class="required"
                           for="_image">Image</label>
                    <div class="needsclick dropzone {{ $errors->has('image') ? 'is-invalid' : '' }}"
                         id="image-dropzone">
                    </div>
                    @if ($errors->has('image'))
                        <span class="invalid-feedback">{{ $errors->first('image') }}</span>
                    @endif
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required">Image Description</label>
                        <input class="form-control {{ $errors->has('image_description') ? 'is-invalid' : '' }}"
                               name="image_description"
                               required
                               type="text"
                               value="{{ old('image_description', $banner->image_description) }}">
                        @if ($errors->has('image_description'))
                            <span class="invalid-feedback">{{ $errors->first('image_description') }}</span>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>Url</label>
                        <input class="form-control {{ $errors->has('url') ? 'is-invalid' : '' }}"
                               name="url"
                               type="text"
                               value="{{ old('url', $banner->url) }}">
                        @if ($errors->has('url'))
                            <span class="invalid-feedback">{{ $errors->first('url') }}</span>
                        @endif
                    </div>
                </div>

                <button class="btn btn-primary"
                        type="submit">
                    <i class="fa-solid fa-floppy-disk"></i> Update
                </button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        Dropzone.options.imageDropzone = {
            url: '{{ route('admin.banners.storeMedia') }}',
            maxFilesize: 10, // MB
            acceptedFiles: '.jpeg,.jpg,.png',
            maxFiles: 1,
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            params: {
                size: 10,
                width: 15000,
                height: 10000
            },
            success: function(file, response) {
                $('form').find('input[name="image"]').remove()
                $('form').append('<input type="hidden" name="image" value="' + response.name + '">')
            },
            removedfile: function(file) {
                file.previewElement.remove()
                if (file.status !== 'error') {
                    $('form').find('input[name="image"]').remove()
                    this.options.maxFiles = this.options.maxFiles + 1
                }
            },
            init: function() {
                @if (isset($banner) && $banner->image)
                    let file = {!! json_encode($banner->image) !!}
                    this.options.addedfile.call(this, file)
                    this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
                    file.previewElement.classList.add('dz-complete')
                    $('form').append('<input type="hidden" name="image" value="' + file.file_name + '">')
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
