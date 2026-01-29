<?php

use App\Models\User;
use App\Models\Application;

test('user cannot create application with empty name', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => '',
            'description' => 'This is a test application without name.',
        ]);

    $response->assertUnprocessable();
});

test('user cannot create application with name too long', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => fake()->text(500),
            'description' => 'This is a test application without name.',
        ]);

    $response->assertUnprocessable();
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
});

test('user cannot update application with empty name', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => '',
            'description' => 'This is a test application without name.',
        ]);

    $response->assertUnprocessable();
});

test('user cannot update application with the same name of another application', function () {
    $user = User::factory()->create();
    $applications = Application::factory()->withUser($user)->count(2)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $applications[0]->id]), [
            'name' => $applications[1]->name,
            'description' => 'This is a test application with the same of another application.',
        ]);

    $response->assertUnprocessable();
});

test('user can create application with an empty description', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => 'Test Application',
        ]);

    $response->assertCreated();
});

test('user cannot create application with description too long', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => 'Test Application',
            'description' => fake()->text(500),
        ]);

    $response->assertUnprocessable();
});

test('user can update application with an empty description', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Test Application',
            'description' => '',
        ]);

    $response->assertOk();
});

test('user cannot update application with description too long', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Test Application',
            'description' => fake()->text(500),
        ]);

    $response->assertUnprocessable();
});