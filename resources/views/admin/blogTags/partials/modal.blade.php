<div class="modal-header border-bottom-0">
    <h5 class="modal-title">{{ $title }}</h5>
    <button aria-label="Close"
            class="close"
            data-dismiss="modal"
            type="button"><span aria-hidden="true"><i class="bi bi-x-lg"></i></span></button>
</div>

<form action="{{ $action }}"
      id="blogTagForm"
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
                   value="{{ old('name', $tag->name ?? '') }}">
            <div class="invalid-feedback d-none"></div>
        </div>
    </div>

    <div class="modal-footer border-top-0">
        <button class="btn btn-light"
                data-dismiss="modal"
                type="button"><i class="bi bi-x-circle mr-1"></i>Cancel</button>
        <button class="btn btn-primary"
                type="submit">
            <i class="bi bi-save mr-1"></i>{{ $mode === 'edit' ? 'Save Changes' : 'Save' }}
        </button>
    </div>
</form>
