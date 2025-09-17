<div class="modal fade"
     id="modal-filter"
     tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title"
                    id="exampleModalLabel">Filter Blog</h5>
                <button aria-label="Close"
                        class="close"
                        data-dismiss="modal"
                        type="button">
                    <span aria-hidden="true">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </button>
            </div>
            <form id="form-filter">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="blog_category_id">Category</label>
                            <select class="custom-select"
                                    name="blog_category_id">
                                @foreach ($categories as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="blog_tag_id">Tag</label>
                            <select class="custom-select"
                                    name="blog_tag_id">
                                @foreach ($tags as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="user_id">Author</label>
                            <select class="custom-select"
                                    name="user_id">
                                @foreach ($authors as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="is_publish">Published</label>
                            <select class="custom-select"
                                    name="is_publish">
                                <option value="">All</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button class="btn btn-secondary"
                            data-dismiss="modal"
                            type="button">
                        <i class="bi bi-x-circle mr-1"></i>Cancel
                    </button>

                    <button class="btn btn-default"
                            id="btn-reset-filter"
                            type="button">
                        <i class="bi bi-arrow-repeat mr-1"></i>Reset
                    </button>

                    <button class="btn btn-primary"
                            id="btn-filter"
                            type="button">
                        <i class="bi bi-filter-circle mr-1"></i>Apply
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
