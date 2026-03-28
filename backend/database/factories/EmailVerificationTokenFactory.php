<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Infrastructure\Persistence\Models\EmailVerificationTokenModel;

/**
 * @extends Factory<EmailVerificationTokenModel>
 */
class EmailVerificationTokenFactory extends Factory
{
    /** Links to the model. */
    protected $model = EmailVerificationTokenModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => str()->uuid(),
            'user_id' => str()->uuid(),
            'token' => str()->random(64),
            'expires_at' => now()->addHour(),
            'used_at' => null,
        ];
    }

    /**
     * Indicate that the token is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the token has been used.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now(),
        ]);
    }
}
