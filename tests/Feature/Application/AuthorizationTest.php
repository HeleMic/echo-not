<?php

use App\Models\User;
use App\Models\Application;

test('user cannot create application when not authenticated', function () {
    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $response->assertUnauthorized();
});

test('user cannot view application when not authenticated', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this->getJson(route('applications.show', [
        'application' => $application->id,
    ]));

    $response->assertUnauthorized();
});

test('user cannot view another user\'s application', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $application = Application::factory()->withUser($user1)->create();

    $response = $this
        ->actingAs($user2, 'sanctum')
        ->getJson(route('applications.show', [
            'application' => $application->id,
        ]));

    $response->assertForbidden();
});

test('user cannot list applications when not authenticated', function () {
    $response = $this->getJson(route('applications.index'));

    $response->assertUnauthorized();
});

test('user admin can view all applications', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->withAdmin()->create();

    Application::factory()->withUser($user1)->create();
    Application::factory()->withUser($user2)->create();

    $response = $this
        ->actingAs($user3, 'sanctum')
        ->getJson(route('applications.index'));

    $response->assertOk();
    expect(count($response->json('data')))->toBe(2);
});

test('user cannot update application when not authenticated', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this->putJson(route('applications.update', ['application' => $application->id]), [
        'name' => 'Test Application updated',
        'description' => 'This is a test application (updated).',
    ]);

    $response->assertUnauthorized();
});

test('user cannot update another user\'s application', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $application = Application::factory()->withUser($user1)->create();

    $response = $this
        ->actingAs($user2, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Test Application updated',
            'description' => 'This is a test application (updated).',
        ]);

    $response->assertForbidden();
});

test('user cannot delete application when not authenticated', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this->deleteJson(route('applications.destroy', [
        'application' => $application->id,
    ]));

    $response->assertUnauthorized();
});

test('user cannot delete another user\'s application', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $application = Application::factory()->withUser($user1)->create();

    $response = $this
        ->actingAs($user2, 'sanctum')
        ->deleteJson(route('applications.destroy', [
            'application' => $application->id,
        ]));

    $response->assertForbidden();
});