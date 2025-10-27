@extends('layouts.admin')

@section('page_title', 'Create Campaign')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Campaigns' => route('admin.campaigns.index'),
            'Create Campaign' => null,
        ],
    ])
@endsection

@section('content')
    <div class="card shadow-lg">
        <form action="{{ route('admin.campaigns.store') }}"
              method="POST">
            @csrf
            <div class="card-header border-bottom-0">
                <div class="card-tools">
                    <a class="btn btn-default"
                       href="{{ route('admin.campaigns.index') }}">
                        <i class="bi bi-arrow-left mr-1"></i> Back to list
                    </a>
                </div>
            </div>

            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="required">Name</label>
                        <input autofocus
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                               name="name"
                               required
                               type="text"
                               value="{{ old('name') }}">
                        @if ($errors->has('name'))
                            <div class="invalid-feedback">{{ $errors->first('name') }}</div>
                        @endif
                    </div>

                    <div class="form-group col-md-6">
                        <label class="required">Subject</label>
                        <input class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}"
                               name="subject"
                               required
                               type="text"
                               value="{{ old('subject') }}">
                        @if ($errors->has('subject'))
                            <div class="invalid-feedback">{{ $errors->first('subject') }}</div>
                        @endif
                    </div>
                </div>

                <div class="form-group col-md-6">
                    <label>Scheduled At</label>
                    <input class="form-control {{ $errors->has('scheduled_at') ? 'is-invalid' : '' }}"
                           name="scheduled_at"
                           type="datetime-local"
                           value="{{ old('scheduled_at') }}">
                    @if ($errors->has('scheduled_at'))
                        <div class="invalid-feedback">{{ $errors->first('scheduled_at') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label class="required d-block">Content</label>
                    <div id="campaign-editor"
                         style="min-height: 200px; border: 1px solid #ced4da; border-radius: .25rem;"></div>
                    <input accept="image/*"
                           class="d-none"
                           id="quill-image-input"
                           type="file">
                    <textarea class="d-none"
                              id="campaign-content"
                              name="content">{!! old('content') !!}</textarea>
                    @if ($errors->has('content'))
                        <div class="invalid-feedback d-block">{{ $errors->first('content') }}</div>
                    @endif
                    <small class="text-muted d-block mt-1">
                        Email content uses Quill editor. HTML will be saved and sent to subscribers.
                    </small>
                </div>
            </div>

            <div class="card-footer border-top-0 gap-2 d-flex justify-content-end">
                <a class="btn btn-light"
                   href="{{ route('admin.campaigns.index') }}">
                    <i class="bi bi-x-circle mr-1"></i> Cancel
                </a>
                <button class="btn btn-primary"
                        type="submit">
                    <i class="bi bi-save mr-1"></i> Save
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editorEl = document.getElementById('campaign-editor');
            const contentEl = document.getElementById('campaign-content');
            const imageInput = document.getElementById('quill-image-input');
            const uploadUrl = "{{ route('admin.campaigns.upload_image') }}";
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

            // Initialize editor with old content if available
            if (contentEl.value) {
                quill.root.innerHTML = contentEl.value;
            }

            // Handle toolbar image button: open file selector then upload
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
                    quill.insertEmbed(range ? range.index : quill.getLength(), 'image', imageUrl,
                        'user');

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

            // Sync HTML to hidden textarea before submit
            const form = editorEl.closest('form');
            form.addEventListener('submit', function() {
                contentEl.value = quill.root.innerHTML;
            });
        });
    </script>
@endsection
