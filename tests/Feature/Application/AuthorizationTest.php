<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| Application Authorization Tests
|--------------------------------------------------------------------------
|
| These tests verify authorization rules for Applications:
| - Unauthenticated access (401 Unauthorized)
| - Cross-user access attempts (403 Forbidden)
| - Admin privileges
|
*/

describe('Unauthenticated Access', function () {
    test('unauthenticated user cannot create application', function () {
        $response = $this->postJson(route('applications.store'), [
            'name' => 'Test Application',
            'description' => 'Test description',
        ]);

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot view application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot list applications', function () {
        $response = $this->getJson(route('applications.index'));

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot update application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Updated name',
        ]);

        $response->assertUnauthorized();
    });

    test('unauthenticated user cannot delete application', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this->deleteJson(route('applications.destroy', ['application' => $application->id]));

        $response->assertUnauthorized();
    });
});

describe('Cross-User Access Prevention (IDOR Protection)', function () {
    test('user cannot view another user\'s application', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();

        $response = $this
            ->actingAs($attacker)
            ->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertForbidden();
    });

    test('user cannot update another user\'s application', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();

        $originalName = $application->name;

        $response = $this
            ->actingAs($attacker)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => 'hacked-name',
            ]);

        $response->assertForbidden();

        // Verify database was not modified
        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'name' => $originalName,
        ]);
    });

    test('user cannot delete another user\'s application', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $application = Application::factory()->withUser($owner)->create();

        $response = $this
            ->actingAs($attacker)
            ->deleteJson(route('applications.destroy', ['application' => $application->id]));

        $response->assertForbidden();

        // Verify application still exists
        $this->assertDatabaseHas('applications', ['id' => $application->id]);
    });

    test('user only sees their own applications in index', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $user1Apps = Application::factory()->withUser($user1)->count(3)->create();
        $user2Apps = Application::factory()->withUser($user2)->count(5)->create();

        $response = $this
            ->actingAs($user1)
            ->getJson(route('applications.index'));

        $response->assertOk();

        $returnedIds = collect($response->json('data'))->pluck('id')->toArray();

        // Should only see user1's applications
        expect($returnedIds)->toHaveCount(3);

        foreach ($user1Apps as $app) {
            expect($returnedIds)->toContain($app->id);
        }

        foreach ($user2Apps as $app) {
            expect($returnedIds)->not->toContain($app->id);
        }
    });
});

/*
|--------------------------------------------------------------------------
| Admin Privileges Tests
|--------------------------------------------------------------------------
|
| These tests verify that administrators have full access to all applications,
| regardless of ownership. This mirrors the behavior in ApiKeyPolicy.
|
*/

describe('Admin Privileges', function () {
    test('admin can view any application', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($admin)
            ->getJson(route('applications.show', ['application' => $application->id]));

        $response->assertOk();
        expect($response->json('data.id'))->toBe($application->id);
    });

    test('admin can view all applications in index', function () {
        $admin = User::factory()->withAdmin()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Application::factory()->withUser($user1)->count(2)->create();
        Application::factory()->withUser($user2)->count(3)->create();

        $response = $this
            ->actingAs($admin)
            ->getJson(route('applications.index'));

        $response->assertOk();
        expect($response->json('data'))->toHaveCount(5);
    });

    test('admin can update any application', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $newName = 'admin-updated-name';

        $response = $this
            ->actingAs($admin)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $newName,
                'description' => 'Updated by admin',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'name' => $newName,
        ]);
    });

    test('admin can delete any application', function () {
        $admin = User::factory()->withAdmin()->create();
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $applicationId = $application->id;

        $response = $this
            ->actingAs($admin)
            ->deleteJson(route('applications.destroy', ['application' => $applicationId]));

        $response->assertNoContent();

        $this->assertDatabaseMissing('applications', ['id' => $applicationId]);
    });
});
