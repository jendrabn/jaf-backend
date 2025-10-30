<?php

namespace Tests\Feature;

use App\Mail\ContactAutoReplyMail;
use App\Mail\ContactReplyMail;
use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use App\Models\ContactReply;
use Carbon\Carbon;
use Tests\TestCase;

class ContactMailTest extends TestCase
{
    public function test_auto_reply_mail_displays_ticket_information(): void
    {
        $message = new ContactMessage([
            'name' => 'Adi Nugroho',
            'email' => 'adi@example.com',
            'phone' => '081234567890',
            'message' => "Halo tim,\nPerlu bantuan mengenai pesanan.",
        ]);
        $message->id = 101;
        $message->created_at = Carbon::create(2025, 10, 1, 9, 15, 0, 'Asia/Jakarta');

        $html = (new ContactAutoReplyMail($message))->render();

        $this->assertStringContainsString('Terima kasih atas pesan Anda', $html);
        $this->assertStringContainsString('#101', $html);
        $this->assertStringContainsString('081234567890', $html);
        $this->assertStringContainsString('Perlu bantuan mengenai pesanan.', $html);
        $this->assertStringContainsString('Senin-Jumat', $html);
    }

    public function test_new_contact_message_mail_contains_admin_link(): void
    {
        $message = new ContactMessage([
            'name' => 'Sari',
            'email' => 'sari@example.com',
            'phone' => null,
            'ip' => '127.0.0.1',
            'message' => 'Pertanyaan mengenai produk terbaru.',
        ]);
        $message->id = 202;
        $message->created_at = Carbon::create(2025, 10, 2, 14, 30, 0, 'Asia/Jakarta');

        $html = (new NewContactMessageMail($message))->render();

        $this->assertStringContainsString('Pesan baru masuk', $html);
        $this->assertStringContainsString('Pertanyaan mengenai produk terbaru.', $html);
        $this->assertStringContainsString('127.0.0.1', $html);
        $this->assertStringContainsString(route('admin.messages.show', $message->id), $html);
        $this->assertStringContainsString('Lihat di Admin', $html);
    }

    public function test_contact_reply_mail_includes_reply_body_and_summary(): void
    {
        $message = new ContactMessage([
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '082233445566',
            'message' => 'Apakah ada stok lagi?',
        ]);
        $message->id = 303;
        $message->created_at = Carbon::create(2025, 10, 3, 11, 45, 0, 'Asia/Jakarta');

        $reply = new ContactReply([
            'subject' => 'Re: Tiket 303',
            'body' => "Halo Budi,\nStok akan tersedia pekan depan.",
        ]);

        $html = (new ContactReplyMail($reply, $message))->render();

        $this->assertStringContainsString('Balasan untuk tiket #303', $html);
        $this->assertStringContainsString('Stok akan tersedia pekan depan.', $html);
        $this->assertStringContainsString('Pesan Awal Anda', $html);
        $this->assertStringContainsString('Apakah ada stok lagi?', $html);
        $this->assertStringContainsString('082233445566', $html);
    }
}
