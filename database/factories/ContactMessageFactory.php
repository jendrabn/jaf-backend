<?php

namespace Database\Factories;

use App\Models\ContactMessage;
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
        $statuses = ['new', 'in_progress', 'resolved', 'spam'];

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->safeEmail(),
            'phone' => $this->faker->optional()->numerify('08##########'),
            'message' => $this->faker->paragraphs(2, true),
            'status' => $this->faker->randomElement($statuses),
            'handled_by' => null,
            'handled_at' => null,
            'notes' => $this->faker->optional()->sentence(),
            'ip' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
