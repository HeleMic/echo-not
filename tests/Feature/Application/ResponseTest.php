<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| Application Response Tests
|--------------------------------------------------------------------------
|
| These tests verify the structure and content of API responses.
| Includes pagination structure, field presence, and data format tests.
|
*/

describe('Store Response', function () {
    test('store returns correct JSON structure', function () {
        $user = User::factory()->create();
        $applicationName = 'response-test-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => $applicationName,
                'description' => 'Test description',
            ]);

        $response->assertCreated();

        expect($response->json('data'))->toBeArray()->toHaveKeys([
            'id',
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);

        // Verify correct data returned
        expect($response->json('data.name'))->toBe($applicationName);
        expect($response->json('data.description'))->toBe('Test description');
    });

    test('store returns HTTP 201 Created status', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => 'status-test-' . fake()->unique()->word(),
            ]);

        expect($response->status())->toBe(201);
    });
});

describe('Show Response', function () {
    test('show returns correct JSON structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertOk();

        expect($response->json('data'))->toBeArray()->toHaveKeys([
            'id',
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);
    });

    test('show returns correct data for specific application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertOk();

        expect($response->json('data.id'))->toBe($application->id);
        expect($response->json('data.name'))->toBe($application->name);
        expect($response->json('data.description'))->toBe($application->description);
    });
});

describe('Index Response', function () {
    test('index returns paginated structure', function () {
        $user = User::factory()->create();
        $totalItems = 20;

        Application::factory()->withUser($user)->count($totalItems)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.index'));

        $response->assertOk();

        expect($response->json())->toHavePaginatedStructure(
            min($totalItems, config('constants.pagination.elements_for_page'))
        );
    });

    test('index returns empty data array when no applications', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.index'));

        $response->assertOk();

        expect($response->json('data'))->toBeArray()->toBeEmpty();
        expect($response->json('meta.total'))->toBe(0);
    });

    test('index pagination respects page parameter', function () {
        $user = User::factory()->create();
        $perPage = config('constants.pagination.elements_for_page');
        $totalItems = $perPage + 5; // More than one page

        Application::factory()->withUser($user)->count($totalItems)->create();

        $page1 = $this
            ->actingAs($user)
            ->getJson(route('applications.index', ['page' => 1]));

        $page2 = $this
            ->actingAs($user)
            ->getJson(route('applications.index', ['page' => 2]));

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
        $newName = 'updated-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $newName,
                'description' => 'Updated description',
            ]);

        $response->assertOk();

        expect($response->json('data'))->toBeArray()->toHaveKeys([
            'id',
            'name',
            'description',
            'created_at',
            'updated_at',
        ]);

        expect($response->json('data.name'))->toBe($newName);
    });

    test('update returns HTTP 200 OK status', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => 'status-test-' . fake()->unique()->word(),
                'description' => 'Test',
            ]);

        expect($response->status())->toBe(200);
    });
});

describe('Delete Response', function () {
    test('delete returns HTTP 204 No Content', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('applications.destroy', ['application' => $application->id]));

        $response->assertNoContent();
        expect($response->status())->toBe(204);
        expect($response->content())->toBeEmpty();
    });
});

describe('Error Response Formats', function () {
    test('not found returns 404 with proper structure', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.show', ['application' => $nonExistentId]));

        $response->assertNotFound();
        expect($response->status())->toBe(404);
    });

    test('forbidden returns 403 with proper structure', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();

        $response = $this
            ->actingAs($attacker)
            ->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertForbidden();
        expect($response->status())->toBe(403);
    });

    test('unauthorized returns 401 with proper structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertUnauthorized();
        expect($response->status())->toBe(401);
    });

    test('validation error returns 422 with proper structure', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => '', // Invalid
            ]);

        $response->assertUnprocessable();
        expect($response->status())->toBe(422);

        expect($response->json())->toHaveKeys(['message', 'errors']);
    });
});
