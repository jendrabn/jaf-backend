@component('mail::message')
    # Selamat Datang, {{ $subscriber->name ?? $subscriber->email }}

    Terima kasih telah berlangganan newsletter kami. Kami akan mengirimkan kabar terbaru, promo, dan konten pilihan langsung
    ke email Anda.

    @component('mail::panel')
        Jika Anda tidak merasa mendaftar, abaikan email ini.
    @endcomponent

    Terima kasih,
    {{ config('app.name') }}
@endcomponent
