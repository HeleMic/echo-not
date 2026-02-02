<?php

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

test('user cannot create api key with empty name', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => '',
            'expires_at' => fake()->datetimeBetween('+1 days', '+30 days', 'UTC')->format('Y-m-d H:i:s'),
        ]);

    $response->assertUnprocessable();
});

test('user cannot create api key with name too long', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => fake()->text(500),
            'expires_at' => fake()->datetimeBetween('+1 days', '+30 days', 'UTC')->format('Y-m-d H:i:s'),
        ]);

    $response->assertUnprocessable();
});

test('user cannot create two api keys with the same name', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $this->actingAs($user, 'sanctum');

    $firstResponse = $this->postJson(route('api-keys.store'), [
        'application_id' => $application->id,
        'name' => 'Test Api Key',
        'expires_at' => fake()->datetimeBetween('+1 days', '+30 days', 'UTC')->format('Y-m-d H:i:s'),
    ]);

    $secondResponse = $this->postJson(route('api-keys.store'), [
        'application_id' => $application->id,
        'name' => 'Test Api Key',
        'expires_at' => fake()->datetimeBetween('+1 days', '+30 days', 'UTC')->format('Y-m-d H:i:s'),
    ]);

    $firstResponse->assertCreated();
    $secondResponse->assertUnprocessable();
});

test('user can update api key keeping the same name', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
            'name' => $apiKey->name,
        ]);

    $response->assertOk();
});

test('user cannot update api key with empty name', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
            'name' => '',
        ]);

    $response->assertUnprocessable();
});

test('user cannot update api key with the same name of another api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKeys = ApiKey::factory()->withApplication($application)->count(2)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => $apiKeys[0]->id]), [
            'name' => $apiKeys[1]->name,
        ]);

    $response->assertUnprocessable();
});

test('user cannot create api key with invalid expiration date', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => fake()->unique()->word(),
            'expires_at' => 'invalid-date-format',
        ]);

    $response->assertUnprocessable();
});

test('user cannot create api key with too large expiration date', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    // Read max duration from config
    $apiKeyMaxDuration = (int) config('api-keys.max_duration');
    $fakeRangeMin = '+' . ($apiKeyMaxDuration + 1) . ' seconds';
    $fakeRangeMax = '+' . ($apiKeyMaxDuration * 2) . ' seconds';

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => fake()->unique()->word(),
            'expires_at' => fake()->datetimeBetween($fakeRangeMin, $fakeRangeMax, 'UTC')->format('Y-m-d H:i:s'),
        ]);

    $response->assertUnprocessable();
});
