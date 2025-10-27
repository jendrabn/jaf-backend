<div aria-hidden="true"
     class="modal fade"
     id="modal-change-status"
     role="dialog"
     tabindex="-1">
    <div class="modal-dialog"
         role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">
                    Change Order Status
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
                @php
                    use App\Enums\OrderStatus;
                @endphp

                <form action="{{ route('admin.orders.change-status', isset($order) ? $order->id : 0) }}"
                      autocomplete="off"
                      data-action-template="{{ route('admin.orders.change-status', 0) }}"
                      id="form-change-status"
                      method="POST">
                    @csrf
                    @method('PUT')

                    @if (!isset($order))
                        <div class="form-group">
                            <label for="order_id">Order ID</label>
                            <input autocomplete="off"
                                   class="form-control"
                                   id="order_id"
                                   min="1"
                                   name="order_id"
                                   required
                                   type="number"
                                   value="{{ old('order_id') }}">
                        </div>
                    @endif

                    <div class="form-group">
                        <label class="required"
                               for="status">Status</label>
                        <select class="custom-select"
                                id="status"
                                name="status"
                                required>
                            <option value="">-- Select Status --</option>
                            @foreach (OrderStatus::cases() as $s)
                                <option @selected(old('status') === $s->value)
                                        value="{{ $s->value }}">{{ $s->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group d-none"
                         id="group-cancel-reason">
                        <label class="required"
                               for="cancel_reason">Cancel Reason</label>
                        <textarea autocomplete="off"
                                  class="form-control"
                                  id="cancel_reason"
                                  name="cancel_reason"
                                  rows="2">{{ old('cancel_reason', $order->cancel_reason) }}</textarea>
                        <small class="text-muted">This field is required when status is Cancelled.</small>
                    </div>

                    <div class="form-group d-none"
                         id="group-tracking-number">
                        <label class="required"
                               for="tracking_number">Tracking Number</label>
                        <input autocomplete="off"
                               class="form-control"
                               id="tracking_number"
                               name="tracking_number"
                               type="text"
                               value="{{ old('tracking_number', $order->tracking_number) }}">
                        <small class="text-muted">This field is required when status is On Delivery.</small>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top-0">
                <button class="btn btn-secondary"
                        data-dismiss="modal"
                        type="button">
                    <i class="bi bi-x-circle mr-1"></i> Close
                </button>
                <button class="btn btn-primary"
                        id="btn-submit-change-status"
                        type="button">
                    <i class="bi bi-save mr-1"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(function() {
            const toggleFields = () => {
                const val = String($('#status').val() || '');
                const isCancelled = (val === 'cancelled');
                const isOnDelivery = (val === 'on_delivery');

                $('#group-cancel-reason').toggleClass('d-none', !isCancelled);
                $('#group-tracking-number').toggleClass('d-none', !isOnDelivery);
                $('#cancel_reason').prop('required', isCancelled);
                $('#tracking_number').prop('required', isOnDelivery);
            };

            // Bind changes using jQuery
            $(document).on('change', '#status', toggleFields);

            // Ensure initial state reflects current value
            toggleFields();
            // Also ensure state when modal becomes visible
            $(document).on('shown.bs.modal', '#modal-change-status', toggleFields);

            // Submit handler
            $('#btn-submit-change-status').on('click', () => {
                const $form = $('#form-change-status');
                if (!$form.length) {
                    return;
                }

                // If form action still contains '/0', replace with provided order_id
                let action = $form.attr('action') || '';
                if (/\/0(\/?$)/.test(action)) {
                    const tmpl = $form.data('action-template') || $form.attr('data-action-template') || '';
                    const idVal = String(($('#order_id').val() || '')).trim();

                    if (!idVal) {
                        if (window.toastr) {
                            toastr.error('Order ID is required');
                        } else {
                            alert('Order ID is required');
                        }
                        return;
                    }

                    const newAction = tmpl.replace(/\/0(\/|$)/, `/${idVal}$1`);
                    $form.attr('action', newAction);
                }

                // Client-side validation based on selected status
                const statusVal = String(($('#status').val() || '')).trim();
                if (!statusVal) {
                    if (window.toastr) {
                        toastr.error('Please select a status');
                    } else {
                        alert('Please select a status');
                    }
                    return;
                }

                if (statusVal === 'cancelled') {
                    const cr = String(($('#cancel_reason').val() || '')).trim();
                    if (!cr) {
                        if (window.toastr) {
                            toastr.error('Cancel reason is required');
                        } else {
                            alert('Cancel reason is required');
                        }
                        return;
                    }
                }

                if (statusVal === 'on_delivery') {
                    const tn = String(($('#tracking_number').val() || '')).trim();
                    if (!tn) {
                        if (window.toastr) {
                            toastr.error('Tracking number is required');
                        } else {
                            alert('Tracking number is required');
                        }
                        return;
                    }
                }

                $form.trigger('submit');
            });
        })
    </script>
@endpush
