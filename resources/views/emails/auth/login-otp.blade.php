@extends('emails.layout')

@section('title', 'Verifikasi Login')
@section('heading', 'Verifikasi Login')

@section('intro')
    Masukkan kode sekali pakai berikut untuk melanjutkan proses masuk. Demi keamanan, jangan bagikan kode ini kepada siapa pun.
@endsection

@section('content')
    <table role="presentation"
           width="100%"
           cellspacing="0"
           cellpadding="0"
           style="margin:0 0 24px 0;">
        <tr>
            <td align="center"
                style="padding:24px; border:1px dashed #e8c86a; border-radius:18px; background-color:#fdf5df;">
                <div style="display:inline-block; padding:12px 18px; border-radius:14px; background-color:#d4af37; color:#1f1a13; font-size:32px; letter-spacing:8px; font-family:'Fira Code', 'Roboto Mono', monospace; font-weight:700;">
                    {{ $code }}
                </div>
                <div style="margin-top:16px; font-size:14px; color:#5b4610;">
                    Kode berlaku sampai
                    {{ $expiresAt->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d-m-Y H:i') }}.
                </div>
            </td>
        </tr>
    </table>

    <p style="margin:0 0 12px 0; color:#4f452c;">
        Jika Anda tidak meminta proses login ini, abaikan email ini. Akun Anda tetap aman selama kode tidak digunakan.
    </p>
@endsection
