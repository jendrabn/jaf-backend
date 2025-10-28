@extends('layouts.admin')

@section('page_title', 'Order Detail - ' . $order->id)

@section('breadcrumb')
    @include('partials.breadcrumb', [
        'items' => [
            'Dashboard' => route('admin.home'),
            'Order' => route('admin.orders.index'),
            'Order Detail' => null,
        ],
    ])
@endsection

@section('content')
    @php
        use App\Models\Order;
        use App\Enums\OrderStatus;
        use App\Enums\InvoiceStatus;
        use App\Enums\ShippingStatus;
        use App\Enums\PaymentStatus;

        $statusEnum = OrderStatus::from($order->status);
        $payment = $order->invoice->payment;
        $shipping = $order->shipping;

        $invoiceStatus = InvoiceStatus::from($order->invoice->status);
        $shippingStatus = ShippingStatus::from($shipping->status);

        $isPaid = $order->status === OrderStatus::Processing->value || $invoiceStatus === InvoiceStatus::Paid;

        $isShipped = $order->status === OrderStatus::OnDelivery->value || $shippingStatus === ShippingStatus::Shipped;

        $isCompleted = $order->status === OrderStatus::Completed->value;
        $isPendingPayment = OrderStatus::PendingPayment->value === $order->status;
        $isPending = OrderStatus::Pending->value === $order->status;
        $isProcessing = OrderStatus::Processing->value === $order->status;
        $isOnDelivery = OrderStatus::OnDelivery->value === $order->status;
    @endphp

    <div class="d-flex align-items-center justify-content-end gap-2 mb-3">
        <button class="btn btn-primary"
                data-target="#modal-change-status"
                data-toggle="modal"
                type="button">
            <i class="bi bi-arrow-repeat mr-1"></i> Change Status
        </button>
        <a class="btn btn-default"
           href="{{ route('admin.orders.index') }}">
            <i class="bi bi-arrow-left mr-1"></i> Back to list
        </a>
    </div>

    @includeIf('admin.orders.partials.change-status-modal', ['order' => $order])

    {{-- Stepper --}}
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="stepper-wrapper">
                <div class="stepper-item completed">
                    <div class="step-counter"><i class="bi bi-receipt"></i></div>
                    <div class="step-name">Order Placed</div>
                    <div class="step-date">{{ $order->created_at }}</div>
                </div>
                <div class="step-line {{ $isPaid ? 'active' : '' }}"></div>
                <div class="stepper-item {{ $isPaid ? 'completed' : '' }}">
                    <div class="step-counter"><i class="bi bi-currency-dollar"></i></div>
                    <div class="step-name">Order Paid <br>(@Rp($order->invoice->amount))</div>
                    <div class="step-date">{{ $order->confirmed_at }}</div>
                </div>
                <div class="step-line {{ $isShipped ? 'active' : '' }}"></div>
                <div class="stepper-item {{ $isShipped ? 'completed' : '' }}">
                    <div class="step-counter"><i class="bi bi-truck"></i></div>
                    <div class="step-name">Order Shipped Out</div>
                    <div class="step-date">{{ $order->shipping->updated_at }}</div>
                </div>
                <div class="step-line {{ $isCompleted ? 'active' : '' }}"></div>
                <div class="stepper-item {{ $isCompleted ? 'completed' : '' }}">
                    <div class="step-counter"><i class="bi bi-check-all"></i></div>
                    <div class="step-name">Order Completed</div>
                    <div class="step-date">{{ $order->completed_at }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        {{-- Payment Information --}}
        <div class="col-md-6">
            <div class="card shadow-lg h-100">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title">Payment Information</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ PaymentStatus::from($payment->status)->color() }}"
                              title="Payment Status">
                            {{ PaymentStatus::from($payment->status)->label() }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <p class="section-label mb-2"><i class="bi bi-person mr-1"></i> Payment From</p>

                            @if ($payment->method === 'bank' && $payment->bank)
                                <table class="table table-borderless kv-table">
                                    <tbody>
                                        <tr>
                                            <th>Bank</th>
                                            <td>{{ $payment->bank?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Name</th>
                                            <td>{{ $payment->bank?->account_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Number</th>
                                            <td>{{ $payment->bank?->account_number }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @elseif ($payment->method === 'ewallet' && $payment->ewallet)
                                <table class="table table-borderless kv-table">
                                    <tbody>
                                        <tr>
                                            <th>E-Wallet</th>
                                            <td>{{ $payment->ewallet?->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Name</th>
                                            <td>{{ $payment->ewallet?->account_name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Username</th>
                                            <td>{{ $payment->ewallet?->account_username }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone Number</th>
                                            <td>{{ $payment->ewallet?->phone }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            @elseif ($payment->method === 'gateway')
                                <table class="table table-borderless kv-table">
                                    <tbody>
                                        <tr>
                                            <th>Provider</th>
                                            <td>{{ strtoupper($payment->info['provider'] ?? 'midtrans') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Client Key</th>
                                            <td>{{ $payment->info['client_key'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Gateway Fee</th>
                                            <td>@Rp($order->gateway_fee)</td>
                                        </tr>
                                        @if (!empty($payment->info['redirect_url']))
                                            <tr>
                                                <th>Redirect URL</th>
                                                <td class="text-break"><a href="{{ $payment->info['redirect_url'] }}"
                                                       target="_blank">{{ $payment->info['redirect_url'] }}</a></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        <div class="col-12 col-md-6">
                            <p class="section-label mb-2"><i class="bi bi-shop mr-1"></i> Payment To</p>

                            <table class="table table-borderless kv-table">
                                <tbody>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td>
                                            <span
                                                  class="h5 d-block text-danger font-weight-bold mb-0">@Rp($order->invoice->amount)</span>
                                            @if ($isPendingPayment)
                                                <small class="text-muted d-block">Due date at
                                                    {{ $order->invoice->due_date }}</small>
                                            @endif
                                        </td>
                                    </tr>

                                    @if ($payment->method === 'bank' && $payment->bank)
                                        <tr>
                                            <th>Bank</th>
                                            <td>{{ $payment->info['name'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Name</th>
                                            <td>{{ $payment->info['account_name'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Number</th>
                                            <td>{{ $payment->info['account_number'] }}</td>
                                        </tr>
                                    @elseif ($payment->method === 'ewallet' && $payment->ewallet)
                                        <tr>
                                            <th>E-Wallet</th>
                                            <td>{{ $payment->info['name'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Name</th>
                                            <td>{{ $payment->info['account_name'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Account Username</th>
                                            <td>{{ $payment->info['account_username'] }}</td>
                                        </tr>
                                        <tr>
                                            <th>Phone Number</th>
                                            <td>{{ $payment->info['phone'] }}</td>
                                        </tr>
                                    @elseif ($payment->method === 'gateway')
                                        <tr>
                                            <th>Provider</th>
                                            <td>{{ strtoupper($payment->info['provider'] ?? 'midtrans') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Client Key</th>
                                            <td>{{ $payment->info['client_key'] ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Gateway Fee</th>
                                            <td>@Rp($order->gateway_fee)</td>
                                        </tr>
                                        @if (!empty($payment->info['redirect_url']))
                                            <tr>
                                                <th>Redirect URL</th>
                                                <td class="text-break"><a href="{{ $payment->info['redirect_url'] }}"
                                                       target="_blank">{{ $payment->info['redirect_url'] }}</a></td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($isPending)
                        <div class="d-flex gap-2 mt-3">
                            <button class="btn btn-primary"
                                    id="btn-accept-payment"
                                    type="button">
                                <i class="bi bi-check2-circle mr-1"></i> Accept
                            </button>
                            <button class="btn btn-outline-danger"
                                    id="btn-reject-payment"
                                    type="button">
                                <i class="bi bi-x-lg mr-1"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        {{-- Shipping Information --}}
        <div class="col-md-6">
            <div class="card shadow-lg h-100">
                <div class="card-header border-bottom-0">
                    <h3 class="card-title">Shipping Information</h3>
                    <div class="card-tools">
                        <span class="badge badge-{{ ShippingStatus::from($shipping->status)->color() }}"
                              title="Shipping Status">
                            {{ ShippingStatus::from($shipping->status)->label() }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6 mb-3 mb-md-0">
                            <table class="table table-sm table-borderless kv-table">
                                <tr>
                                    <th>Courier</th>
                                    <td>{{ strtoupper($shipping->courier) }} - {{ $shipping->courier_name }}</td>
                                </tr>
                                <tr>
                                    <th>Courier Service</th>
                                    <td>{{ $shipping->service }}{{ $shipping->service_name ? ' - ' . $shipping->service_name : '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estimation</th>
                                    <td>{{ $shipping->etd }}</td>
                                </tr>
                                <tr>
                                    <th>Weight</th>
                                    <td>{{ (int) ceil($shipping->weight / 1000) }} kg</td>
                                </tr>
                                <tr>
                                    <th>Tracking Number</th>
                                    <td class="d-flex align-items-center">
                                        <span>{{ $shipping->tracking_number }}</span>
                                    </td>
                                </tr>
                            </table>

                            <div class="d-flex gap-2">
                                @if ($isProcessing)
                                    <button class="btn btn-primary"
                                            id="btn-confirm-shipping">
                                        <i class="bi bi-plus-lg mr-1"></i> Add Tracking Number
                                    </button>
                                @endif

                                @if (!empty($shipping->tracking_number) && $isOnDelivery)
                                    <button class="btn btn-primary"
                                            onclick="showTrackingModal(this);">
                                        <i class="bi bi-truck mr-1"></i> Track Order
                                    </button>
                                @endif
                            </div>
                        </div>

                        {{-- ADDRESS --}}
                        <div class="col-12 col-md-6">
                            <p class="section-label mb-2"><i class="bi bi-geo-alt mr-1"></i> Shipping Address</p>
                            <address class="mb-0">
                                <strong>{{ $shipping->address['name'] }}</strong><br>
                                {{ $shipping->address['phone'] ?? '-' }}<br>
                                {{ $shipping->address['address'] ?? '-' }},
                                {{ $shipping->address['city'] ?? '-' }},
                                {{ $shipping->address['district'] ?? '-' }},
                                {{ $shipping->address['province'] ?? '-' }},
                                {{ $shipping->address['postal_code'] ?? '-' }}
                            </address>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-lg order-card rounded-0">
        <div class="order-sheet">
            <div class="order-body">
                <div class="order-header">
                    <h4 class="mb-1 order-title">ORDER INFORMATION</h4>
                    <div class="order-meta">
                        <span class="label">Order ID</span>
                        <div class="order-ref mb-2">#{{ $order->id ?? '-' }}</div>

                        <span class="label">Order Date</span>
                        <div class="mb-2">
                            {{ optional($order->created_at)->format('d M Y H:i') ?? $order->created_at }}</div>

                        <span class="label">Status</span>
                        <div>
                            <span class="badge badge-{{ $statusEnum->color() }}">
                                {{ $statusEnum->label() }}
                            </span>
                        </div>
                    </div>
                </div>

                <hr class="hr-dashed">

                {{-- Bill to & Reference --}}
                <div class="row">
                    <div class="col-12 col-md-7 mb-3">
                        <div class="text-uppercase text-muted small mb-1">Customer</div>
                        @if ($order->user)
                            <div class="font-weight-bold">
                                {!! $order->user->name . externalIconLink(route('admin.users.show', $order->user->id)) !!}
                            </div>
                        @else
                            <div class="text-muted">
                                -
                            </div>
                        @endif
                    </div>
                    <div class="col-12 col-md-5 text-md-right">
                        <div class="text-uppercase text-muted small mb-1">Reference</div>
                        <div class="font-weight-bold">
                            {{ $order->invoice->number ? '#' . $order->invoice->number : '-' }}
                        </div>
                    </div>
                </div>

                {{-- Items --}}
                <div class="table-responsive mt-3">
                    <table class="table table-sm table-order">
                        <thead>
                            <tr>
                                <th class="text-center w-40">#</th>
                                <th>Product(s)</th>
                                <th class="text-right w-120 d-none d-sm-table-cell">Price</th>
                                <th class="text-center w-80">Quantity</th>
                                <th class="text-right w-140">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img alt=""
                                                 class="thumb-44 rounded border mr-2 d-none d-sm-block"
                                                 src="{{ $item->product?->image?->preview_url }}">

                                            <span class="text-body product-name">
                                                {!! $item->name . externalIconLink(route('admin.products.show', $item->id)) !!}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="text-right d-none d-sm-table-cell">@Rp($item->price)</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right">@Rp((int) $item->price * (int) $item->quantity)</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Totals (kanan) + Payment --}}
                <div class="row justify-content-end">
                    <div class="col-12 col-md-6 col-lg-5">
                        <table class="table table-sm table-borderless table-totals mb-2">
                            <tbody>
                                <tr>
                                    <td>Total Price</td>
                                    <td>@Rp($order->total_price)</td>
                                </tr>
                                <tr>
                                    <td>Discount</td>
                                    <td>-@Rp($order->discount)</td>
                                </tr>
                                <tr>
                                    <td>Shipping Cost</td>
                                    <td>@Rp($order->shipping_cost)</td>
                                </tr>
                                <tr>
                                    <td>Tax</td>
                                    <td>@Rp($order->tax_amount)</td>
                                </tr>
                                <tr>
                                    <td>Payment Gateway Fee</td>
                                    <td>@Rp($order->gateway_fee)</td>
                                </tr>
                                <tr>
                                    <td><span class="mr-2">Total Amount</span></td>
                                    <td class="grand-total">@Rp($order->invoice->amount)</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="text-right">
                            <span class="text-uppercase mr-1 small text-muted">Payment Method:</span>
                            <span class="">
                                {{ strtoupper($payment->method ?? '-') }}@if (!empty($payment->info['name']))
                                    — {{ $payment->info['name'] }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Note (opsional) --}}
                <div class="mt-4">
                    <div class="text-uppercase small text-muted mb-1">Note</div>
                    <div>{{ $order->note ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.orders.confirm-payment', $order->id) }}"
          hidden
          id="form-payment"
          method="POST">
        @csrf @method('PUT')
        <input id="_action"
               name="action"
               type="text" />
        <input id="_cancel_reason"
               name="cancel_reason"
               type="text">
    </form>

    @if ($order->status === \App\Enums\OrderStatus::Processing->value)
        <form action="{{ route('admin.orders.confirm-shipping', $order->id) }}"
              hidden
              id="form-confirm-shipping"
              method="POST">
            @method('PUT') @csrf
            <input class="form-control"
                   id="_tracking_number"
                   name="tracking_number"
                   type="text" />
        </form>
    @endif

    @include('admin.orders.partials.tracking-modal')

@endsection

@section('styles')
    <style>
        /* Paper look */
        .order-sheet {
            width: 100%;
            max-width: 100%;
            background: #fff;
            position: relative
        }

        .order-card {
            border: 1px solid #e9ecef
        }

        .order-body {
            padding: 1.25rem
        }

        @media (min-width:768px) {
            .order-body {
                padding: 2rem 2.25rem
            }
        }

        /* Header */
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap
        }

        .order-title {
            letter-spacing: .5px
        }

        .order-meta {
            min-width: 260px;
            text-align: right
        }

        .order-meta .label {
            display: block;
            font-size: .75rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: .06em
        }

        .order-ref {
            font-weight: 600
        }

        /* Divider */
        .hr-dashed {
            border: 0;
            border-top: 1px dashed #cfd8dc;
            margin: .75rem 0 1.25rem
        }

        /* Table: items */
        .table-order thead th {
            background: #f8f9fa
        }

        .table-order td,
        .table-order th {
            vertical-align: middle
        }

        .table-order .w-40 {
            width: 40px
        }

        .table-order .w-80 {
            width: 80px
        }

        .table-order .w-120 {
            width: 120px
        }

        .table-order .w-140 {
            width: 140px
        }

        .thumb-44 {
            width: 44px;
            height: 44px;
            object-fit: cover
        }

        .product-name {
            max-width: 420px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        @media (max-width:575.98px) {
            .product-name {
                max-width: 220px
            }
        }

        /* Totals (kanan) */
        .table-totals td {
            padding: .45rem .75rem
        }

        .table-totals td:first-child {
            width: 60%;
            text-align: right;
            color: #6c757d
        }

        .table-totals td:last-child {
            text-align: right
        }

        .table-totals tr:last-child td {
            border-top: 1px solid #dee2e6;
            font-weight: 600
        }

        .grand-total {
            font-size: 1.25rem;
            font-weight: 700
        }

        /* Small helpers */
        .badge-pill {
            vertical-align: middle
        }
    </style>

    <style>
        .stepper-wrapper {
            display: flex;
            align-items: flex-start;
            margin: 40px 0;
        }

        .stepper-item {
            position: relative;
            text-align: center;
            flex: 1;
        }

        .step-line {
            position: relative;
            height: 4px;
            background-color: #e0e0e0;
            flex: 1;
            z-index: 0;
            top: 20px;
        }

        .step-line.active {
            background-color: #28a745;
        }

        .step-counter {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 40px;
            height: 40px;
            background-color: #e0e0e0;
            border-radius: 50%;
            font-size: 20px;
            color: #fff;
            position: relative;
            z-index: 1;
            margin: 0 auto;
            transition: background-color 0.3s;
        }

        .completed .step-counter {
            background-color: #28a745;
        }

        .stepper-item.completed .step-line {
            background-color: #28a745;
        }

        .step-name {
            font-weight: bold;
            margin-top: 10px;
        }

        .step-date {
            font-size: 0.9rem;
            color: #888;
        }

        .step-counter:hover {
            background-color: #6c757d;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .stepper-wrapper {
                flex-direction: column;
                align-items: center
            }

            .step-line {
                margin: 10px 0;
            }
        }
    </style>

    <style>
        @media (max-width: 576px) {

            #btn-accept-payment,
            #btn-reject-payment {
                width: 100%;
            }

            #btn-accept-payment {
                margin-bottom: .5rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        $(() => {
            const $formPayment = $('#form-payment');
            const $formConfirmShipping = $('#form-confirm-shipping');

            const setInputValue = ($form, selector, value) => {
                if ($form && $form.length) {
                    const $input = $form.find(selector);
                    if ($input && $input.length) {
                        $input.val(value);
                    }
                }
            };

            $('#btn-accept-payment').on('click', async () => {
                const result = await Swal.fire({
                    titleText: 'Payment Acceptance',
                    text: "Please check the payment details carefully before accepting.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check2-circle mr-1"></i> Accept Payment',
                });

                if (result.isConfirmed && $formPayment.length) {
                    setInputValue($formPayment, '#_action', 'accept');
                    $formPayment.trigger('submit');
                }
            });

            $('#btn-reject-payment').on('click', async () => {
                const result = await Swal.fire({
                    title: 'Payment Rejection',
                    text: "Please enter the reason for rejecting the payment.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-x-lg mr-1"></i> Reject Payment',
                    input: 'text',
                    inputLabel: 'Cancel Reason',
                    inputValue: 'Invalid Payment',
                    inputValidator: (value) => (value ? null : 'Required'),
                });

                if (result.isConfirmed && $formPayment.length) {
                    setInputValue($formPayment, '#_action', 'reject');
                    setInputValue($formPayment, '#_cancel_reason', result.value);
                    $formPayment.trigger('submit');
                }
            });

            $('#btn-confirm-shipping').on('click', async () => {
                const result = await Swal.fire({
                    title: 'Add Tracking Number',
                    text: "Please enter the tracking number carefully so that the system can track your order.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-save mr-1"></i> Save',
                    input: 'text',
                    inputLabel: 'Tracking Number',
                    inputValidator: (value) => (value ? null : 'Required'),
                });

                if (result.isConfirmed && $formConfirmShipping.length) {
                    setInputValue($formConfirmShipping, '#_tracking_number', result.value);
                    $formConfirmShipping.trigger('submit');
                }
            });

            window.showTrackingModal = (el) => {
                if (el && el.preventDefault) {
                    el.preventDefault();
                }

                const url = "{{ route('admin.orders.track-waybill', $order->id) }}";
                const $modal = $('#trackingWaybillModal');

                $modal.find('.modal-title').text('Waybill Tracking');
                $modal.find('.modal-body').html(
                    '<div class="text-center small text-muted py-4">Loading tracking data...</div>'
                );

                // Robust modal show: prefer Bootstrap 4 jQuery plugin, fallback to Bootstrap 5 API, then manual
                let modalShown = false;
                try {
                    if (typeof $.fn.modal === 'function') {
                        $modal.modal('show');
                        modalShown = true;
                    }
                } catch (e) {}

                if (!modalShown && window.bootstrap && typeof window.bootstrap.Modal === 'function') {
                    const inst = new window.bootstrap.Modal($modal.get(0));
                    inst.show();
                    modalShown = true;
                }

                if (!modalShown) {
                    $modal.addClass('show')
                        .css('display', 'block')
                        .attr('aria-hidden', 'false');
                    document.body.classList.add('modal-open');
                    $('<div class="modal-backdrop fade show"></div>').appendTo(document.body);
                }

                $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'json',
                        headers: {
                            'Accept': 'application/json'
                        },
                    })
                    .done((resp) => {
                        const meta = (resp && resp.meta) ? resp.meta : {};
                        const isSuccess = (meta.code === 200) || (String(meta.status || '')
                            .toLowerCase() === 'success');

                        if (!isSuccess) {
                            const msg = meta.message || 'Unable to fetch tracking data.';
                            $modal.find('.modal-body').html(
                                '<div class="alert alert-danger mb-0" role="alert">' + msg + '</div>'
                            );
                            return;
                        }

                        const html = (resp && resp.html) ? resp.html :
                            '<div class="text-muted small">No tracking content.</div>';
                        $modal.find('.modal-body').html(html);
                    })
                    .fail((xhr) => {
                        let msg = 'Unable to fetch tracking data.';
                        try {
                            if (xhr && xhr.responseJSON && xhr.responseJSON.meta && xhr.responseJSON.meta
                                .message) {
                                msg = xhr.responseJSON.meta.message;
                            }
                        } catch (e) {}
                        $modal.find('.modal-body').html(
                            '<div class="alert alert-danger mb-0" role="alert">' + msg + '</div>'
                        );
                    });
            };

            // Fallback close handler for cases where $.fn.modal is unavailable
            $(document).on('click', '#trackingWaybillModal [data-dismiss="modal"]', function(ev) {
                ev.preventDefault();
                try {
                    if (typeof $.fn.modal === 'function') {
                        $('#trackingWaybillModal').modal('hide');
                        return;
                    }
                } catch (e) {}
                const $m = $('#trackingWaybillModal');
                $m.removeClass('show')
                    .css('display', '')
                    .attr('aria-hidden', 'true');
                document.body.classList.remove('modal-open');
                $('.modal-backdrop').remove();
            });

        });
    </script>
@endsection
