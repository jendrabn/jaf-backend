<div aria-hidden="true"
     class="modal fade"
     id="priceCalculatorModal"
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Calculate Flash Price</h5>
                <button type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="mb-1 text-muted">Original price:</p>
                <h4 id="priceModalOriginalPrice"
                    class="text-primary"></h4>

                <div class="form-group mt-4">
                    <label class="d-block mb-2">Discount type</label>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio"
                               class="custom-control-input"
                               id="price-adjust-flat"
                               name="price-adjust-type"
                               value="flat"
                               checked>
                        <label class="custom-control-label"
                               for="price-adjust-flat">Flat (IDR)</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline">
                        <input type="radio"
                               class="custom-control-input"
                               id="price-adjust-percent"
                               name="price-adjust-type"
                               value="percent">
                        <label class="custom-control-label"
                               for="price-adjust-percent">Percent (%)</label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="price-adjust-value">Discount value</label>
                    <input type="number"
                           class="form-control"
                           id="price-adjust-value"
                           placeholder="Enter discount value">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button"
                        class="btn btn-light"
                        data-dismiss="modal">Cancel</button>
                <button type="button"
                        class="btn btn-primary"
                        id="saveCalculatedPrice">
                    Save price
                </button>
            </div>
        </div>
    </div>
</div>
