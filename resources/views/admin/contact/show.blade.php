@extends('layouts.admin')

@section('page_title', 'Support - Message #' . $message->id)

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Support' => route('admin.messages.index'),
            'Message #' . $message->id => null,
        ],
    ])
@endsection

@section('content')
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Message Detail</h3>
                    <span
                          class="badge badge-pill badge-{{ ['new' => 'secondary', 'in_progress' => 'warning', 'resolved' => 'success', 'spam' => 'danger'][$message->status] ?? 'secondary' }}">
                        {{ strtoupper(str_replace('_', ' ', $message->status)) }}
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless kv-table">
                        <tbody>
                            <tr>
                                <th>Received At</th>
                                <td>{{ optional($message->created_at)->format('d-m-Y H:i') }}</td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td>{{ $message->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $message->email }}</td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td>{{ $message->phone ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>Message</th>
                                <td style="white-space: pre-wrap">{{ $message->message }}</td>
                            </tr>
                            <tr>
                                <th>Handler</th>
                                <td>{{ $message->handler?->name ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Handled At</th>
                                <td>{{ $message->handled_at ? $message->handled_at->format('d-m-Y H:i') : '-' }}</td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td>{{ $message->notes ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>IP</th>
                                <td>{{ $message->ip ?: '-' }}</td>
                            </tr>
                            <tr>
                                <th>User Agent</th>
                                <td class="text-monospace small">{{ $message->user_agent ?: '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Update Status</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.messages.update', $message->id) }}"
                          method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="form-group">
                            <label class="section-label d-block mb-2">Status</label>
                            <select class="form-control"
                                    name="status">
                                <option @selected($message->status === 'new')
                                        value="new">New</option>
                                <option @selected($message->status === 'in_progress')
                                        value="in_progress">In Progress</option>
                                <option @selected($message->status === 'resolved')
                                        value="resolved">Resolved</option>
                                <option @selected($message->status === 'spam')
                                        value="spam">Spam</option>
                            </select>
                            @error('status')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="section-label d-block mb-2">Notes</label>
                            <textarea class="form-control"
                                      name="notes"
                                      placeholder="Add notes..."
                                      rows="3">{{ old('notes', $message->notes) }}</textarea>
                            @error('notes')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-primary"
                                type="submit">
                            <i class="bi bi-save mr-1"></i> Save
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h3 class="card-title mb-0">Reply to Sender</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.messages.reply', $message->id) }}"
                          method="POST">
                        @csrf

                        <div class="form-group">
                            <label class="section-label d-block mb-2">Subject</label>
                            <input class="form-control"
                                   name="subject"
                                   placeholder="Subject"
                                   type="text"
                                   value="{{ old('subject') }}">
                            @error('subject')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="section-label d-block mb-2">Body</label>
                            <div id="reply-body-editor"
                                 style="min-height: 200px; border: 1px solid #ced4da; border-radius: .25rem;"></div>
                            <input accept="image/*"
                                   class="d-none"
                                   id="quill-image-input-reply"
                                   type="file">
                            <textarea class="d-none"
                                      id="reply-body-content"
                                      name="body">{{ old('body') }}</textarea>
                            @error('body')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>

                        <button class="btn btn-outline-primary"
                                type="submit">
                            <i class="bi bi-send mr-1"></i> Send Reply
                        </button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Previous Replies</h3>
                    <span class="badge badge-secondary badge-pill">{{ $message->replies->count() }}</span>
                </div>
                <div class="card-body">
                    @forelse ($message->replies as $reply)
                        <div class="border rounded p-2 mb-2">
                            <div class="d-flex align-items-center justify-content-between">
                                <strong>{{ $reply->subject }}</strong>
                                <span
                                      class="badge badge-{{ ['draft' => 'secondary', 'sent' => 'success', 'failed' => 'danger'][$reply->status] ?? 'secondary' }} badge-pill">
                                    {{ strtoupper($reply->status) }}
                                </span>
                            </div>
                            <div class="small text-muted mb-1">
                                {{ $reply->sent_at ? $reply->sent_at->format('d-m-Y H:i') : optional($reply->created_at)->format('d-m-Y H:i') }}
                                —
                                by {{ $reply->admin?->name ?? '-' }}
                            </div>
                            <div class="small"
                                 style="white-space: pre-wrap">{{ \Illuminate\Support\Str::limit($reply->body, 400) }}
                            </div>
                            @if ($reply->status === 'failed' && $reply->error_message)
                                <div class="text-danger small mt-1">Error: {{ $reply->error_message }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-muted">No replies yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .section-label {
            text-transform: uppercase;
            letter-spacing: .06em;
            font-size: .75rem;
            color: #6c757d;
            font-weight: 600;
        }

        .kv-table th {
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .75rem;
            color: #6c757d;
            padding-right: .75rem;
            vertical-align: middle !important;
            width: 40%;
            white-space: nowrap;
        }

        .kv-table td {
            vertical-align: middle !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editorEl = document.getElementById('reply-body-editor');
            const contentEl = document.getElementById('reply-body-content');
            const imageInput = document.getElementById('quill-image-input-reply');
            const uploadUrl = "{{ route('admin.messages.upload_image') }}";
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

            const form = editorEl.closest('form');
            form.addEventListener('submit', function() {
                contentEl.value = quill.root.innerHTML;
            });
        });
    </script>
@endsection
