<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| Application Validation Tests
|--------------------------------------------------------------------------
|
| These tests verify input validation for Application operations.
| Uses Pest datasets for comprehensive coverage of edge cases.
|
*/

describe('Store Validation - Name Field', function () {
    it('rejects invalid names on create', function (mixed $name) {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => $name,
                'description' => 'Valid description',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    })->with([
                'empty string' => [''],
                'null value' => [null],
                'too long (256 chars)' => [str_repeat('a', 256)],
                'too long (500 chars)' => [str_repeat('x', 500)],
            ]);

    test('accepts valid name with max length (255 chars)', function () {
        $user = User::factory()->create();
        $maxLengthName = str_repeat('a', 255);

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => $maxLengthName,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('applications', [
            'user_id' => $user->id,
            'name' => $maxLengthName,
        ]);
    });
});

describe('Store Validation - Description Field', function () {
    test('description is optional', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => 'valid-name-' . fake()->unique()->word(),
            ]);

        $response->assertCreated();
    });

    test('description can be empty string', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => 'valid-name-' . fake()->unique()->word(),
                'description' => '',
            ]);

        $response->assertCreated();
    });

    test('description cannot exceed max length', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => 'valid-name-' . fake()->unique()->word(),
                'description' => str_repeat('a', 500),
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    });
});

describe('Store Validation - Name Uniqueness', function () {
    test('cannot create two applications with the same name', function () {
        $user = User::factory()->create();
        $duplicateName = 'duplicate-app-name';

        $this->actingAs($user);

        $firstResponse = $this->postJson(route('applications.store'), [
            'name' => $duplicateName,
        ]);

        $secondResponse = $this->postJson(route('applications.store'), [
            'name' => $duplicateName,
        ]);

        $firstResponse->assertCreated();
        $secondResponse->assertUnprocessable();
        $secondResponse->assertJsonValidationErrors(['name']);
    });

    test('different users cannot have applications with same name (global uniqueness)', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $sharedName = 'shared-name-' . fake()->unique()->word();

        // User 1 creates application
        $response1 = $this
            ->actingAs($user1)
            ->postJson(route('applications.store'), [
                'name' => $sharedName,
            ]);

        // User 2 tries same name
        $response2 = $this
            ->actingAs($user2)
            ->postJson(route('applications.store'), [
                'name' => $sharedName,
            ]);

        $response1->assertCreated();
        $response2->assertUnprocessable();
        $response2->assertJsonValidationErrors(['name']);
    });
});

describe('Update Validation - Name Field', function () {
    it('rejects invalid names on update', function (mixed $name) {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $name,
                'description' => 'Valid description',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    })->with([
                'empty string' => [''],
                'null value' => [null],
                'too long (256 chars)' => [str_repeat('a', 256)],
            ]);

    test('user can update application keeping the same name', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $originalName = $application->name;

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $originalName,
                'description' => 'Updated description',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('applications', [
            'id' => $application->id,
            'name' => $originalName,
        ]);
    });

    test('cannot update to another existing application name', function () {
        $user = User::factory()->create();
        $app1 = Application::factory()->withUser($user)->create();
        $app2 = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $app1->id]), [
                'name' => $app2->name, // Try to steal app2's name
                'description' => 'Description',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);

        // Verify original name preserved
        $this->assertDatabaseHas('applications', [
            'id' => $app1->id,
            'name' => $app1->name,
        ]);
    });
});

describe('Update Validation - Description Field', function () {
    test('description can be empty on update', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create([
            'description' => 'Original description',
        ]);

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $application->name,
                'description' => '',
            ]);

        $response->assertOk();
    });

    test('description cannot exceed max length on update', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('applications.update', ['application' => $application->id]), [
                'name' => $application->name,
                'description' => str_repeat('a', 500),
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    });
});

describe('Validation Error Response Format', function () {
    test('validation errors return proper JSON structure', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                'name' => '', // Invalid
                'description' => str_repeat('a', 500), // Invalid
            ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'description',
            ],
        ]);
    });

    test('multiple validation errors are returned together', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('applications.store'), [
                // Missing name
                'description' => str_repeat('a', 500), // Invalid
            ]);

        $response->assertUnprocessable();

        $errors = $response->json('errors');
        expect(array_keys($errors))->toContain('name');
        expect(array_keys($errors))->toContain('description');
    });
});
