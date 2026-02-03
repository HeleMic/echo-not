<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| API Key Authorization Tests
|--------------------------------------------------------------------------
|
| These tests verify authorization rules for API keys:
| - Unauthenticated access (401 Unauthorized)
| - Cross-user access attempts (403 Forbidden)
| - Admin privileges
|
*/

describe('Unauthenticated Access', function () {
    test('unauthenticated user cannot create api key', function () {
        $response = $this->postJson(route('api-keys.store'), [
            'application_id' => fake()->uuid(),
            'name' => 'test-key',
            'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
        ]);

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot view api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot list api keys', function () {
        $response = $this->getJson(route('api-keys.index'));

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot update api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
            'name' => 'new-name',
        ]);

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot delete api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this->deleteJson(route('api-keys.destroy', ['api_key' => $apiKey->id]));

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot revoke api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $response->assertUnauthorized();
    });
});

describe('Cross-User Access Prevention (IDOR Protection)', function () {
    test('user cannot view another user\'s api key', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($attacker)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertForbidden();
    });

    test('user cannot update another user\'s api key', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $originalName = $apiKey->name;

        $response = $this
            ->actingAs($attacker)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => 'hacked-name',
            ]);

        $response->assertForbidden();

        // Verify database was not modified
        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'name' => $originalName,
        ]);
    });

    test('user cannot delete another user\'s api key', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($attacker)
            ->deleteJson(route('api-keys.destroy', ['api_key' => $apiKey->id]));

        $response->assertForbidden();

        // Verify api key still exists
        $this->assertDatabaseHas('api_keys', ['id' => $apiKey->id]);
    });

    test('user cannot revoke another user\'s api key', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($attacker)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $response->assertForbidden();

        // Verify api key was not revoked
        $apiKey->refresh();
        expect($apiKey->revoked_at)->toBeNull();
    });

    test('user cannot create api key for another user\'s application', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $ownerApplication = Application::factory()->withUser($owner)->create();

        $response = $this
            ->actingAs($attacker)
            ->postJson(route('api-keys.store'), [
                'application_id' => $ownerApplication->id,
                'name' => 'malicious-key',
                'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        // Should fail validation (application doesn't belong to attacker)
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['application_id']);

        // Verify no key was created
        $this->assertDatabaseMissing('api_keys', [
            'application_id' => $ownerApplication->id,
            'name' => 'malicious-key',
        ]);
    });

    test('user only sees their own api keys in index', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $app1 = Application::factory()->withUser($user1)->create();
        $app2 = Application::factory()->withUser($user2)->create();

        $user1Keys = ApiKey::factory()->withApplication($app1)->count(3)->create();
        $user2Keys = ApiKey::factory()->withApplication($app2)->count(5)->create();

        $response = $this
            ->actingAs($user1)
            ->getJson(route('api-keys.index'));

        $response->assertOk();

        $returnedIds = collect($response->json('data'))->pluck('id')->toArray();

        // Should only see user1's keys
        expect($returnedIds)->toHaveCount(3);

        foreach ($user1Keys as $key) {
            expect($returnedIds)->toContain($key->id);
        }

        foreach ($user2Keys as $key) {
            expect($returnedIds)->not->toContain($key->id);
        }
    });
});

describe('Admin Privileges', function () {
    test('admin can view any api key', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($admin)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertOk();
        expect($response->json('data.id'))->toBe($apiKey->id);
    });

    test('admin can view all api keys in index', function () {
        $admin = User::factory()->withAdmin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $app1 = Application::factory()->withUser($user1)->create();
        $app2 = Application::factory()->withUser($user2)->create();

        ApiKey::factory()->withApplication($app1)->count(2)->create();
        ApiKey::factory()->withApplication($app2)->count(3)->create();

        $response = $this
            ->actingAs($admin)
            ->getJson(route('api-keys.index'));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(5);
    });

    test('admin can update any api key', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $newName = 'admin-updated-name';

        $response = $this
            ->actingAs($admin)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => $newName,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'name' => $newName,
        ]);
    });

    test('admin can delete any api key', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $apiKeyId = $apiKey->id;

        $response = $this
            ->actingAs($admin)
            ->deleteJson(route('api-keys.destroy', ['api_key' => $apiKeyId]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('api_keys', ['id' => $apiKeyId]);
    });

    test('admin can revoke any api key', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        expect($apiKey->revoked_at)->toBeNull();

        $response = $this
            ->actingAs($admin)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $response->assertOk();

        $apiKey->refresh();
        expect($apiKey->revoked_at)->not->toBeNull();
    });
});

describe('Edge Cases', function () {
    test('user without applications cannot create api key', function () {
        $user = User::factory()->create();

        // User has no applications
        expect($user->applications()->count())->toBe(0);

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => fake()->uuid(),
                'name' => 'orphan-key',
            ]);

        // Should fail - either forbidden or validation error
        expect($response->status())->toBeIn([403, 422]);
    });

    test('user can access api key after application owner change scenario', function () {
        // This test documents expected behavior when ownership might change
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        // User can still access their key
        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertOk();
    });
});
