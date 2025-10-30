<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0"
          name="viewport">
    <meta content="ie=edge"
          http-equiv="X-UA-Compatible">
    <title>Faktur</title>

    <style>
        @page {
            size: A5 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: bold;
            color: #4A4A4A;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            table-layout: fixed;
        }

        table th,
        table td {
            padding: 6px;
            font-size: 10px;
            border: 1px solid #ddd;
            word-break: break-word;
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
            font-size: 12px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 8px;
            margin-top: 8px;
        }

        .total-section .title {
            text-align: right;
            font-size: 11px;
            color: #555;
        }

        .total-section .amount {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }

        .info-table td {
            vertical-align: top;
            padding-bottom: 8px;
        }

        .address-section {
            margin-bottom: 16px;
        }

        .header-table {
            margin-bottom: 24px;
        }
    </style>
</head>

<body>
    <h2>FAKTUR {{ $order->invoice->number }}</h2>

    <table class="header-table info-table">
        <tbody>
            <tr>
                <td style="width: 50%;">
                    <strong>Pembeli:</strong> {{ $order->user->name ?? '-' }}<br>
                    <strong>Tanggal Pesanan:</strong> {{ $order->created_at->format('d-m-Y H:i') }}<br>
                    <strong>ID Pesanan:</strong> {{ $order->id }}<br>
                    <strong>Status Pembayaran:</strong> {{ strtoupper($order->invoice->payment->status) }}
                </td>
                <td style="width: 50%;">
                    <strong>Kurir:</strong> {{ strtoupper($order->shipping->courier) }} -
                    {{ $order->shipping->courier_name }}<br>
                    {{ $order->shipping->service }}
                    {{ $order->shipping->service_name ? ' - ' . $order->shipping->service_name : '' }}
                </td>
            </tr>
            <tr>
                <td style="width: 50%;">
                    <strong>Dari:</strong><br>
                    <div class="address-section">
                        <strong>JAF Parfum's</strong><br>
                        Jember, Jawa Timur
                    </div>
                </td>
                <td style="width: 50%;">
                    <strong>Kepada:</strong><br>
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
                <th>Produk</th>
                <th class="text-right">Harga</th>
                <th class="text-right">Kuantitas</th>
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
                    colspan="3">Total Harga</td>
                <td class="text-right amount"
                    colspan="2">@Rp($order->total_price)</td>
            </tr>
            @if ((int) $order->discount > 0)
                <tr class="total-section">
                    <td class="title"
                        colspan="3">
                        Diskon @if ($order->discount_name)
                            ({{ $order->discount_name }})
                        @endif
                    </td>
                    <td class="text-right amount"
                        colspan="2">-@Rp($order->discount)</td>
                </tr>
            @endif
            @if ((int) $order->tax_amount > 0)
                @php($__dpp = max(0, (int) $order->total_price - (int) $order->discount))
                <tr class="total-section">
                    <td class="title"
                        colspan="3">DPP (Dasar Pengenaan Pajak)</td>
                    <td class="text-right amount"
                        colspan="2">@Rp($__dpp)</td>
                </tr>
                <tr class="total-section">
                    <td class="title"
                        colspan="3">{{ $order->tax_name ?? 'Pajak' }}</td>
                    <td class="text-right amount"
                        colspan="2">@Rp($order->tax_amount)</td>
                </tr>
            @endif
            <tr class="total-section">
                <td class="title"
                    colspan="3">Biaya Pengiriman</td>
                <td class="text-right amount"
                    colspan="2">@Rp($order->shipping_cost)</td>
            </tr>
            @if ((int) $order->gateway_fee > 0)
                <tr class="total-section">
                    <td class="title"
                        colspan="3">Biaya Layanan Pembayaran</td>
                    <td class="text-right amount"
                        colspan="2">@Rp($order->gateway_fee)</td>
                </tr>
            @endif
            <tr class="total-section">
                <td class="title"
                    colspan="3">Total Bayar</td>
                <td class="text-right amount"
                    colspan="2">@Rp($order->invoice->amount)</td>
            </tr>
            <tr class="payment-method">
                <td class="title"
                    colspan="3">Metode Pembayaran</td>
                <td class="text-right"
                    colspan="2">
                    {{ $order->invoice->payment->info['name'] ?? ($order->invoice->payment->info['provider'] ?? 'N/A') }}
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>
