<?php

use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

test('user can create a new application', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $response->assertStatus(Response::HTTP_CREATED);
});

test('user cannot create application when not authenticated', function () {
    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test('user can view their own application', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $applicationId = $response->json('data.id');

    $response = $this->getJson(route('applications.show', ['application' => $applicationId]));

    $response->assertStatus(Response::HTTP_OK);
});

test('user cannot view another user\'s application', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $applicationId = $response->json('data.id');

    $this->actingAs($user2, 'sanctum');

    $response = $this->getJson(route('applications.show', ['application' => $applicationId]));

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('user can update their own application', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $applicationId = $response->json('data.id');

    $response = $this->putJson(route('applications.update', ['application' => $applicationId]), [
        'name' => 'Test Application updated',
        'description' => 'This is a test application (updated).',
    ]);

    $response->assertStatus(Response::HTTP_OK);
});

test('user cannot update another user\'s application', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $applicationId = $response->json('data.id');

    $this->actingAs($user2, 'sanctum');

    $response = $this->putJson(route('applications.update', ['application' => $applicationId]), [
        'name' => 'Test Application updated',
        'description' => 'This is a test application (updated).',
    ]);

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});

test('user can delete their own application', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $applicationId = $response->json('data.id');

    $response = $this->deleteJson(route('applications.destroy', ['application' => $applicationId]));

    $response->assertStatus(Response::HTTP_NO_CONTENT);
});

test('user can delete another user\'s application', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $this->actingAs($user1, 'sanctum');

    $response = $this->postJson(route('applications.store'), [
        'name' => 'Test Application',
        'description' => 'This is a test application.',
    ]);

    $applicationId = $response->json('data.id');

    $this->actingAs($user2, 'sanctum');

    $response = $this->deleteJson(route('applications.destroy', ['application' => $applicationId]));

    $response->assertStatus(Response::HTTP_FORBIDDEN);
});