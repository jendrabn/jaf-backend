@extends('layouts.admin')

@section('page_title', 'Edit Campaign')

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Campaigns' => route('admin.campaigns.index'),
            'Edit Campaign' => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('admin.campaigns.update', $campaign) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-lg">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="card-tools">
                            <a class="btn btn-default"
                               href="{{ route('admin.campaigns.index') }}">
                                <i class="bi bi-arrow-left mr-1"></i>Back to list
                            </a>
                            <a class="btn btn-primary"
                               href="{{ route('admin.campaigns.show', $campaign) }}">
                                <i class="bi bi-eye mr-1"></i>Detail
                            </a>
                            <button class="btn btn-success"
                                    data-url="{{ route('admin.campaigns.send_all', $campaign) }}"
                                    id="btn-send-all"
                                    type="button">
                                <i class="bi bi-send-check mr-1"></i>Send to all
                            </button>
                            <button class="btn btn-info"
                                    data-url="{{ route('admin.campaigns.test_send', $campaign) }}"
                                    id="btn-test-send"
                                    type="button">
                                <i class="bi bi-envelope-paper mr-1"></i>Test send
                            </button>
                            <button class="btn btn-danger"
                                    data-url="{{ route('admin.campaigns.destroy', $campaign) }}"
                                    id="btn-delete"
                                    type="button">
                                <i class="bi bi-trash mr-1"></i>Delete
                            </button>
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
                                       value="{{ old('name', $campaign->name) }}">
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
                                       value="{{ old('subject', $campaign->subject) }}">
                                @if ($errors->has('subject'))
                                    <div class="invalid-feedback">{{ $errors->first('subject') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Status</label>
                                <input class="form-control"
                                       disabled
                                       type="text"
                                       value="{{ $campaign->status->getLabel() }}">
                                <small class="text-muted">Status follows sending lifecycle.</small>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Scheduled At</label>
                                <input class="form-control {{ $errors->has('scheduled_at') ? 'is-invalid' : '' }}"
                                       name="scheduled_at"
                                       type="datetime-local"
                                       value="{{ old('scheduled_at', optional($campaign->scheduled_at)->format('Y-m-d\TH:i')) }}">
                                @if ($errors->has('scheduled_at'))
                                    <div class="invalid-feedback">{{ $errors->first('scheduled_at') }}</div>
                                @endif
                            </div>
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
                                      name="content">{!! old('content', $campaign->content) !!}</textarea>
                            @if ($errors->has('content'))
                                <div class="invalid-feedback d-block">{{ $errors->first('content') }}</div>
                            @endif
                            <small class="text-muted d-block mt-1">
                                Email content uses Quill editor. HTML will be saved and sent to subscribers.
                            </small>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <a class="btn btn-light mr-2"
                           href="{{ route('admin.campaigns.index') }}">
                            <i class="bi bi-x-circle mr-1"></i>Cancel
                        </a>
                        <button class="btn btn-primary"
                                type="submit">
                            <i class="bi bi-check2-circle mr-1"></i>Save
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
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

            // Initialize editor with existing content
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

            // Action buttons
            const sendAllBtn = document.getElementById('btn-send-all');
            const testSendBtn = document.getElementById('btn-test-send');
            const deleteBtn = document.getElementById('btn-delete');

            sendAllBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;

                Swal.fire({
                    title: 'Send to all subscribers?',
                    text: 'This will queue emails for all subscribed users.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Send',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: url,
                            success: function(data) {
                                toastr.success(data.message ||
                                    'Campaign sending queued.');
                            },
                        });
                    }
                });
            });

            testSendBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;

                Swal.fire({
                    title: 'Test send',
                    input: 'email',
                    inputLabel: 'Enter recipient email for test',
                    inputPlaceholder: 'email@example.com',
                    showCancelButton: true,
                    confirmButtonText: 'Queue Test Email',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Email is required';
                        }
                        return null;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: url,
                            data: {
                                email: result.value
                            },
                            success: function(data) {
                                toastr.success(data.message || 'Test email queued.');
                            },
                        });
                    }
                });
            });

            deleteBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.dataset.url;

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This campaign will be deleted.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Delete',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            headers: {
                                'x-csrf-token': _token
                            },
                            method: 'POST',
                            url: url,
                            data: {
                                _method: 'DELETE'
                            },
                            success: function(data) {
                                toastr.success(data.message ||
                                    'Campaign deleted successfully.');
                                window.location.href =
                                    "{{ route('admin.campaigns.index') }}";
                            },
                        });
                    }
                });
            });
        });
    </script>
@endsection
