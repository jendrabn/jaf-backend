@extends('layouts.admin')

@section('page_title', 'Edit Blog')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Blog' => route('admin.blogs.index'),
            'Edit Blog' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <form action="{{ route('admin.blogs.update', $blog->id) }}"
              enctype="multipart/form-data"
              method="POST">
            @csrf
            @method('PUT')

            <div class="card-header border-bottom-0">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.blogs.index') }}"><i class="bi bi-arrow-left mr-1"></i>Back to list</a>
                </div>
            </div>

            <div class="card-body">
                <div class="form-group">
                    <label class="required">Featured Image</label>
                    <div class="needsclick dropzone {{ $errors->has('featured_image') ? 'is-invalid' : '' }}"
                         id="images-dropzone">
                    </div>
                    @if ($errors->has('featured_image'))
                        <div class="invalid-feedback">{{ $errors->first('featured_image') }}</div>
                    @endif

                    <input class="form-control mt-3"
                           name="featured_image_description"
                           placeholder="Featured Image Description"
                           type="text"
                           value="{{ old('featured_image_description', $blog->featured_image_description) }}">
                    @if ($errors->has('featured_image_description'))
                        <div class="invalid-feedback">{{ $errors->first('featured_image_description') }}</div>
                    @endif
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required">Title</label>
                        <input autofocus
                               class="form-control {{ $errors->has('title') ? 'is-invalid' : '' }}"
                               name="title"
                               required
                               type="text"
                               value="{{ old('title', $blog->title) }}">
                        @if ($errors->has('title'))
                            <div class="invalid-feedback">{{ $errors->first('title') }}</div>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Author</label>
                        <select class="form-control select2 {{ $errors->has('user_id') ? 'is-invalid' : '' }}"
                                name="user_id"
                                required
                                style="width: 100%">
                            @foreach ($authors as $id => $name)
                                <option @selected(old('user_id', $blog->user_id) == $id)
                                        value="{{ $id }}">{{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('user_id'))
                            <div class="invalid-feedback">{{ $errors->first('user_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Category</label>
                        <select class="form-control select2 {{ $errors->has('blog_category_id') ? 'is-invalid' : '' }}"
                                name="blog_category_id"
                                required
                                style="width: 100%">
                            @foreach ($categories as $id => $name)
                                <option @selected(old('blog_category_id', $blog->blog_category_id) == $id)
                                        value="{{ $id }}">{{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('blog_category_id'))
                            <div class="invalid-feedback">{{ $errors->first('category_id') }}</div>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label>Tags</label>
                        <div style="padding-bottom: 4px">
                            <span class="btn btn-info btn-xs select-all"
                                  style="border-radius: 0">Select all</span>
                            <span class="btn btn-info btn-xs deselect-all"
                                  style="border-radius: 0">Deselect all</span>
                        </div>
                        <select class="form-control select2 {{ $errors->has('tag_ids') ? 'is-invalid' : '' }}"
                                multiple
                                name="tag_ids[]"
                                style="width: 100%;">
                            @foreach ($tags as $id => $name)
                                <option @selected(in_array($id, old('tag_ids', $blog->tags->pluck('id')->toArray())))
                                        value="{{ $id }}">
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('tag_ids'))
                            <div class="invalid-feedback">{{ $errors->first('tag_ids') }}</div>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <label class="required d-block">Content</label>
                    <div id="blog-content-editor"
                         style="min-height: 200px; border: 1px solid #ced4da; border-radius: .25rem;"></div>
                    <input accept="image/*"
                           class="d-none"
                           id="quill-image-input-blog"
                           type="file">
                    <textarea class="d-none"
                              id="blog-content"
                              name="content">{!! old('content', $blog->content) !!}</textarea>
                    @if ($errors->has('content'))
                        <div class="invalid-feedback d-block">{{ $errors->first('content') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input @checked(old('is_publish', $blog->is_publish))
                               class="custom-control-input {{ $errors->has('is_publish') ? 'is-invalid' : '' }}"
                               id="is_publish"
                               name="is_publish"
                               type="checkbox">
                        <label class="custom-control-label"
                               for="is_publish">Published</label>
                        @if ($errors->has('is_publish'))
                            <div class="invalid-feedback">{{ $errors->first('is_publish') }}</div>
                        @endif
                    </div>
                </div>

            </div>
            <div class="card-footer border-top-0 d-flex gap-2 justify-content-end">
                <a class="btn btn-light"
                   href="{{ route('admin.blogs.index') }}"><i class="bi bi-x-circle mr-1"></i> Cancel</a>
                <button class="btn btn-primary"
                        type="submit"><i class="bi bi-save mr-1"></i> Save Changes</button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        // Dropzone for Featured Image (unchanged)
        Dropzone.options.imagesDropzone = {
            url: '{{ route('admin.blogs.storeMedia') }}',
            maxFilesize: 10, // MB
            acceptedFiles: '.jpeg,.jpg,.png',
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            params: {
                size: 10,
                width: 10000,
                height: 10000
            },
            maxFiles: 1,
            success: function(file, response) {
                $('form').find('input[name="featured_image"]').remove()
                $('form').append('<input type="hidden" name="featured_image" value="' + response.name + '">')
            },
            removedfile: function(file) {
                file.previewElement.remove()
                if (file.status !== 'error') {
                    $('form').find('input[name="featured_image"]').remove()
                    this.options.maxFiles = this.options.maxFiles + 1
                }
            },
            init: function() {
                @if (isset($blog) && $blog->featured_image)
                    let file = {!! json_encode($blog->featured_image) !!}
                    this.options.addedfile.call(this, file)
                    this.options.thumbnail.call(this, file, file.preview ?? file.preview_url)
                    file.previewElement.classList.add('dz-complete')
                    $('form').append('<input type="hidden" name="featured_image" value="' + file.file_name + '">')
                    this.options.maxFiles = this.options.maxFiles - 1
                @endif
            },
            error: function(file, response) {
                let message = '';

                if ($.type(response) === 'string') {
                    message = response
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
        };

        // Quill editor for Blog Content with shared upload endpoint
        (function() {
            const editorEl = document.getElementById('blog-content-editor');
            const contentEl = document.getElementById('blog-content');
            const imageInput = document.getElementById('quill-image-input-blog');
            const uploadUrl = "{{ route('admin.blogs.upload_image') }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video', 'formula'],
                [{
                    'header': 1
                }, {
                    'header': 2
                }],
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }, {
                    'list': 'check'
                }],
                [{
                    'script': 'sub'
                }, {
                    'script': 'super'
                }],
                [{
                    'indent': '-1'
                }, {
                    'indent': '+1'
                }],
                [{
                    'direction': 'rtl'
                }],
                [{
                    'size': ['small', false, 'large', 'huge']
                }],
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],
                [{
                    'color': []
                }, {
                    'background': []
                }],
                [{
                    'font': []
                }],
                [{
                    'align': []
                }],
                ['clean']
            ];

            const quill = new Quill(editorEl, {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });

            if (contentEl.value) {
                quill.root.innerHTML = contentEl.value;
            }

            quill.getModule('toolbar').addHandler('image', function() {
                imageInput.click();
            });

            imageInput.addEventListener('change', async function(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);

                try {
                    const resp = await fetch(uploadUrl, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: formData
                    });

                    if (!resp.ok) {
                        throw new Error('Upload failed (' + resp.status + ')');
                    }

                    const data = await resp.json();
                    const imageUrl = data.url;

                    const range = quill.getSelection(true);
                    quill.insertEmbed(range ? range.index : quill.getLength(), 'image', imageUrl, 'user');

                    if (window.toastr) {
                        toastr.success('Image uploaded');
                    }
                } catch (err) {
                    console.error(err);
                    if (window.toastr) {
                        toastr.error(err.message || 'Failed to upload image');
                    }
                } finally {
                    imageInput.value = '';
                }
            });

            const form = editorEl.closest('form');
            form.addEventListener('submit', function() {
                contentEl.value = quill.root.innerHTML;
            });
        })();
    </script>
@endsection
