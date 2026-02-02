<?php

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

test('user can create their own api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    // Read max duration from config
    $apiKeyMaxDuration = (string) config('api-keys.max_duration');
    $fakeRangeMin = '+10 seconds';
    $fakeRangeMax = "+$apiKeyMaxDuration seconds";

    $response = $this
        ->actingAs($user)
        ->postJson(route('api-keys.store', [
            'application_id' => $application->id,
            'name' => fake()->unique()->word(),
            'expires_at' => fake()->datetimeBetween($fakeRangeMin, $fakeRangeMax, 'UTC')->format('Y-m-d H:i:s'),
        ]));

    $response->assertCreated();
});

test('user can view their own api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user)
        ->getJson(route('api-keys.show', [
            'api_key' => $apiKey->id,
        ]));

    $response->assertOk();
});

test('user cannot view a non-existent api key', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('api-keys.show', [
            'api_key' => fake()->uuid(),
        ]));

    $response->assertNotFound();
});

test('user can list their own api keys', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    ApiKey::factory()->withApplication($application)->count(10)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('api-keys.index'));

    $response->assertOk();
});

test('user can update their own api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
            'name' => fake()->unique()->word(),
        ]);

    $response->assertOk();
});

test('user cannot update a non-existent api key', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => fake()->uuid()]), [
            'name' => fake()->unique()->word(),
        ]);

    $response->assertNotFound();
});

test('user can delete their own api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->deleteJson(route('api-keys.destroy', [
            'api_key' => $apiKey->id,
        ]));

    $response->assertNoContent();
});

test('user cannot delete a non-existent api key', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->deleteJson(route('api-keys.destroy', [
            'api_key' => fake()->uuid(),
        ]));

    $response->assertNotFound();
});