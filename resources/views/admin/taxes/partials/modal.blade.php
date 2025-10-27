<div class="modal-header">
    <h5 class="modal-title">{{ $title }}</h5>
    <button aria-label="Close"
            class="close"
            data-dismiss="modal"
            type="button"><span aria-hidden="true"><i class="bi bi-x-lg"></i></span></button>
</div>

<form action="{{ $action }}"
      id="taxForm"
      method="POST">
    @csrf
    @if ($mode === 'edit')
        @method('PUT')
    @endif

    <div class="modal-body">
        <div class="form-group">
            <label class="required">Name</label>
            <input class="form-control"
                   name="name"
                   required
                   type="text"
                   value="{{ old('name', $tax->name ?? '') }}">
            <div class="invalid-feedback d-none"></div>
        </div>

        <div class="form-group">
            <label class="required">Rate (%)</label>
            <input class="form-control"
                   max="100"
                   min="0"
                   name="rate"
                   required
                   step="0.01"
                   type="number"
                   value="{{ old('rate', isset($tax) ? number_format((float) $tax->rate, 2, '.', '') : '') }}">
            <small class="form-text text-muted">Contoh: 10 untuk 10%.</small>
            <div class="invalid-feedback d-none"></div>
        </div>
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
