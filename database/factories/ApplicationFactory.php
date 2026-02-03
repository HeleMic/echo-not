<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(10, true),
        ];
    }

    /**
     * Indicate that the application belongs to the given user.
     */
    public function withUser(User $user): static
    {
        return $this->state(fn(array $attributes) => [
            'user_id' => $user,
        ]);
    }
}
