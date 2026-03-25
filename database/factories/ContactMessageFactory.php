<?php

namespace Database\Factories;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    protected $model = ContactMessage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $contactMessage = FactoryData::contactMessage();
        $handledBy = in_array($contactMessage['status'], ['in_progress', 'resolved'], true)
            ? User::factory()
            : null;
        $handledAt = in_array($contactMessage['status'], ['in_progress', 'resolved'], true)
            ? fake()->dateTimeBetween('-5 days', 'now')
            : null;

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->optional(0.8)->numerify('08##########'),
            'message' => $contactMessage['message'],
            'status' => $contactMessage['status'],
            'handled_by' => $handledBy,
            'handled_at' => $handledAt,
            'notes' => $contactMessage['notes'],
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
        ];
    }
}
