<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Application;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * Storage for plain keys indexed by hashed key.
     * Static so it persists across factory instances.
     *
     * @var array<string, string>
     */
    private static array $plainKeys = [];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Read max duration from config
        $apiKeyMaxDuration = (string) config('api-keys.max_duration');
        $fakeRangeMin = '+10 seconds';
        $fakeRangeMax = "+$apiKeyMaxDuration seconds";

        // Generate plain key and store it indexed by hash
        $plainKey = \App\Support\ApiKey::generate();
        $hashedKey = \App\Support\ApiKey::hash($plainKey);
        self::$plainKeys[$hashedKey] = $plainKey;

        return [
            'application_id' => Application::factory(),
            'name' => fake()->unique()->word(),
            'key_prefix' => \App\Support\ApiKey::getPrefix($plainKey),
            'key' => $hashedKey,
            'last_used_at' => null,
            'expires_at' => fake()->datetimeBetween($fakeRangeMin, $fakeRangeMax, 'UTC')->format('Y-m-d H:i:s'),
            'revoked_at' => null,
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (ApiKey $apiKey) {
            // Retrieve plain key using the hashed key as index
            $apiKey->plainKey = self::$plainKeys[$apiKey->key] ?? null;
        });
    }

    /**
     * Indicate that the api key belongs to the given application.
     */
    public function withApplication(Application $application): static
    {
        return $this->state(fn(array $attributes) => [
            'application_id' => $application,
        ]);
    }

    /**
     * Indicate that the api key is expired.
     */
    public function expired(): static
    {
        return $this->state(fn(array $attributes) => [
            'expires_at' => now()->utc()->subDay()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Indicate that the api key is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn(array $attributes) => [
            'revoked_at' => now()->utc()->subDay()->format('Y-m-d H:i:s'),
        ]);
    }
}
