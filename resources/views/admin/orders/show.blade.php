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
        $status = App\Models\Order::STATUSES[$order->status];
        $payment = $order->invoice->payment;
        $shipping = $order->shipping;
    @endphp

    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="stepper-wrapper">
                <div class="stepper-item completed">
                    <div class="step-counter"><i class="bi bi-receipt"></i></div>
                    <div class="step-name">Order Placed</div>
                    <div class="step-date">{{ $order->created_at }}</div>
                </div>
                <div
                     class="step-line {{ $order->status === App\Models\Order::STATUS_PROCESSING || $order->invoice->status === App\Models\Invoice::STATUS_PAID ? 'active' : '' }}">
                </div>
                <div
                     class="stepper-item {{ $order->status === App\Models\Order::STATUS_PROCESSING || $order->invoice->status === App\Models\Invoice::STATUS_PAID ? 'completed' : '' }}">
                    <div class="step-counter"><i class="bi bi-currency-dollar"></i></div>
                    <div class="step-name">Order Paid <br>(@Rp($order->invoice->amount))</div>
                    <div class="step-date">{{ $order->confirmed_at }}</div>
                </div>
                <div
                     class="step-line {{ $order->status === App\Models\Order::STATUS_ON_DELIVERY || $order->shipping->status === App\Models\Shipping::STATUS_SHIPPED ? 'active' : '' }}">
                </div>
                <div
                     class="stepper-item {{ $order->status === App\Models\Order::STATUS_ON_DELIVERY || $order->shipping->status === App\Models\Shipping::STATUS_SHIPPED ? 'completed' : '' }}">
                    <div class="step-counter"><i class="bi bi-truck"></i></div>
                    <div class="step-name">Order Shipped Out</div>
                    <div class="step-date">{{ $order->shipping->updated_at }}</div>
                </div>
                <div class="step-line {{ $order->status === App\Models\Order::STATUS_COMPLETED ? 'active' : '' }}"></div>
                <div class="stepper-item {{ $order->status === App\Models\Order::STATUS_COMPLETED ? 'completed' : '' }}">
                    <div class="step-counter"><i class="bi bi-check-all"></i></div>
                    <div class="step-name">Order Completed</div>
                    <div class="step-date">{{ $order->completed_at }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        {{-- PAYMENT --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Payment Information</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary badge-pill">
                            {{ App\Models\Payment::STATUSES[$payment->status]['label'] }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        {{-- FROM --}}
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
                                                <td><a href="{{ $payment->info['redirect_url'] }}"
                                                       target="_blank">{{ $payment->info['redirect_url'] }}</a></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        {{-- TO --}}
                        <div class="col-12 col-md-6">
                            <p class="section-label mb-2"><i class="bi bi-shop mr-1"></i> Payment To</p>

                            <table class="table table-borderless kv-table">
                                <tbody>
                                    <tr>
                                        <th>Total Amount</th>
                                        <td>
                                            <span class="h5 d-block mb-0">@Rp($order->invoice->amount)</span>
                                            @if (\App\Models\Order::STATUS_PENDING_PAYMENT === $order->status)
                                                <small class="text-muted d-block">
                                                    Due date at {{ $order->invoice->due_date }}
                                                </small>
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
                                                <td><a href="{{ $payment->info['redirect_url'] }}"
                                                       target="_blank">{{ $payment->info['redirect_url'] }}</a></td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if ($order->status === App\Models\Order::STATUS_PENDING)
                        <div class="mt-3">
                            <button class="btn btn-primary btn-sm mr-1"
                                    id="btn-accept-payment"
                                    type="button">
                                <i class="bi bi-check2-circle mr-1"></i> Accept
                            </button>
                            <button class="btn btn-outline-danger btn-sm"
                                    id="btn-reject-payment"
                                    type="button">
                                <i class="bi bi-x-lg mr-1"></i> Reject
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SHIPPING --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h3 class="card-title mb-0">Shipping Information</h3>
                    <div class="card-tools">
                        <span class="badge badge-secondary badge-pill">
                            {{ App\Models\Shipping::STATUSES[$shipping->status]['label'] }}
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        {{-- FACTS --}}
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

                            @if ($order->status === App\Models\Order::STATUS_PROCESSING)
                                <button class="btn btn-outline-primary btn-sm"
                                        id="btn-confirm-shipping">
                                    <i class="bi bi-plus-lg mr-1"></i> Add Tracking Number
                                </button>
                            @endif

                            @if (!empty($shipping->tracking_number))
                                <button class="btn btn-primary"
                                        onclick="showTrackingModal(this);">
                                    Track
                                </button>
                            @endif
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

    <div class="container-fluid px-0">
        <div class="card shadow-lg order-card rounded-0">
            <div class="order-sheet">
                <div class="order-body">
                    {{-- Header --}}
                    <div class="order-header">
                        <div>
                            <h4 class="mb-1 order-title">ORDER INFORMATION</h4>
                            <div class="text-muted small">
                                {{ config('app.name') }}
                            </div>
                        </div>
                        <div class="order-meta">
                            <span class="label">Order ID</span>
                            <div class="order-ref mb-2">#{{ $order->id ?? '-' }}</div>

                            <span class="label">Tanggal Order</span>
                            <div class="mb-2">
                                {{ optional($order->created_at)->format('d M Y H:i') ?? $order->created_at }}</div>

                            <span class="label">Status</span>
                            <div>
                                <span
                                      class="badge badge-{{ \App\Models\Order::STATUSES[$order->status]['color'] }} badge-pill">
                                    {{ \App\Models\Order::STATUSES[$order->status]['label'] }}
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
                                <div class="font-weight-bold d-flex align-items-center">
                                    {{ $order->user->name }} <a class="icon-btn text-muted ml-1 small"
                                       href="{{ route('admin.users.show', $order->user->id) }}"><i
                                           class="bi bi-box-arrow-up-right"></i></a>
                                </div>
                            @else
                                <div class="text-muted">—</div>
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
                        <table class="table table-sm table-bordered table-order">
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
                                                    {{ $item->name }} <a class="icon-btn text-muted ml-1 small"
                                                       href="{{ $item->product_id ? route('admin.products.show', $item->product_id) : 'javascript:;' }}"
                                                       target="{{ $item->product_id ? '_blank' : '_self' }}">
                                                        <i class="bi bi-box-arrow-up-right"></i></a>
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
                                        <td>Total Harga</td>
                                        <td>@Rp($order->total_price)</td>
                                    </tr>
                                    <tr>
                                        <td>Diskon</td>
                                        <td>-@Rp($order->discount)</td>
                                    </tr>
                                    <tr>
                                        <td>Ongkos Kirim</td>
                                        <td>@Rp($order->shipping_cost)</td>
                                    </tr>
                                    <tr>
                                        <td>Pajak</td>
                                        <td>@Rp($order->tax_amount)</td>
                                    </tr>
                                    <tr>
                                        <td>Payment Gateway Fee</td>
                                        <td>@Rp($order->gateway_fee)</td>
                                    </tr>
                                    <tr>
                                        <td><span class="mr-2">Total Bayar</span></td>
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

    @if ($order->status === App\Models\Order::STATUS_PROCESSING)
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

    <!-- Tracking Waybill Modal -->
    <div aria-hidden="true"
         aria-labelledby="trackingWaybillModalLabel"
         class="modal fade"
         id="trackingWaybillModal"
         role="dialog"
         tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered"
             role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"
                        id="trackingWaybillModalLabel">Waybill Tracking</h5>
                    <button aria-label="Close"
                            class="close"
                            data-dismiss="modal"
                            type="button">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center small text-muted py-4">Loading tracking data...</div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('styles')

    {{-- ===== Order Information (invoice-like, Bootstrap 4.6, full width, single card) ===== --}}
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
        .section-label {
            text-transform: uppercase;
            letter-spacing: .06em;
            font-size: .75rem;
            color: #6c757d;
            font-weight: 600;
        }

        .kv-table th {
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: .75rem;
            /* white-space: nowrap; */
            color: #6c757d;
            /* width: 40%; */
            padding-right: .75rem;
            vertical-align: middle !important;
        }

        .kv-table td {
            color: #212529;
            vertical-align: middle !important;
        }

        .kv-table th,
        .kv-table td {
            padding: .4rem .25rem;
        }

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
        $(function() {
            $("#btn-accept-payment").on("click", function(e) {
                Swal.fire({
                    titleText: "Payment Acceptance",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Accept Payment",
                }).then(function(result) {
                    if (result.isConfirmed) {
                        const form = $("#form-payment");
                        form.find("#_action").val("accept");
                        form.submit();
                    }
                });
            });

            $("#btn-reject-payment").on("click", function(e) {
                Swal.fire({
                    title: "Payment Rejection",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Reject Payment",
                    input: "text",
                    inputLabel: "Cancel Reason",
                    inputValue: "Invalid Payment",
                    inputValidator: function(value) {
                        if (!value) return "Required";
                    },
                }).then(function(result) {
                    if (result.isConfirmed) {
                        const form = $("#form-payment");
                        form.find("#_action").val("reject");
                        form.find("#_cancel_reason").val(result.value);
                        form.submit();
                    }
                });
            });

            $("#btn-confirm-shipping").on("click", function() {
                Swal.fire({
                    title: "Add Tracking Number",
                    text: "You won't be able to revert this!",
                    showCancelButton: true,
                    confirmButtonText: "Save",
                    input: "text",
                    inputLabel: "Tracking Number",
                    inputValidator: function(value) {
                        if (!value) return "Required";
                    },
                }).then(function(result) {
                    if (result.isConfirmed) {
                        const form = $("#form-confirm-shipping");
                        form.find("#_tracking_number").val(result.value);
                        form.submit();
                    }
                });
            });


            window.showTrackingModal = function(el) {
                if (el && el.preventDefault) {
                    el.preventDefault();
                }

                var url = "{{ route('admin.orders.track-waybill', $order->id) }}";

                var $modal = $('#trackingWaybillModal');
                $modal.find('.modal-title').text('Waybill Tracking');
                $modal.find('.modal-body').html(
                    '<div class="text-center small text-muted py-4">Loading tracking data...</div>');
                $modal.modal('show');

                $.ajax({
                    url: url,
                    method: "GET",
                    dataType: "json",
                    headers: {
                        "Accept": "application/json"
                    }
                }).done(function(resp) {
                    var meta = resp && resp.meta ? resp.meta : {};
                    if ((meta.code && meta.code !== 200) || (meta.status && ('' + meta.status)
                            .toLowerCase() !== 'success')) {
                        var msg = meta.message || "Unable to fetch tracking data.";
                        $modal.find('.modal-body').html(
                            '<div class="alert alert-danger mb-0" role="alert">' + msg + '</div>');
                        return;
                    }

                    var html = (resp && resp.html) ? resp.html :
                        '<div class="text-muted small">No tracking content.</div>';
                    $modal.find('.modal-body').html(html);
                }).fail(function(xhr) {
                    var msg = "Unable to fetch tracking data.";
                    try {
                        if (xhr && xhr.responseJSON && xhr.responseJSON.meta && xhr.responseJSON.meta
                            .message) {
                            msg = xhr.responseJSON.meta.message;
                        }
                    } catch (e) {}
                    $modal.find('.modal-body').html(
                        '<div class="alert alert-danger mb-0" role="alert">' + msg + '</div>');
                });
            };
        });
    </script>
@endsection
