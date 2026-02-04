<?php

namespace Database\Factories;

use DateTime;
use DateTimeZone;
use App\Support\ApiKey;
use App\Models\Application;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ApiKey>
 */
class ApiKeyFactory extends Factory
{
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

        $plainKey = ApiKey::generate();

        return [
            'application_id' => Application::factory(),
            'name' => fake()->unique()->word(),
            'key_prefix' => ApiKey::getPrefix($plainKey),
            'key' => ApiKey::hash($plainKey),
            'last_used_at' => null,
            'expires_at' => fake()->datetimeBetween($fakeRangeMin, $fakeRangeMax, 'UTC')->format('Y-m-d H:i:s'),
            'revoked_at' => null,
        ];
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
}
