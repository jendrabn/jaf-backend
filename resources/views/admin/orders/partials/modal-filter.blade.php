<div class="modal fade"
     id="modal-filter"
     tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">
                    <i class="bi bi-filter"></i> Filter
                </h5>
                <button aria-label="Close"
                        class="close"
                        data-dismiss="modal"
                        type="button">
                    <span aria-hidden="true"><i class="bi bi-x-lg"></i></span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-filter">
                    <div class="form-group">
                        <label for="date">Date</label>

                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="bi bi-calendar"></i>
                                </span>
                            </div>
                            <input class="form-control date-range"
                                   name="daterange"
                                   type="text">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select class="custom-select"
                                id="payment_method"
                                name="payment_method">
                            <option value="">All</option>
                            <option value="bank">Bank</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="gateway">Gateway</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="custom-select"
                                id="status"
                                name="status">
                            <option value="">All</option>
                            @foreach (App\Enums\OrderStatus::cases() as $status)
                                <option value="{{ $status->value }}">{{ $status->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button class="btn btn-secondary"
                        data-dismiss="modal"
                        type="button">
                    <i class="bi bi-x-circle mr-1"></i> Close
                </button>
                <button class="btn btn-default"
                        id="btn-reset-filter"
                        type="button">
                    <i class="bi bi-arrow-repeat mr-1"></i> Reset
                </button>
                <button class="btn btn-primary"
                        id="btn-filter"
                        type="button">
                    <i class="bi bi-filter-circle mr-1"></i> Apply
                </button>
            </div>
        </div>
    </div>
</div>
