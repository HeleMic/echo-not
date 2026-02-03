<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| API Key CRUD Tests
|--------------------------------------------------------------------------
|
| These tests verify the core CRUD operations for API keys.
| Each test validates both the HTTP response AND the database state.
|
*/

describe('Create API Key', function () {
    test('user can create api key for their own application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKeyName = 'test-api-key-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => $apiKeyName,
                'expires_at' => now()->addDays(30)->toDateTimeString(),
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('api_keys', [
            'application_id' => $application->id,
            'name' => $apiKeyName,
        ]);
    });

    test('user can create api key without expiration date (uses default)', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKeyName = 'no-expiry-key-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => $apiKeyName,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('api_keys', [
            'application_id' => $application->id,
            'name' => $apiKeyName,
        ]);

        // Verify default expiration was set
        $apiKey = ApiKey::where('name', $apiKeyName)->first();
        expect($apiKey->expires_at)->not->toBeNull();
    });

    test('user can create multiple api keys for same application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $this->actingAs($user);

        $response1 = $this->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => 'first-key-' . fake()->unique()->word(),
        ]);

        $response2 = $this->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => 'second-key-' . fake()->unique()->word(),
        ]);

        $response1->assertCreated();
        $response2->assertCreated();

        expect(ApiKey::where('application_id', $application->id)->count())->toBe(2);
    });
});

describe('Read API Key', function () {
    test('user can view their own api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertOk();

        expect($response->json('data.id'))->toBe($apiKey->id);
        expect($response->json('data.name'))->toBe($apiKey->name);
    });

    test('user cannot view a non-existent api key', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.show', ['api_key' => $nonExistentId]));

        $response->assertNotFound();
    });

    test('user can list their own api keys', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKeysCount = 5;

        ApiKey::factory()->withApplication($application)->count($apiKeysCount)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index'));

        $response->assertOk();

        expect($response->json('data'))->toHaveCount($apiKeysCount);
    });

    test('user sees empty list when they have no api keys', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index'));

        $response->assertOk();

        expect($response->json('data'))->toBeEmpty();
    });
});

describe('Update API Key', function () {
    test('user can update their own api key name', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $newName = 'updated-name-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => $newName,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'name' => $newName,
        ]);

        expect($response->json('data.name'))->toBe($newName);
    });

    test('user cannot update a non-existent api key', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $nonExistentId]), [
                'name' => 'some-name',
            ]);

        $response->assertNotFound();
    });

    test('update does not modify other fields', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $originalExpiresAt = $apiKey->expires_at;
        $originalApplicationId = $apiKey->application_id;

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => 'new-name-' . fake()->unique()->word(),
            ]);

        $response->assertOk();

        $apiKey->refresh();
        expect($apiKey->application_id)->toBe($originalApplicationId);
        expect($apiKey->expires_at->timestamp)->toBe($originalExpiresAt->timestamp);
    });
});

describe('Delete API Key', function () {
    test('user can delete their own api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $apiKeyId = $apiKey->id;

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api-keys.destroy', ['api_key' => $apiKeyId]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('api_keys', [
            'id' => $apiKeyId,
        ]);
    });

    test('user cannot delete a non-existent api key', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api-keys.destroy', ['api_key' => $nonExistentId]));

        $response->assertNotFound();
    });

    test('deleting api key does not affect other api keys', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKeyToDelete = ApiKey::factory()->withApplication($application)->create();
        $apiKeyToKeep = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api-keys.destroy', ['api_key' => $apiKeyToDelete->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('api_keys', ['id' => $apiKeyToDelete->id]);
        $this->assertDatabaseHas('api_keys', ['id' => $apiKeyToKeep->id]);
    });
});

describe('Revoke API Key', function () {
    test('user can revoke their own api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        expect($apiKey->revoked_at)->toBeNull();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $response->assertOk();

        $apiKey->refresh();
        expect($apiKey->revoked_at)->not->toBeNull();
    });

    test('revoking api key does not delete it', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $originalId = $apiKey->id;

        $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $this->assertDatabaseHas('api_keys', [
            'id' => $originalId,
        ]);
    });

    test('revoking already revoked api key is idempotent', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $this->actingAs($user);

        // First revoke
        $response1 = $this->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));
        $response1->assertOk();

        // Second revoke (should still work)
        $response2 = $this->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));
        $response2->assertOk();
    });

    test('user cannot revoke a non-existent api key', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $nonExistentId]));

        $response->assertNotFound();
    });
});
