@extends('emails.layout')

@section('title', 'Pesan Baru Diterima')
@section('heading', 'Pesan baru masuk')

@section('intro')
    Ticket <strong>#{{ $contactMessage->id }}</strong> baru saja diterima dari {{ $contactMessage->name }}. Mohon cek detailnya dan tanggapi sesuai prioritas.
@endsection

@section('content')
    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Detail Kontak</h2>
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 20px 0; border-collapse:collapse;">
        <tbody>
            <tr>
                <td style="padding:8px 12px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f; width:28%;">
                    Nama
                </td>
                <td style="padding:8px 12px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                    {{ $contactMessage->name }}
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
            <tr>
                <td style="padding:8px 12px; background-color:#fdf5df; border:1px solid #ead596; font-size:14px; color:#5f532f;">
                    Waktu
                </td>
                <td style="padding:8px 12px; border:1px solid #ead596; font-size:14px; color:#3a3220;">
                    {{ optional($contactMessage->created_at)->format('d-m-Y H:i') }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 12px; background-color:#f8fafc; border:1px solid #e2e8f0; font-size:14px; color:#475569;">
                    IP
                </td>
                <td style="padding:8px 12px; border:1px solid #e2e8f0; font-size:14px; color:#1f2937;">
                    {{ $contactMessage->ip ?? '-' }}
                </td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Pesan</h2>
    <div style="padding:18px; border-radius:16px; border:1px solid #e6c874; background:linear-gradient(135deg, rgba(212,175,55,0.12), rgba(243,214,119,0.12)); font-size:15px; color:#3a3220;">
        {!! nl2br(e($contactMessage->message)) !!}
    </div>

    <table role="presentation"
           cellspacing="0"
           cellpadding="0"
           style="margin:28px 0 0 0;">
        <tr>
            <td>
                <a href="{{ route('admin.messages.show', $contactMessage->id) }}"
                   style="display:inline-block; padding:14px 22px; border-radius:14px; background:linear-gradient(135deg, #d4af37, #f3d677); color:#1f1a13; font-weight:600; text-decoration:none;">
                    Lihat di Admin
                </a>
            </td>
        </tr>
    </table>
@endsection
