<x-mail::message>
    # Balasan untuk Tiket #{{ $message->id }}

    Halo {{ $message->name }},

    Berikut adalah balasan dari tim kami:

    {{-- Body Reply --}}
    {{ $reply->body }}

    Ringkasan tiket Anda:
    - Tanggal: {{ optional($message->created_at)->format('d-m-Y H:i') }}
    - Email: {{ $message->email }}
    - Telepon: {{ $message->phone ?? '-' }}

    Pesan awal Anda:
    "{{ \Illuminate\Support\Str::limit($message->message, 500) }}"

    Terima kasih,<br>
    {{ config('app.name') }}
</x-mail::message>
