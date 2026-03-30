<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Infrastructure\Persistence\Models\EmailChangeTokenModel;

/**
 * @extends Factory<EmailChangeTokenModel>
 */
class EmailChangeTokenFactory extends Factory
{
    /** Links to the model. */
    protected $model = EmailChangeTokenModel::class;

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
            'current_email' => fake()->safeEmail(),
            'new_email' => fake()->safeEmail(),
            'token' => str()->random(64),
            'expires_at' => now()->addHour(),
            'confirmed_at' => null,
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
     * Indicate that the token has been confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmed_at' => now(),
        ]);
    }
}
