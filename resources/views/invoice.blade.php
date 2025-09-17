<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0"
          name="viewport">
    <meta content="ie=edge"
          http-equiv="X-UA-Compatible">
    <title>Invoice</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 18px;
            font-weight: bold;
            color: #4A4A4A;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 10px;
            font-size: 12px;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f8f8f8;
            text-align: left;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .total-section {
            font-size: 14px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }

        .total-section .title {
            text-align: right;
            font-size: 12px;
            color: #555;
        }

        .total-section .amount {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .info-table td {
            vertical-align: top;
            padding-bottom: 10px;
        }

        .address-section {
            margin-bottom: 20px;
        }

        .header-table {
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <h2>INVOICE {{ $order->invoice->number }}</h2>

    <table class="header-table info-table">
        <tbody>
            <tr>
                <td style="width: 50%;">
                    <strong>Buyer:</strong> {{ $order->user->name ?? '-' }}<br>
                    <strong>Order Date:</strong> {{ $order->created_at }}<br>
                    <strong>Order ID:</strong> {{ $order->id }}<br>
                    <strong>Payment Status:</strong> {{ strtoupper($order->invoice->payment->status) }}
                </td>
                <td style="width: 50%;">
                    <strong>Courier:</strong> {{ strtoupper($order->shipping->courier) }} -
                    {{ $order->shipping->courier_name }}<br>
                    {{ $order->shipping->service }}
                    {{ $order->shipping->service_name ? ' - ' . $order->shipping->service_name : '' }}
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <strong>From:</strong><br>
                    <div class="address-section">
                        <strong>JAF Parfum's</strong><br>
                        Jember, Jawa Timur
                    </div>
                </td>
                <td style="width: 50%;">
                    <strong>To:</strong><br>
                    <div class="address-section">
                        <strong>{{ $order->shipping->address['name'] }}</strong><br>
                        {{ $order->shipping->address['phone'] ?? '-' }}<br>
                        {{ $order->shipping->address['address'] ?? '-' }},
                        {{ $order->shipping->address['sub_district'] ?? '-' }},
                        {{ $order->shipping->address['district'] ?? '-' }},
                        {{ $order->shipping->address['city'] ?? '-' }},
                        {{ $order->shipping->address['province'] ?? '-' }},
                        {{ $order->shipping->address['zip_code'] ?? '-' }}
                    </div>
                </td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Product</th>
                <th class="text-right">Price</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->name }}</td>
                    <td class="text-right">@Rp($item->price)</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">@Rp($item->price * $item->quantity)</td>
                </tr>
            @endforeach
            <tr class="total-section">
                <td class="title"
                    colspan="3">Total Price</td>
                <td class="text-right amount"
                    colspan="2">@Rp($order->total_price)</td>
            </tr>
            <tr class="total-section">
                <td class="title"
                    colspan="3">Shipping Cost</td>
                <td class="text-right amount"
                    colspan="2">@Rp($order->shipping_cost)</td>
            </tr>
            <tr class="total-section">
                <td class="title"
                    colspan="3">Total Amount</td>
                <td class="text-right amount"
                    colspan="2">@Rp($order->invoice->amount)</td>
            </tr>
            <tr class="payment-method">
                <td class="title"
                    colspan="3">Payment Method</td>
                <td class="text-right"
                    colspan="2">{{ $order->invoice->payment->info['name'] }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
