<div class="modal-header">
  <h5 class="modal-title">{{ $title }}</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
</div>

<form action="{{ $action }}" method="POST" id="blogTagForm">
  @csrf
  @if($mode === 'edit')
    @method('PUT')
  @endif

  <div class="modal-body">
    <div class="form-group">
      <label class="required">Name</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $tag->name ?? '') }}" required>
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
