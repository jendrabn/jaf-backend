@extends('emails.layout')

@section('title', 'Pesanan Berhasil Dibuat')
@section('heading', 'Pesanan Anda berhasil dibuat')

@section('intro')
    Terima kasih atas kepercayaan Anda. Pesanan dengan invoice <strong>{{ $invoice->number }}</strong> telah kami terima dan siap diproses.
@endsection

@php
    $rupiah = static fn (int $value): string => 'Rp '.number_format($value, 0, ',', '.');
    $gatewayFee = (int) ($paymentInfo['fee'] ?? ($order->gateway_fee ?? 0));
    $emailTotal = (int) $totalAmount + $gatewayFee;
    $dueDateFormatted = $dueDate?->format('d-m-Y H:i');
    $invoiceStatus = \Illuminate\Support\Str::headline($invoice->status ?? '-');
@endphp

@section('content')
    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Detail Pesanan</h2>
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 24px 0; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="padding:10px 14px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f; width:32%;">Nomor Invoice</td>
                <td style="padding:10px 14px; border:1px solid #ead596; font-size:14px; color:#3a3220; font-weight:600;">
                    {{ $invoice->number }}
                </td>
            </tr>
            <tr>
                <td style="padding:10px 14px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f;">Status Invoice</td>
                <td style="padding:10px 14px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                    {{ $invoiceStatus }}
                </td>
            </tr>
            <tr>
                <td style="padding:10px 14px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f;">Total Pembayaran</td>
                <td style="padding:10px 14px; border:1px solid #ead596; font-size:14px; color:#3a3220; font-weight:600;">
                    {{ $rupiah((int) $emailTotal) }}
                </td>
            </tr>
            @if ($dueDateFormatted)
                <tr>
                    <td style="padding:10px 14px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f;">Bayar sebelum</td>
                    <td style="padding:10px 14px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                        {{ $dueDateFormatted }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Detail Produk</h2>
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 24px 0; border-collapse:collapse; border:1px solid #ead596;">
        <thead>
            <tr style="background-color:#f9edc6;">
                <th align="left"
                    style="padding:12px; font-size:13px; letter-spacing:0.05em; font-weight:600; color:#5f532f; text-transform:uppercase;">
                    Produk
                </th>
                <th align="right"
                    style="padding:12px; font-size:13px; letter-spacing:0.05em; font-weight:600; color:#5f532f; text-transform:uppercase; width:80px;">
                    Harga
                </th>
                <th align="right"
                    style="padding:12px; font-size:13px; letter-spacing:0.05em; font-weight:600; color:#5f532f; text-transform:uppercase; width:60px;">
                    Qty
                </th>
                <th align="right"
                    style="padding:12px; font-size:13px; letter-spacing:0.05em; font-weight:600; color:#5f532f; text-transform:uppercase; width:110px;">
                    Subtotal
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td style="padding:12px; border-top:1px solid #ead596; font-size:14px; color:#3a3220;">
                        {{ $item->name }}
                    </td>
                    <td style="padding:12px; border-top:1px solid #ead596; font-size:14px; color:#3a3220; text-align:right;">
                        {{ $rupiah((int) $item->price_after_discount) }}
                    </td>
                    <td style="padding:12px; border-top:1px solid #ead596; font-size:14px; color:#3a3220; text-align:right;">
                        {{ (int) $item->quantity }}
                    </td>
                    <td style="padding:12px; border-top:1px solid #ead596; font-size:14px; color:#3a3220; text-align:right;">
                        {{ $rupiah((int) $item->price_after_discount * (int) $item->quantity) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Ringkasan Pembayaran</h2>
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 24px 0; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="padding:8px 12px; font-size:14px; color:#5f532f; width:55%;">Subtotal Produk</td>
                <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                    {{ $rupiah((int) $order->total_price) }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; font-size:14px; color:#5f532f;">
                    Diskon
                    @if ($order->discount_name)
                        ({{ $order->discount_name }})
                    @endif
                </td>
                <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                    {{ $rupiah((int) $order->discount) }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Pajak</td>
                <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                    {{ $rupiah((int) $order->tax_amount) }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Ongkir</td>
                <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                    {{ $rupiah((int) $order->shipping_cost) }}
                </td>
            </tr>
            @if ($gatewayFee > 0)
                <tr>
                    <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Biaya Payment Gateway</td>
                    <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                        {{ $rupiah((int) $gatewayFee) }}
                    </td>
                </tr>
            @endif
            <tr>
                <td style="padding:12px 12px; font-size:15px; font-weight:700; color:#2b2110; border-top:1px solid #ead596;">
                    Total yang harus dibayar
                </td>
                <td style="padding:12px 12px; font-size:15px; font-weight:700; color:#2b2110; text-align:right; border-top:1px solid #ead596;">
                    {{ $rupiah((int) $emailTotal) }}
                </td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Petunjuk Pembayaran</h2>
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 24px 0; border-collapse:collapse;">
        <tbody>
            @if (($payment->method ?? '') === 'gateway')
                <tr>
                    <td style="padding:8px 12px; font-size:14px; color:#5f532f; width:45%;">Metode</td>
                    <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                        Payment Gateway ({{ ucfirst($paymentInfo['provider'] ?? 'midtrans') }})
                    </td>
                </tr>
                @if ($gatewayFee > 0)
                    <tr>
                        <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Biaya Gateway</td>
                        <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                            {{ $rupiah((int) $gatewayFee) }}
                        </td>
                    </tr>
                @endif
                @if (!empty($paymentInfo['redirect_url']))
                    <tr>
                        <td colspan="2"
                            style="padding:20px 12px 0 12px;">
                            <a href="{{ $paymentInfo['redirect_url'] }}"
                               style="display:inline-block; padding:14px 22px; border-radius:14px; background:linear-gradient(135deg, #d4af37, #f3d677); color:#1f1a13; font-weight:600; text-decoration:none;">
                                Klik untuk bayar via {{ ucfirst($paymentInfo['provider'] ?? 'midtrans') }}
                            </a>
                        </td>
                    </tr>
                @endif
                @if (!empty($paymentInfo['client_key']))
                    <tr>
                        <td style="padding:20px 12px 8px 12px; font-size:14px; color:#5f532f;">Client Key</td>
                        <td style="padding:20px 12px 8px 12px; font-size:13px; color:#6b5a28; text-align:right;">
                            {{ $paymentInfo['client_key'] }}
                        </td>
                    </tr>
                @endif
            @else
                <tr>
                    <td style="padding:8px 12px; font-size:14px; color:#5f532f; width:45%;">Metode</td>
                    <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                        {{ ucfirst($payment->method ?? '') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Nama</td>
                    <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                        {{ $paymentInfo['name'] ?? '-' }}
                    </td>
                </tr>
                @if (isset($paymentInfo['code']))
                    <tr>
                        <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Kode Bank</td>
                        <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                            {{ $paymentInfo['code'] }}
                        </td>
                    </tr>
                @endif
                @if (isset($paymentInfo['account_name']))
                    <tr>
                        <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Nama Akun</td>
                        <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                            {{ $paymentInfo['account_name'] }}
                        </td>
                    </tr>
                @endif
                @if (isset($paymentInfo['account_number']))
                    <tr>
                        <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Nomor Rekening</td>
                        <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                            {{ $paymentInfo['account_number'] }}
                        </td>
                    </tr>
                @endif
                @if (isset($paymentInfo['account_username']))
                    <tr>
                        <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Username Akun</td>
                        <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                            {{ $paymentInfo['account_username'] }}
                        </td>
                    </tr>
                @endif
                @if (isset($paymentInfo['phone']))
                    <tr>
                        <td style="padding:8px 12px; font-size:14px; color:#5f532f;">Nomor HP</td>
                        <td style="padding:8px 12px; font-size:14px; color:#3a3220; text-align:right;">
                            {{ $paymentInfo['phone'] }}
                        </td>
                    </tr>
                @endif
            @endif
        </tbody>
    </table>

    @if ($dueDateFormatted)
        <div style="margin:0 0 16px 0; padding:18px; border-radius:16px; border:1px solid #e6c874; background:linear-gradient(135deg, rgba(243,214,119,0.14), rgba(212,175,55,0.12)); font-size:15px; color:#3a3220;">
            Lakukan pembayaran sebelum <strong>{{ $dueDateFormatted }}</strong> agar pesanan dapat segera diproses.
        </div>
    @endif

    <p style="margin:0 0 10px 0; font-size:14px; color:#5f532f;">
        Jika Anda sudah melakukan pembayaran, abaikan pemberitahuan ini. Status invoice akan diperbarui secara otomatis setelah kami menerima konfirmasi.
    </p>
    <p style="margin:0; font-size:14px; color:#5f532f;">
        Simpan email ini sebagai referensi jika Anda membutuhkan bantuan layanan pelanggan.
    </p>
@endsection

@section('footer')
    <p style="margin:0 0 6px 0;">
        Butuh bantuan? Balas email ini atau hubungi tim support kami melalui pusat bantuan.
    </p>
    <p style="margin:0;">
        Terima kasih,<br>
        {{ config('app.name') }}
    </p>
@endsection
