@extends('layouts.admin')

@section('page_title', 'Edit Product')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Product' => route('admin.products.index'),
            'Edit Product' => null,
        ],
    ])
@endsection

@section('content')
    <form action="{{ route('admin.products.update', [$product->id]) }}"
          enctype="multipart/form-data"
          method="POST">
        @method('PUT')
        @csrf

        <div class="card shadow-lg">
            <div class="card-header">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.products.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                </div>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label class="required">Product Images</label>
                    <div class="needsclick dropzone {{ $errors->has('images') ? 'is-invalid' : '' }}"
                         id="images-dropzone">
                    </div>
                    @if ($errors->has('images'))
                        <span class="invalid-feedback">{{ $errors->first('images') }}</span>
                    @endif
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required">Product Name</label>
                        <input autofocus
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               name="name"
                               required
                               type="text"
                               value="{{ old('name', $product->name) }}">
                        @if ($errors->has('name'))
                            <span class="invalid-feedback">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Category</label>
                        <select class="form-control select2 {{ $errors->has('product_category') ? 'is-invalid' : '' }}"
                                name="product_category_id"
                                required
                                style="width: 100%">
                            @foreach ($product_categories as $id => $entry)
                                <option @selected((old('product_category_id') ? old('product_category_id') : $product->category->id ?? '') == $id)
                                        value="{{ $id }}">
                                    {{ $entry }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('product_category'))
                            <span class="invalid-feedback">{{ $errors->first('product_category') }}</span>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Product Description</label>
                    <textarea class="form-control ckeditor {{ $errors->has('description') ? 'is-invalid' : '' }}"
                              name="description">{!! old('description', $product->description) !!}</textarea>
                    @if ($errors->has('description'))
                        <span class="invalid-feedback">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Brand</label>
                        <select class="form-control select2 {{ $errors->has('product_brand') ? 'is-invalid' : '' }}"
                                name="product_brand_id"
                                style="width: 100%">
                            @foreach ($product_brands as $id => $entry)
                                <option @selected((old('product_brand_id') ? old('product_brand_id') : $product->brand->id ?? '') == $id)
                                        value="{{ $id }}">
                                    {{ $entry }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('product_brand'))
                            <span class="invalid-feedback">{{ $errors->first('product_brand') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label>Sex</label>
                        <select class="form-control select2 {{ $errors->has('sex') ? 'is-invalid' : '' }}"
                                name="sex"
                                style="width: 100%">
                            <option @selected(old('sex', null) === null)
                                    value>---</option>
                            @foreach (App\Models\Product::SEX_SELECT as $key => $label)
                                <option @selected(old('sex', $product->sex) === $key)
                                        value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('sex'))
                            <span class="invalid-feedback">{{ $errors->first('sex') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Price</label>
                        <div class="input-group has-validation">
                            <div class="input-group-prepend">
                                <span class="input-group-text">Rp</span>
                            </div>
                            <input class="form-control {{ $errors->has('price') ? 'is-invalid' : '' }}"
                                   name="price"
                                   required
                                   type="number"
                                   value="{{ old('price', $product->price) }}">
                            @if ($errors->has('price'))
                                <span class="invalid-feedback">{{ $errors->first('price') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Stock</label>
                        <input class="form-control {{ $errors->has('stock') ? 'is-invalid' : '' }}"
                               name="stock"
                               required
                               step="1"
                               type="number"
                               value="{{ old('stock', $product->stock) }}" />
                        @if ($errors->has('stock'))
                            <span class="invalid-feedback">{{ $errors->first('stock') }}</span>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Weight</label>
                        <div class="input-group">
                            <input class="form-control {{ $errors->has('weight') ? 'is-invalid' : '' }}"
                                   name="weight"
                                   required
                                   step="1"
                                   type="number"
                                   value="{{ old('weight', $product->weight) }}">
                            <div class="input-group-append">
                                <span class="input-group-text">Gram</span>
                            </div>
                            @if ($errors->has('weight'))
                                <span class="invalid-feedback">{{ $errors->first('weight') }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox {{ $errors->has('is_publish') ? 'is-invalid' : '' }}">
                        <input @checked($product->is_publish || old('is_publish', 0) === 1)
                               class="custom-control-input"
                               name="is_publish"
                               type="checkbox" />
                        <label class="custom-control-label">Publish Status</label>
                    </div>
                    @if ($errors->has('is_publish'))
                        <span class="invalid-feedback">{{ $errors->first('is_publish') }}</span>
                    @endif
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <a class="btn btn-light mr-2"
                   href="{{ route('admin.products.index') }}"><i class="bi bi-x-circle mr-1"></i>Cancel</a>
                <button class="btn btn-primary"
                        type="submit">
                    <i class="bi bi-check2-circle mr-1"></i>Save Changes
                </button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/ckeditor5/43.0.0/ckeditor.min.js"></script>

    <script>
        let uploadedImagesMap = {}

        Dropzone.options.imagesDropzone = {
            url: '{{ route('admin.products.storeMedia') }}',
            maxFilesize: 5, // MB
            acceptedFiles: '.jpeg,.jpg,.png',
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            params: {
                size: 5,
                width: 10000,
                height: 10000
            },
            maxFiles: 5,
            success: function(file, response) {
                $('form').append('<input type="hidden" name="images[]" value="' + response.name + '">')
                uploadedImagesMap[file.name] = response.name
            },
            removedfile: function(file) {
                console.log(file)
                file.previewElement.remove()
                let name = ''
                if (typeof file.file_name !== 'undefined') {
                    name = file.file_name
                } else {
                    name = uploadedImagesMap[file.name]
                }
                $('form').find('input[name="images[]"][value="' + name + '"]').remove()
            },
            init: function() {
                @if (isset($product) && $product->images)
                    let files = {!! json_encode($product->images) !!}
                    for (let i in files) {
                        let file = files[i]
                        this.options.addedfile.call(this, file)
                        this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
                        file.previewElement.classList.add('dz-complete')
                        $('form').append('<input type="hidden" name="images[]" value="' + file.file_name + '">')
                    }
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

        ClassicEditor
            .create(document.querySelector('.ckeditor'), {
                ckfinder: {
                    uploadUrl: '{{ route('admin.products.storeCKEditorImages') . '?_token=' . csrf_token() }}',
                }
            })
            .catch(error => {
                toastr.error(error, 'Error');
            });
    </script>
@endsection
