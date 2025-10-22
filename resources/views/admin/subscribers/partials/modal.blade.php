@php
    $mode = isset($subscriber) ? 'edit' : 'create';
    $title = $mode === 'edit' ? 'Edit Subscriber' : 'Create Subscriber';
    $action = $mode === 'edit' ? route('admin.subscribers.update', $subscriber) : route('admin.subscribers.store');
@endphp

<div class="modal-header">
    <h5 class="modal-title">{{ $title }}</h5>
    <button aria-label="Close"
            class="close"
            data-dismiss="modal"
            type="button"><span aria-hidden="true">&times;</span></button>
</div>

<form action="{{ $action }}"
      id="subscriberForm"
      method="POST">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="modal-body">
        <div class="form-group">
            <label class="required">Email</label>
            <input class="form-control"
                   name="email"
                   required
                   type="email"
                   value="{{ old('email', $subscriber->email ?? '') }}">
            <div class="invalid-feedback d-none"></div>
        </div>

        <div class="form-group">
            <label>Name</label>
            <input class="form-control"
                   name="name"
                   placeholder="Optional"
                   type="text"
                   value="{{ old('name', $subscriber->name ?? '') }}">
            <div class="invalid-feedback d-none"></div>
        </div>

        <div class="form-group">
            <label class="required">Status</label>
            <select class="form-control"
                    name="status"
                    required>
                <option value="">Select Status</option>
                <option {{ old('status', $subscriber->status->value ?? '') === 'pending' ? 'selected' : '' }}
                        value="pending">Pending</option>
                <option {{ old('status', $subscriber->status->value ?? '') === 'subscribed' ? 'selected' : '' }}
                        value="subscribed">Subscribed</option>
                <option {{ old('status', $subscriber->status->value ?? '') === 'unsubscribed' ? 'selected' : '' }}
                        value="unsubscribed">Unsubscribed</option>
            </select>
            <div class="invalid-feedback d-none"></div>
        </div>

        @if ($mode === 'edit')
            <div class="form-group">
                <label>Token</label>
                <input class="form-control"
                       readonly
                       type="text"
                       value="{{ $subscriber->token }}">
                <small class="form-text text-muted">This token is used for unsubscribe links</small>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Subscribed At</label>
                        <input class="form-control"
                               readonly
                               type="text"
                               value="{{ $subscriber->subscribed_at ? $subscriber->subscribed_at->format('d M Y H:i') : '-' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Unsubscribed At</label>
                        <input class="form-control"
                               readonly
                               type="text"
                               value="{{ $subscriber->unsubscribed_at ? $subscriber->unsubscribed_at->format('d M Y H:i') : '-' }}">
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="modal-footer">
        <button class="btn btn-light"
                data-dismiss="modal"
                type="button"><i class="bi bi-x-circle mr-1"></i>Cancel</button>
        <button class="btn btn-primary"
                type="submit">
            <i class="bi bi-check2-circle mr-1"></i>{{ $mode === 'edit' ? 'Save Changes' : 'Save' }}
        </button>
    </div>
</form>
