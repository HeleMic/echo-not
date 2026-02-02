<?php

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

test('user cannot create api key when not authenticated', function () {
    // Read max duration from config
    $apiKeyMaxDuration = (string) config('api-keys.max_duration');
    $fakeRangeMin = '+10 seconds';
    $fakeRangeMax = "+$apiKeyMaxDuration seconds";

    $response = $this->postJson(route('api-keys.store'), [
        'application_id' => 1,
        'name' => fake()->unique()->word(),
        'expires_at' => fake()->datetimeBetween($fakeRangeMin, $fakeRangeMax, 'UTC')->format('Y-m-d H:i:s'),
    ]);

    $response->assertUnauthorized();
});

test('user cannot view api key when not authenticated', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this->getJson(route('api-keys.show', [
        'api_key' => $apiKey->id,
    ]));

    $response->assertUnauthorized();
});

test('user cannot view another user\'s application api key', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $application = Application::factory()->withUser($user1)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user2, 'sanctum')
        ->getJson(route('api-keys.show', [
            'api_key' => $apiKey->id,
        ]));

    $response->assertForbidden();
});

test('user cannot list api keys when not authenticated', function () {
    $response = $this->getJson(route('api-keys.index'));

    $response->assertUnauthorized();
});

test('user admin can view all api keys', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->withAdmin()->create();

    $application1 = Application::factory()->withUser($user1)->create();
    $application2 = Application::factory()->withUser($user2)->create();

    ApiKey::factory()->withApplication($application1)->create();
    ApiKey::factory()->withApplication($application2)->create();

    $response = $this
        ->actingAs($user3, 'sanctum')
        ->getJson(route('api-keys.index'));

    $response->assertOk();
    expect(count($response->json('data')))->toBe(2);
});

test('user cannot update api key when not authenticated', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
        'name' => fake()->unique()->word(),
    ]);

    $response->assertUnauthorized();
});

test('user cannot update another user\'s application api key', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $application = Application::factory()->withUser($user1)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user2, 'sanctum')
        ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
            'name' => fake()->unique()->word(),
        ]);

    $response->assertForbidden();
});

test('user cannot delete api key when not authenticated', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this->deleteJson(route('api-keys.destroy', [
        'api_key' => $apiKey->id,
    ]));

    $response->assertUnauthorized();
});

test('user cannot delete another user\'s application api key', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $application = Application::factory()->withUser($user1)->create();
    $apiKey = ApiKey::factory()->withApplication($application)->create();

    $response = $this
        ->actingAs($user2, 'sanctum')
        ->deleteJson(route('api-keys.destroy', [
            'api_key' => $apiKey->id,
        ]));

    $response->assertForbidden();
});
