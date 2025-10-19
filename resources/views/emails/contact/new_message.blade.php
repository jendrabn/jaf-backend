<x-mail::message>
    # Pesan Baru Diterima

    Ticket #{{ $message->id }}

    - Nama: {{ $message->name }}
    - Email: {{ $message->email }}
    - Telepon: {{ $message->phone ?? '-' }}
    - Waktu: {{ optional($message->created_at)->format('d-m-Y H:i') }}
    - IP: {{ $message->ip ?? '-' }}

    Pesan:
    {{ $message->message }}

    <x-mail::button :url="route('admin.messages.show', $message->id)">
        Lihat di Admin
    </x-mail::button>

    Terima kasih,<br>
    {{ config('app.name') }}
</x-mail::message>
