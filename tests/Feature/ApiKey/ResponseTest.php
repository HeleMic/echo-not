<?php

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

test('store returns correct structure', function () {
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

    expect($response->json('data'))->toBeArray()->toHaveKeys([
        'id',
        'application_id',
        'name',
        'key',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_at',
        'updated_at',
    ]);
});

test('index returns paginated structure', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    ApiKey::factory()->withApplication($application)->count(20)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('api-keys.index'));

    $response->assertOk();

    expect($response->json())->toHavePaginatedStructure(config('constants.pagination.elements_for_page'));
});

test('show returns correct structure', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

    $response->assertOk();

    expect($response->json('data'))->toBeArray()->toHaveKeys([
        'id',
        'application_id',
        'name',
        'key',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_at',
        'updated_at',
    ]);
});

test('update returns correct structure', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
            'name' => fake()->unique()->word(),
        ]);

    $response->assertOk();

    expect($response->json('data'))->toBeArray()->toHaveKeys([
        'id',
        'application_id',
        'name',
        'key',
        'last_used_at',
        'expires_at',
        'revoked_at',
        'created_at',
        'updated_at',
    ]);
});