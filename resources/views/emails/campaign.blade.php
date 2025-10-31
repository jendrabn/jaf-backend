@extends('emails.layout')

@section('title', $subject ?? 'Kampanye')
@section('heading', $subject ?? 'Kampanye')

@section('intro')
    Konten terbaru kami khusus untuk Anda. Semoga menginspirasi dan membantu aktivitas Anda hari ini.
@endsection

@section('content')
    {!! $html !!}
@endsection

@section('footer')
    <p style="margin:0 0 6px 0;">
        Jika Anda tidak ingin menerima email seperti ini lagi,
        <a href="{{ route('unsubscribe', $subscriber->token) }}"
           style="color:#d4af37; text-decoration:none; font-weight:600;">berhenti berlangganan</a>.
    </p>

    @if (!empty($receiptId))
        <p style="margin:8px 0 0 0;">
            Buka versi web:
            <a href="{{ route('newsletter.webview', ['receipt' => $receiptId, 'token' => $subscriber->token]) }}"
               style="color:#d4af37; text-decoration:none; font-weight:600;">lihat di browser</a>.
        </p>
    @endif
@endsection

@if (!empty($receiptId))
    @section('after')
        <img alt=""
             height="1"
             src="{{ route('newsletter.track.open', ['receipt' => $receiptId, 'token' => $subscriber->token]) }}"
             style="display:block; width:1px; height:1px; border:0; margin:0; padding:0;"
             width="1">
    @endsection
@endif
