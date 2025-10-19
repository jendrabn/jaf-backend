<x-mail::message>
    # Terima kasih atas pesan Anda

    Halo {{ $message->name }},

    Pesan Anda sudah kami terima. Nomor tiket: #{{ $message->id }}.

    Jam operasional: Senin–Jumat, 09:00–17:00 WIB. Kami akan membalas secepatnya.

    Ringkasan:
    - Tanggal: {{ optional($message->created_at)->format('d-m-Y H:i') }}
    - Email: {{ $message->email }}
    - Telepon: {{ $message->phone ?? '-' }}

    Pesan:
    {{ $message->message }}

    Terima kasih,<br>
    {{ config('app.name') }}
</x-mail::message>
