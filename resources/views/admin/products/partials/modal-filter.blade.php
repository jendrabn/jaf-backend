<div class="modal fade"
     data-backdrop="static"
     id="modal-filter">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">
                    Filter Product
                </h5>
                <button aria-label="Close"
                        class="close"
                        data-dismiss="modal"
                        type="button">
                    <span aria-hidden="true">
                        <i class="bi bi-x-lg"></i>
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <form class="form-row"
                      id="form-filter">
                    <div class="form-group col-md-6">
                        <label for="_product_category_id">Category</label>
                        <select class="custom-select"
                                id="_product_category_id"
                                name="product_category_id">
                            @foreach ($product_categories as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="_product_brand_id">Brand</label>
                        <select class="custom-select"
                                id="_product_brand_id"
                                name="product_brand_id">
                            @foreach ($product_brands as $id => $name)
                                <option value="{{ $id }}"> {{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="_sex">Gender</label>
                        <select class="custom-select"
                                id="_sex"
                                name="sex">
                            <option selected
                                    value="">All</option>
                            @foreach (App\Models\Product::SEX_SELECT as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="_is_publish">Published</label>
                        <select class="custom-select"
                                id="_is_publish"
                                name="is_publish">
                            <option selected
                                    value="">All</option>
                            <option value="1">Published</option>
                            <option value="0">Unpublished</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button class="btn btn-secondary mr-2"
                        data-dismiss="modal"
                        type="button">
                    <i class="bi bi-x-circle mr-1"></i>Cancel
                </button>
                <button class="btn btn-default mr-2"
                        id="btn-reset-filter">
                    <i class="bi bi-arrow-repeat mr-1"></i>Reset
                </button>
                <button class="btn btn-primary"
                        id="btn-filter"
                        type="button">
                    <i class="bi bi-filter-circle mr-1"></i>Apply
                </button>
            </div>
        </div>
    </div>
</div>
