<x-mail::message>
    # Selamat Datang di Newsletter Kami!

    Terima kasih telah berlangganan newsletter kami! Kami senang dapat memberikan Anda informasi terbaru, promosi, dan
    konten eksklusif.

    Anda akan menerima email berkala dengan:
    - Update dan berita terbaru
    - Promosi dan diskon khusus
    - Konten eksklusif untuk subscriber
    - Akses awal ke fitur baru

    <x-mail::button :url="route('unsubscribe', $subscriber->token)">
        Berhenti Berlangganan
    </x-mail::button>

    Jika Anda tidak berlangganan newsletter kami, silakan klik tombol berhenti berlangganan di atas.

    Terima kasih,<br>
    Tim {{ config('app.name') }}
</x-mail::message>
