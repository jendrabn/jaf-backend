@extends('emails.layout')

@section('title', 'Balasan Tiket')
@section('heading', 'Balasan untuk tiket #'.$contactMessage->id)

@section('intro')
    Halo {{ $contactMessage->name }}, berikut balasan terbaru dari tim kami. Silakan tinjau informasi di bawah ini dan hubungi kami kembali bila masih ada pertanyaan.
@endsection

@section('content')
    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Balasan Kami</h2>
    <div style="padding:18px; border-radius:16px; border:1px solid #e6c874; background:linear-gradient(135deg, rgba(243,214,119,0.14), rgba(212,175,55,0.12)); font-size:15px; color:#3a3220;">
        {!! $reply->body !!}
    </div>

    <h2 style="margin:28px 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Ringkasan Tiket</h2>
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

    <h2 style="margin:0 0 12px 0; font-size:17px; font-weight:700; color:#2b2110;">Pesan Awal Anda</h2>
    <div style="padding:18px; border-radius:16px; border:1px solid #ead596; background-color:#fdf5df; font-size:15px; color:#3a3220;">
        {!! nl2br(e(\Illuminate\Support\Str::limit($contactMessage->message, 800))) !!}
    </div>
@endsection
