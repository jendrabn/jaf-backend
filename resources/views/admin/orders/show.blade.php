@extends('layouts.admin', ['title' => 'Order'])

@section('content')
    @php
        $status = App\Models\Order::STATUSES[$order->status];
        $payment = $order->invoice->payment;
        $shipping = $order->shipping;
    @endphp

    <div class="container mt-5">
        <div class="stepper-wrapper">
            <div class="stepper-item completed">
                <div class="step-counter"><i class="fas fa-receipt"></i></div>
                <div class="step-name">Order Placed</div>
                <div class="step-date">{{ $order->created_at }}</div>
            </div>
            <div
                 class="step-line {{ $order->status === App\Models\Order::STATUS_PROCESSING || $order->invoice->status === App\Models\Invoice::STATUS_PAID ? 'active' : '' }}">
            </div>
            <div
                 class="stepper-item {{ $order->status === App\Models\Order::STATUS_PROCESSING || $order->invoice->status === App\Models\Invoice::STATUS_PAID ? 'completed' : '' }}">
                <div class="step-counter"><i class="fas fa-dollar-sign"></i></div>
                <div class="step-name">Order Paid <br>(@Rp($order->invoice->amount))</div>
                <div class="step-date">{{ $order->confirmed_at }}</div>
            </div>
            <div
                 class="step-line {{ $order->status === App\Models\Order::STATUS_ON_DELIVERY || $order->shipping->status === App\Models\Shipping::STATUS_SHIPPED ? 'active' : '' }}">
            </div>
            <div
                 class="stepper-item {{ $order->status === App\Models\Order::STATUS_ON_DELIVERY || $order->shipping->status === App\Models\Shipping::STATUS_SHIPPED ? 'completed' : '' }}">
                <div class="step-counter"><i class="fas fa-truck"></i></div>
                <div class="step-name">Order Shipped Out</div>
                <div class="step-date">{{ $order->shipping->updated_at }}</div>
            </div>
            <div class="step-line {{ $order->status === App\Models\Order::STATUS_COMPLETED ? 'active' : '' }}"></div>
            <div class="stepper-item {{ $order->status === App\Models\Order::STATUS_COMPLETED ? 'completed' : '' }}">
                <div class="step-counter"><i class="fas fa-check"></i></div>
                <div class="step-name">Order Completed</div>
                <div class="step-date">{{ $order->completed_at }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment Information</h3>
            <span class="float-right font-weight-bold">
                {{ App\Models\Payment::STATUSES[$payment->status]['label'] }}
            </span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <p class="mb-2">Payment From (Buyer)</p>

                    @if ($payment->method === 'bank' && $payment->bank)
                        <table class="table table-sm table-borderless">
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
                        <table class="table table-sm table-borderless">
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
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <p class="mb-2">Payment To (Seller)</p>
                    <table class="table table-sm table-borderless">
                        <tbody>
                            <tr>
                                <th>Total Amount</th>
                                <td>
                                    <span class="h5">@Rp($order->invoice->amount)</span> <br>
                                    @if (\App\Models\Order::STATUS_PENDING_PAYMENT === $order->status)
                                        <span class="text-muted text-small">Due date at
                                            {{ $order->invoice->due_date }}</span></span>
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
                            @endif
                        </tbody>
                    </table>

                </div>
            </div>

            @if ($order->status === App\Models\Order::STATUS_PENDING)
                <div class="row mt-3">
                    <div class="col-12">
                        <button class="btn btn-primary mr-1"
                                id="btn-accept-payment"
                                type="button">
                            <i class="fa-solid fa-check"></i>
                            Accept
                        </button>
                        <button class="btn btn-danger"
                                id="btn-reject-payment"
                                type="button">
                            <i class="fa-solid fa-times"></i>
                            Reject
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Shipping Information</h3>
            <span
                  class="float-right font-weight-bold">{{ App\Models\Shipping::STATUSES[$shipping->status]['label'] }}</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12 col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th>Courier</th>
                            <td>
                                {{ strtoupper($shipping->courier) }} -
                                {{ $shipping->courier_name }}
                            </td>
                        </tr>
                        <tr>
                            <th>Courier Service</th>
                            <td>
                                {{ $shipping->service }}
                                {{ $shipping->service_name ? ' - ' . $shipping->service_name : '' }}
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
                            <td>{{ $shipping->tracking_number }}</td>
                        </tr>
                    </table>

                    @if ($order->status === App\Models\Order::STATUS_PROCESSING)
                        <button class="btn btn-primary"
                                id="btn-confirm-shipping">
                            <i class="fa-solid fa-plus"></i> Add Tracking Number
                        </button>
                    @endif
                </div>

                <div class="col-12 col-md-6">
                    <p class="mb-2">
                        Shipping Address
                    </p>

                    <address>
                        <strong>{{ $shipping->address['name'] }}</strong><br />
                        {{ $shipping->address['phone'] }}<br />
                        {{ $shipping->address['address'] }},
                        {{ $shipping->address['city'] }},
                        {{ $shipping->address['district'] }},
                        {{ $shipping->address['province'] }},
                        {{ $shipping->address['postal_code'] }}
                    </address>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Order Information</h3>

            <span class="font-weight-bold float-right">{{ $order->invoice->number }}</span>
        </div>
        <div class="card-body">

            <div class="row mb-3">
                <div class="col-12 col-md-6">
                    <strong>Order ID: </strong> {{ $order->id }}<br />
                    <strong>Order Date: </strong>{{ $order->created_at }}<br />
                    <strong>Order Status: </strong>
                    <span
                          class="badge badge-{{ \App\Models\Order::STATUSES[$order->status]['color'] }}">{{ \App\Models\Order::STATUSES[$order->status]['label'] }}</span><br />
                    <strong>Buyer: </strong>
                    @if ($order->user)
                        <a class="text-body"
                           href="{{ route('admin.users.show', $order->user->id) }}"
                           target="_blank">{{ $order->user->name }}</a>
                    @endif
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm text-right table-borderless">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-left">Product(s)</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $key => $item)
                            <tr>
                                <td class="text-center align-middle">{{ $loop->iteration }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="mr-1">
                                            <div style="width: 50px; height: 50px">
                                                <img class="img-fluid"
                                                     src="{{ $item->product?->image->preview_url }}"
                                                     style="object-fit: cover" />
                                            </div>
                                        </div>
                                        <a class="text-body"
                                           href=" {{ $item->product_id ? route('admin.products.show', $item->product_id) : 'javascript:;' }}"
                                           target="_blank">
                                            {{ $item->name }}
                                        </a>
                                    </div>
                                </td>
                                <td>@Rp($item->price)</td>
                                <td>{{ $item->quantity }}</td>
                                <td>@Rp((int) $item->price * (int) $item->quantity)</td>
                            </tr>
                        @endforeach
                        <tr>
                            <td class="text-right font-weight-bold"
                                colspan="4">Total Price</td>
                            <td>@Rp($order->total_price)</td>
                        </tr>
                        <tr>
                            <td class="text-right font-weight-bold"
                                colspan="4">Shipping Cost</td>
                            <td>@Rp($order->shipping_cost)</td>
                        </tr>
                        <tr>
                            <td class="text-right font-weight-bold"
                                colspan="4">Total Amount</td>
                            <td class="h3">@Rp($order->invoice->amount)</td>
                        </tr>
                        <tr>
                            <td class="text-right font-weight-bold"
                                colspan="4">Payment Method</td>
                            <td>{{ strtoupper($payment->method) . ' - ' . $payment->info['name'] }}</td>
                        </tr>
                    </tbody>
                </table>
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
              id="form-confirm-shipping"
              method="POST">
            @method('PUT') @csrf
            <input class="form-control"
                   id="_tracking_number"
                   name="tracking_number"
                   type="text" />
        </form>
    @endif
@endsection

@section('styles')
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
        });
    </script>
@endsection
