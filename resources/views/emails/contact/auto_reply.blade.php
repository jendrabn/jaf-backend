@extends('emails.layout')

@section('title', 'Konfirmasi Pesan')
@section('heading', 'Terima kasih atas pesan Anda')

@section('intro')
    Halo {{ $contactMessage->name }}, pesan Anda telah kami terima dan tercatat sebagai tiket <strong>#{{ $contactMessage->id }}</strong>.
    Tim kami bertugas Senin-Jumat pukul 09.00-17.00 WIB dan akan membalas secepatnya.
@endsection

@section('content')
    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Ringkasan Kontak</h2>
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 20px 0; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="padding:8px 12px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f; width:28%;">
                    Tanggal
                </td>
                <td style="padding:8px 12px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                    {{ optional($contactMessage->created_at)->format('d-m-Y H:i') }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f;">
                    Email
                </td>
                <td style="padding:8px 12px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                    {{ $contactMessage->email }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f;">
                    Telepon
                </td>
                <td style="padding:8px 12px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                    {{ $contactMessage->phone ?? '-' }}
                </td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Pesan Anda</h2>
    <div style="padding:18px; border-radius:16px; border:1px solid #e6c874; background:linear-gradient(135deg, rgba(212,175,55,0.12), rgba(243,214,119,0.12)); font-size:15px; color:#3a3220;">
        {!! nl2br(e($contactMessage->message)) !!}
    </div>
@endsection
