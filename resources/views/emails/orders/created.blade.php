<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1"
          name="viewport">
    <title>Pesanan Berhasil Dibuat</title>
</head>

<body style="margin:0; padding:0; background:#f6f6f6; font-family: Arial, Helvetica, sans-serif; color:#111;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; padding:20px;">
        <h1 style="font-size:20px; margin:0 0 12px;">Pesanan Berhasil Dibuat</h1>
        <p style="margin:0 0 12px;">Terima kasih, pesanan Anda telah berhasil dibuat. Berikut ringkasan pesanan Anda.</p>
        <p style="margin:0 0 16px;"><strong>Nomor Invoice:</strong> {{ $invoice->number }}</p>

        @php
            $rupiah = fn(int $v) => 'Rp ' . number_format($v, 0, ',', '.');
        @endphp

        <h2 style="font-size:16px; margin:16px 0 8px;">Detail Produk</h2>
        <table border="0"
               cellpadding="0"
               cellspacing="0"
               role="presentation"
               style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th style="text-align:left; padding:8px; border-bottom:1px solid #ddd; font-size:13px;">Produk</th>
                    <th style="text-align:right; padding:8px; border-bottom:1px solid #ddd; font-size:13px;">Harga</th>
                    <th style="text-align:right; padding:8px; border-bottom:1px solid #ddd; font-size:13px;">Qty</th>
                    <th style="text-align:right; padding:8px; border-bottom:1px solid #ddd; font-size:13px;">Subtotal
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td style="padding:8px; border-bottom:1px solid #eee; font-size:13px;">{{ $item->name }}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee; text-align:right; font-size:13px;">
                            {{ $rupiah((int) $item->price_after_discount) }}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee; text-align:right; font-size:13px;">
                            {{ (int) $item->quantity }}</td>
                        <td style="padding:8px; border-bottom:1px solid #eee; text-align:right; font-size:13px;">
                            {{ $rupiah((int) $item->price_after_discount * (int) $item->quantity) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2 style="font-size:16px; margin:16px 0 8px;">Ringkasan Pembayaran</h2>
        <table border="0"
               cellpadding="0"
               cellspacing="0"
               role="presentation"
               style="width:100%; border-collapse:collapse;">
            <tbody>
                <tr>
                    <td style="padding:6px 8px; font-size:13px;">Subtotal Produk</td>
                    <td style="padding:6px 8px; font-size:13px; text-align:right;">
                        {{ $rupiah((int) $order->total_price) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 8px; font-size:13px;">Diskon @if ($order->discount_name)
                            ({{ $order->discount_name }})
                        @endif
                    </td>
                    <td style="padding:6px 8px; font-size:13px; text-align:right;">{{ $rupiah((int) $order->discount) }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:6px 8px; font-size:13px;">Pajak</td>
                    <td style="padding:6px 8px; font-size:13px; text-align:right;">
                        {{ $rupiah((int) $order->tax_amount) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 8px; font-size:13px;">Ongkir</td>
                    <td style="padding:6px 8px; font-size:13px; text-align:right;">
                        {{ $rupiah((int) $order->shipping_cost) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px; font-size:14px; font-weight:bold; border-top:1px solid #ddd;">Total Bayar
                    </td>
                    <td
                        style="padding:8px; font-size:14px; font-weight:bold; text-align:right; border-top:1px solid #ddd;">
                        {{ $rupiah((int) $totalAmount) }}</td>
                </tr>
            </tbody>
        </table>

        <h2 style="font-size:16px; margin:16px 0 8px;">Pembayaran Ke</h2>
        <table border="0"
               cellpadding="0"
               cellspacing="0"
               role="presentation"
               style="width:100%; border-collapse:collapse;">
            <tbody>
                <tr>
                    <td style="padding:6px 8px; font-size:13px;">Metode</td>
                    <td style="padding:6px 8px; font-size:13px; text-align:right;">
                        {{ ucfirst($payment->method ?? '') }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 8px; font-size:13px;">Nama</td>
                    <td style="padding:6px 8px; font-size:13px; text-align:right;">{{ $paymentInfo['name'] ?? '-' }}
                    </td>
                </tr>
                @if (isset($paymentInfo['code']))
                    <tr>
                        <td style="padding:6px 8px; font-size:13px;">Kode Bank</td>
                        <td style="padding:6px 8px; font-size:13px; text-align:right;">{{ $paymentInfo['code'] }}</td>
                    </tr>
                @endif
                @if (isset($paymentInfo['account_name']))
                    <tr>
                        <td style="padding:6px 8px; font-size:13px;">Nama Akun</td>
                        <td style="padding:6px 8px; font-size:13px; text-align:right;">
                            {{ $paymentInfo['account_name'] }}</td>
                    </tr>
                @endif
                @if (isset($paymentInfo['account_number']))
                    <tr>
                        <td style="padding:6px 8px; font-size:13px;">Nomor Rekening</td>
                        <td style="padding:6px 8px; font-size:13px; text-align:right;">
                            {{ $paymentInfo['account_number'] }}</td>
                    </tr>
                @endif
                @if (isset($paymentInfo['account_username']))
                    <tr>
                        <td style="padding:6px 8px; font-size:13px;">Username Akun</td>
                        <td style="padding:6px 8px; font-size:13px; text-align:right;">
                            {{ $paymentInfo['account_username'] }}</td>
                    </tr>
                @endif
                @if (isset($paymentInfo['phone']))
                    <tr>
                        <td style="padding:6px 8px; font-size:13px;">Nomor HP</td>
                        <td style="padding:6px 8px; font-size:13px; text-align:right;">{{ $paymentInfo['phone'] }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <h2 style="font-size:16px; margin:16px 0 8px;">Batas Waktu Pembayaran</h2>
        <p style="margin:0 0 16px;">Silakan lakukan pembayaran sebelum:
            <strong>{{ $dueDate->format('d-m-Y H:i') }}</strong>.
        </p>

        <p style="margin:0 0 16px;">Jika Anda sudah melakukan pembayaran, abaikan pemberitahuan ini.</p>

        <p style="margin-top:24px;">Thanks,<br>{!! config('app.name') !!}</p>
    </div>
</body>

</html>
