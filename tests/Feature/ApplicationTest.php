<?php

use App\Models\User;
use App\Models\Application;

test('user can create a new application', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => 'Test Application',
            'description' => 'This is a test application.',
        ]);

    $response->assertCreated();

    expect($response->json('data.name'))->toBe('Test Application');
    expect($response->json('data.description'))->toBe('This is a test application.');
});

test('user cannot create application when not authenticated', function () {
    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $response->assertUnauthorized();
});

test('user cannot create two application with the same name', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $firstResponse = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $secondResponse = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $firstResponse->assertCreated();
    $secondResponse->assertUnprocessable();

    expect($firstResponse->json('data.name'))->toBe('Test Application');
    expect($firstResponse->json('data.description'))->toBe('This is a test application.');
});

test('user can view their own application', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.show', [
            'application' => $application->id,
        ]));

    $response->assertOk();
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

test('user admin can view all application', function () {
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

test('user can update their own application', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Test Application updated',
            'description' => 'This is a test application (updated).',
        ]);

    $response->assertOk();
    expect($response->json('data.name'))->toBe('Test Application updated');
    expect($response->json('data.description'))->toBe('This is a test application (updated).');
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

test('user can delete their own application', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->deleteJson(route('applications.destroy', [
            'application' => $application->id,
        ]));

    $response->assertNoContent();
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