<div class="modal-header">
  <h5 class="modal-title">{{ $title }}</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>

<form action="{{ $action }}" method="POST" id="taxForm">
  @csrf
  @if($mode === 'edit')
    @method('PUT')
  @endif

  <div class="modal-body">
    <div class="form-group">
      <label class="required">Name</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $tax->name ?? '') }}" required>
      <div class="invalid-feedback d-none"></div>
    </div>

    <div class="form-group">
      <label class="required">Rate (%)</label>
      <input type="number" step="0.01" min="0" max="100" name="rate" class="form-control" value="{{ old('rate', isset($tax) ? number_format((float) $tax->rate, 2, '.', '') : '') }}" required>
      <small class="form-text text-muted">Contoh: 10 untuk 10%.</small>
      <div class="invalid-feedback d-none"></div>
    </div>
  </div>

  <div class="modal-footer">
    <button type="button" class="btn btn-light" data-dismiss="modal"><i class="bi bi-x-circle mr-1"></i>Cancel</button>
    <button type="submit" class="btn btn-primary">
      <i class="bi bi-check2-circle mr-1"></i>{{ $mode === 'edit' ? 'Save Changes' : 'Save' }}
    </button>
  </div>
</form>
