{{-- resources/views/admin/orders/partials/change-status-modal.blade.php --}}
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
                        &times;
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
                                  rows="2">{{ old('cancel_reason') }}</textarea>
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
                               value="{{ old('tracking_number') }}">
                        <small class="text-muted">This field is required when status is On Delivery.</small>
                    </div>
                </form>
            </div>

            <div class="modal-footer border-top-0">
                <button class="btn btn-secondary"
                        data-dismiss="modal"
                        type="button">
                    <i class="bi bi-x-circle mr-1"></i>Close
                </button>
                <button class="btn btn-primary"
                        id="btn-submit-change-status"
                        type="button">
                    <i class="bi bi-check2-circle mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Utilities
        function toggleFields() {
            var val = document.getElementById('status') ? document.getElementById('status').value : '';
            var isCancelled = (val === 'cancelled');
            var isOnDelivery = (val === 'on_delivery');

            var cancelGroup = document.getElementById('group-cancel-reason');
            var trackGroup = document.getElementById('group-tracking-number');
            var cancelInput = document.getElementById('cancel_reason');
            var trackInput = document.getElementById('tracking_number');

            if (cancelGroup) {
                cancelGroup.classList.toggle('d-none', !isCancelled);
            }
            if (trackGroup) {
                trackGroup.classList.toggle('d-none', !isOnDelivery);
            }
            if (cancelInput) {
                cancelInput.required = isCancelled;
            }
            if (trackInput) {
                trackInput.required = isOnDelivery;
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target && e.target.id === 'status') {
                toggleFields();
            }
        });

        // Ensure initial state reflects current value
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', toggleFields);
        } else {
            toggleFields();
        }

        // Submit handler
        document.getElementById('btn-submit-change-status').addEventListener('click', function() {
            var form = document.getElementById('form-change-status');
            if (!form) return;

            // If form action still contains '/0/', try to replace with provided order_id
            var action = form.getAttribute('action') || '';
            if (action.indexOf('/0/') !== -1 || action.endsWith('/0')) {
                var orderIdInput = document.getElementById('order_id');
                var tmpl = form.getAttribute('data-action-template') || '';
                var idVal = orderIdInput ? String(orderIdInput.value || '') : '';

                if (!idVal) {
                    if (window.toastr) {
                        toastr.error('Order ID is required');
                    } else {
                        alert('Order ID is required');
                    }
                    return;
                }

                var newAction = tmpl.replace(/\/0(\/|$)/, '/' + idVal + '$1');
                form.setAttribute('action', newAction);
            }

            // Client-side validation based on selected status
            var statusEl = document.getElementById('status');
            var statusVal = statusEl ? String(statusEl.value || '') : '';
            if (!statusVal) {
                if (window.toastr) {
                    toastr.error('Please select a status');
                } else {
                    alert('Please select a status');
                }
                return;
            }

            if (statusVal === 'cancelled') {
                var cr = document.getElementById('cancel_reason');
                if (!cr || !String(cr.value || '').trim()) {
                    if (window.toastr) {
                        toastr.error('Cancel reason is required');
                    } else {
                        alert('Cancel reason is required');
                    }
                    return;
                }
            }

            if (statusVal === 'on_delivery') {
                var tn = document.getElementById('tracking_number');
                if (!tn || !String(tn.value || '').trim()) {
                    if (window.toastr) {
                        toastr.error('Tracking number is required');
                    } else {
                        alert('Tracking number is required');
                    }
                    return;
                }
            }

            form.submit();
        });
    })();
</script>
