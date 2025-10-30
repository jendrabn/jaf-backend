@extends('emails.layout')

@section('title', 'Selamat Datang')
@section('heading', 'Selamat datang di newsletter kami')

@section('intro')
    Hai {{ $subscriber->name ?? $subscriber->email }}, terima kasih sudah bergabung. Kami akan mengirimkan kabar terbaru, promo, dan insight pilihan langsung ke inbox Anda.
@endsection

@section('content')
    <div style="padding:22px; border-radius:18px; border:1px solid #e8c86a; background:linear-gradient(135deg, rgba(212,175,55,0.14), rgba(243,214,119,0.14)); font-size:15px; color:#3a3220;">
        Jika Anda merasa tidak pernah mendaftar, cukup abaikan email ini atau hapus saja. Anda dapat berhenti berlangganan kapan pun melalui tautan pada footer email kami.
    </div>
@endsection
