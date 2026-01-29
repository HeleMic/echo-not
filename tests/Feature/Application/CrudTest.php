<?php

use App\Models\User;
use App\Models\Application;

test('user can create their own application', function () {
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

test('user cannot view a non-existent application', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.show', [
            'application' => fake()->uuid(),
        ]));

    $response->assertNotFound();
});

test('user can list their own application', function () {
    $user = User::factory()->create();
    Application::factory()->withUser($user)->count(10)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.index'));

    $response->assertOk();
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

test('user cannot update a non-existent application', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => fake()->uuid()]), [
            'name' => 'Test Application updated',
            'description' => 'This is a test application (updated).',
        ]);

    $response->assertNotFound();
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

test('user cannot delete a non-existent application', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->deleteJson(route('applications.destroy', [
            'application' => fake()->uuid(),
        ]));

    $response->assertNotFound();
});