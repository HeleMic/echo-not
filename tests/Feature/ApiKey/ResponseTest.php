<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| API Key Response Tests
|--------------------------------------------------------------------------
|
| These tests verify the structure and content of API responses.
| Includes pagination structure, field presence, and data format tests.
|
*/

describe('Store Response', function () {
    test('store returns correct JSON structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKeyName = 'response-test-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => $apiKeyName,
                'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

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

        // Verify correct data returned
        expect($response->json('data.name'))->toBe($apiKeyName);
        expect($response->json('data.application_id'))->toBe($application->id);
        expect($response->json('data.revoked_at'))->toBeNull();
        expect($response->json('data.last_used_at'))->toBeNull();
    });

    test('store returns plain key only on creation', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'key-visibility-test-' . fake()->unique()->word(),
            ]);

        $response->assertCreated();

        $returnedKey = $response->json('data.key');

        // Key should be visible (not placeholder) on creation
        expect($returnedKey)->not->toBe(config('api-keys.hidden_placeholder'));
        expect($returnedKey)->not->toBeNull();
    });

    test('store returns HTTP 201 Created status', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'status-test-' . fake()->unique()->word(),
            ]);

        expect($response->status())->toBe(201);
    });
});

describe('Show Response', function () {
    test('show returns correct JSON structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
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

    test('show hides key with placeholder', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertOk();

        // Key should be hidden when viewing
        expect($response->json('data.key'))->toBe(config('api-keys.hidden_placeholder'));
    });

    test('show returns correct data for specific api key', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertOk();

        expect($response->json('data.id'))->toBe($apiKey->id);
        expect($response->json('data.name'))->toBe($apiKey->name);
        expect($response->json('data.application_id'))->toBe($application->id);
    });
});

describe('Index Response', function () {
    test('index returns paginated structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $totalItems = 20;

        ApiKey::factory()->withApplication($application)->count($totalItems)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index'));

        $response->assertOk();

        expect($response->json())->toHavePaginatedStructure(
            min($totalItems, config('constants.pagination.elements_for_page')),
        );
    });

    test('index returns empty data array when no api keys', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index'));

        $response->assertOk();

        expect($response->json('data'))->toBeArray()->toBeEmpty();
        expect($response->json('meta.total'))->toBe(0);
    });

    test('index hides keys with placeholder for all items', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        ApiKey::factory()->withApplication($application)->count(5)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index'));

        $response->assertOk();

        $items = $response->json('data');
        $placeholder = config('api-keys.hidden_placeholder');

        foreach ($items as $item) {
            expect($item['key'])->toBe($placeholder);
        }
    });

    test('index pagination respects page parameter', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $perPage = config('constants.pagination.elements_for_page');
        $totalItems = $perPage + 5; // More than one page

        ApiKey::factory()->withApplication($application)->count($totalItems)->create();

        $page1 = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index', ['page' => 1]));

        $page2 = $this
            ->actingAs($user)
            ->getJson(route('api-keys.index', ['page' => 2]));

        $page1->assertOk();
        $page2->assertOk();

        // Page 1 should have full page
        expect($page1->json('data'))->toHaveCount($perPage);

        // Page 2 should have remaining items
        expect($page2->json('data'))->toHaveCount($totalItems - $perPage);

        // IDs should be different between pages
        $page1Ids = collect($page1->json('data'))->pluck('id')->toArray();
        $page2Ids = collect($page2->json('data'))->pluck('id')->toArray();

        expect(array_intersect($page1Ids, $page2Ids))->toBeEmpty();
    });
});

describe('Update Response', function () {
    test('update returns correct JSON structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $newName = 'updated-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => $newName,
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

        expect($response->json('data.name'))->toBe($newName);
    });

    test('update hides key with placeholder', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => 'new-name-' . fake()->unique()->word(),
            ]);

        $response->assertOk();

        expect($response->json('data.key'))->toBe(config('api-keys.hidden_placeholder'));
    });

    test('update returns HTTP 200 OK status', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => 'status-test-' . fake()->unique()->word(),
            ]);

        expect($response->status())->toBe(200);
    });
});

describe('Delete Response', function () {
    test('delete returns HTTP 204 No Content', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('api-keys.destroy', ['api_key' => $apiKey->id]));

        $response->assertNoContent();
        expect($response->status())->toBe(204);
        expect($response->content())->toBeEmpty();
    });
});

describe('Revoke Response', function () {
    test('revoke returns correct JSON structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

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

    test('revoke response includes revoked_at timestamp', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $response->assertOk();

        // revoked_at should now have a value
        expect($response->json('data.revoked_at'))->not->toBeNull();
    });

    test('revoke hides key with placeholder', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $response->assertOk();

        expect($response->json('data.key'))->toBe(config('api-keys.hidden_placeholder'));
    });

    test('revoke returns HTTP 200 OK status', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        expect($response->status())->toBe(200);
    });
});

describe('Error Response Formats', function () {
    test('not found returns 404 with proper structure', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->getJson(route('api-keys.show', ['api_key' => $nonExistentId]));

        $response->assertNotFound();
        expect($response->status())->toBe(404);
    });

    test('forbidden returns 403 with proper structure', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($attacker)
            ->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertForbidden();
        expect($response->status())->toBe(403);
    });

    test('unauthorized returns 401 with proper structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this->getJson(route('api-keys.show', ['api_key' => $apiKey->id]));

        $response->assertUnauthorized();
        expect($response->status())->toBe(401);
    });

    test('validation error returns 422 with proper structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => '', // Invalid
            ]);

        $response->assertUnprocessable();
        expect($response->status())->toBe(422);

        expect($response->json())->toHaveKeys(['message', 'errors']);
    });
});
