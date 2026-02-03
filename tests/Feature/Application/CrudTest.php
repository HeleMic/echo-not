<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| Application CRUD Tests
|--------------------------------------------------------------------------
|
| These tests verify the core CRUD operations for Applications.
| Each test validates both the HTTP response AND the database state.
|
*/

describe('Create Application', function () {
    test('user can create application', function () {
        $user = User::factory()->create();
        $applicationName = 'test-app-' . fake()->unique()->word();
        $description = fake()->sentence(10);

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => $applicationName,
                'description' => $description,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'name' => $applicationName,
            'description' => $description,
        ]);
    });

    test('user can create application without description', function () {
        $user = User::factory()->create();
        $applicationName = 'no-desc-app-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => $applicationName,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'name' => $applicationName,
        ]);
    });

    test('user can create multiple applications', function () {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response1 = $this->postJson(route('applications.store'), [
            'name' => 'first-app-' . fake()->unique()->word(),
        ]);

        $response2 = $this->postJson(route('applications.store'), [
            'name' => 'second-app-' . fake()->unique()->word(),
        ]);

        $response1->assertCreated();
        $response2->assertCreated();

        expect(Application::where('user_id', $user->id)->count())->toBe(2);
    });

    test('created application belongs to authenticated user', function () {
        $user = User::factory()->create();
        $applicationName = 'owned-app-' . fake()->unique()->word();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => $applicationName,
            ]);

        $response->assertCreated();

        $applicationId = $response->json('data.id');
        $application = Application::find($applicationId);

        expect($application->user_id)->toBe($user->id);
        expect($application->user->id)->toBe($user->id);
    });
});

describe('Read Application', function () {
    test('user can view their own application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertOk();

        expect($response->json('data.id'))->toBe($application->id);
        expect($response->json('data.name'))->toBe($application->name);
    });

    test('user cannot view a non-existent application', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.show', ['application' => $nonExistentId]));

        $response->assertNotFound();
    });

    test('user can list their own applications', function () {
        $user = User::factory()->create();
        $applicationsCount = 5;

        Application::factory()->withUser($user)->count($applicationsCount)->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.index'));

        $response->assertOk();

        expect($response->json('data'))->toHaveCount($applicationsCount);
    });

    test('user sees empty list when they have no applications', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->getJson(route('applications.index'));

        $response->assertOk();

        expect($response->json('data'))->toBeEmpty();
    });
});

describe('Update Application', function () {
    test('user can update their own application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $newName = 'updated-name-' . fake()->unique()->word();
        $newDescription = 'Updated description';

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $newName,
                'description' => $newDescription,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'name' => $newName,
            'description' => $newDescription,
        ]);

        expect($response->json('data.name'))->toBe($newName);
        expect($response->json('data.description'))->toBe($newDescription);
    });

    test('user cannot update a non-existent application', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $nonExistentId]), [
                'name' => 'some-name',
            ]);

        $response->assertNotFound();
    });

    test('update preserves user ownership', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => 'new-name-' . fake()->unique()->word(),
                'description' => 'New description',
            ]);

        $response->assertOk();

        $application->refresh();
        expect($application->user_id)->toBe($user->id);
    });
});

describe('Delete Application', function () {
    test('user can delete their own application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $applicationId = $application->id;

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('applications.destroy', ['application' => $applicationId]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('applications', [
            'id' => $applicationId,
        ]);
    });

    test('user cannot delete a non-existent application', function () {
        $user = User::factory()->create();
        $nonExistentId = fake()->uuid();

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('applications.destroy', ['application' => $nonExistentId]));

        $response->assertNotFound();
    });

    test('deleting application does not affect other applications', function () {
        $user = User::factory()->create();
        $appToDelete = Application::factory()->withUser($user)->create();
        $appToKeep = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('applications.destroy', ['application' => $appToDelete->id]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('applications', ['id' => $appToDelete->id]);
        $this->assertDatabaseHas('applications', ['id' => $appToKeep->id]);
    });

    test('deleting application cascades to api keys', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $applicationId = $application->id;
        $apiKeyId = $apiKey->id;

        $response = $this
            ->actingAs($user)
            ->deleteJson(route('applications.destroy', ['application' => $applicationId]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('applications', ['id' => $applicationId]);
        $this->assertDatabaseMissing('api_keys', ['id' => $apiKeyId]);
    });
});
