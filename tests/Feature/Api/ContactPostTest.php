<?php

namespace Tests\Feature\Api;

use App\Mail\ContactAutoReplyMail;
use App\Mail\NewContactMessageMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\ApiTestCase;

class ContactPostTest extends ApiTestCase
{
    use RefreshDatabase;

    #[Test]
    public function can_store_contact_message()
    {
        Mail::fake();
        config(['mail.support_email' => 'support@example.com']);

        RateLimiter::clear('contact:ip:127.0.0.1:email:john@gmail.com');

        $response = $this->postJson('/api/contact', [
            'name' => '<b>John Doe</b>',
            'email' => 'john@gmail.com',
            'phone' => '<script>081234567890</script>',
            'message' => '<p>This is a valid contact message for the API.</p>',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'received');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'phone' => '081234567890',
            'message' => 'This is a valid contact message for the API.',
            'status' => 'new',
        ]);

        Mail::assertQueued(NewContactMessageMail::class);
        Mail::assertQueued(ContactAutoReplyMail::class);
    }

    #[Test]
    public function returns_validation_errors_for_invalid_payload()
    {
        $response = $this->postJson('/api/contact', [
            'name' => '',
            'email' => 'invalid-email',
            'message' => 'short',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'message']);
    }

    #[Test]
    public function rate_limits_contact_message_submission()
    {
        config(['mail.support_email' => 'support@example.com']);
        RateLimiter::clear('contact:ip:127.0.0.1:email:john@gmail.com');

        $payload = [
            'name' => 'John Doe',
            'email' => 'john@gmail.com',
            'phone' => '081234567890',
            'message' => 'This is a valid contact message for the API.',
        ];

        $firstResponse = $this->postJson('/api/contact', $payload);
        $secondResponse = $this->postJson('/api/contact', $payload);

        $firstResponse->assertCreated();
        $secondResponse->assertStatus(429)
            ->assertJsonPath('message', 'Terlalu sering mengirim pesan. Coba lagi setelah 5 menit.');
    }
}
