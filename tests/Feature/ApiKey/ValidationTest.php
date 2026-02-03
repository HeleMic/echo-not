<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| API Key Validation Tests
|--------------------------------------------------------------------------
|
| These tests verify input validation for API key operations.
| Uses Pest datasets for comprehensive coverage of edge cases.
|
*/

describe('Store Validation - Name Field', function () {
    it('rejects invalid names on create', function (mixed $name, string $errorKey) {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => $name,
                'expires_at' => now()->addDays(30)->format('Y-m-d H:i:s'),
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    })->with([
                'empty string' => ['', 'required'],
                'null value' => [null, 'required'],
                'too long (256 chars)' => [str_repeat('a', 256), 'max'],
                'too long (500 chars)' => [str_repeat('x', 500), 'max'],
            ]);

    test('accepts valid name with max length (255 chars)', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $maxLengthName = str_repeat('a', 255);

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => $maxLengthName,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('api_keys', [
            'application_id' => $application->id,
            'name' => $maxLengthName,
        ]);
    });
});

describe('Store Validation - Application ID Field', function () {
    it('rejects invalid application_id on create', function (mixed $applicationId) {
        $user = User::factory()->create();
        // Create an application so user has at least one
        Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $applicationId,
                'name' => 'valid-name',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['application_id']);
    })->with([
                'empty string' => [''],
                'null value' => [null],
                'invalid uuid format' => ['not-a-uuid'],
                'numeric value' => [12345],
                'non-existent uuid' => ['00000000-0000-0000-0000-000000000000'],
            ]);

    test('application_id must belong to authenticated user', function () {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $ownerApplication = Application::factory()->withUser($owner)->create();

        // Attacker tries to use owner's application
        $response = $this
            ->actingAs($attacker)
            ->postJson(route('api-keys.store'), [
                'application_id' => $ownerApplication->id,
                'name' => 'malicious-key',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['application_id']);
    });
});

describe('Store Validation - Expiration Date Field', function () {
    it('rejects invalid expiration dates on create', function (mixed $expiresAt) {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'test-key-' . fake()->unique()->word(),
                'expires_at' => $expiresAt,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['expires_at']);
    })->with([
                'invalid format' => ['not-a-date'],
                'random string' => ['tomorrow'],
                'incomplete date' => ['2025-01'],
                'past date' => ['2020-01-01 00:00:00'],
                'yesterday' => [now()->subDay()->format('Y-m-d H:i:s')],
            ]);

    test('expiration date cannot exceed max duration', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $maxDuration = (int) config('api-keys.max_duration');
        $tooFarDate = now()->addSeconds($maxDuration + 86400)->format('Y-m-d H:i:s'); // +1 day over max

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'test-key-' . fake()->unique()->word(),
                'expires_at' => $tooFarDate,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['expires_at']);
    });

    test('expiration date at max duration boundary is accepted', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $maxDuration = (int) config('api-keys.max_duration');
        // Slightly before max to avoid timing issues
        $validDate = now()->addSeconds($maxDuration - 60)->format('Y-m-d H:i:s');

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'boundary-test-' . fake()->unique()->word(),
                'expires_at' => $validDate,
            ]);

        $response->assertCreated();
    });

    test('null expiration date is accepted (uses default)', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'no-expiry-' . fake()->unique()->word(),
            ]);

        $response->assertCreated();
    });
});

describe('Store Validation - Name Uniqueness', function () {
    test('cannot create two api keys with the same name', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $duplicateName = 'duplicate-key-name';

        $this->actingAs($user);

        $firstResponse = $this->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => $duplicateName,
        ]);

        $secondResponse = $this->postJson(route('api-keys.store'), [
            'application_id' => $application->id,
            'name' => $duplicateName,
        ]);

        $firstResponse->assertCreated();
        $secondResponse->assertUnprocessable();
        $secondResponse->assertJsonValidationErrors(['name']);
    });

    test('different users cannot have api keys with same name (global uniqueness)', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $app1 = Application::factory()->withUser($user1)->create();
        $app2 = Application::factory()->withUser($user2)->create();
        $sharedName = 'shared-name-' . fake()->unique()->word();

        // User 1 creates key
        $response1 = $this
            ->actingAs($user1)
            ->postJson(route('api-keys.store'), [
                'application_id' => $app1->id,
                'name' => $sharedName,
            ]);

        // User 2 tries same name
        $response2 = $this
            ->actingAs($user2)
            ->postJson(route('api-keys.store'), [
                'application_id' => $app2->id,
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
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => $name,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    })->with([
                'empty string' => [''],
                'null value' => [null],
                'too long (256 chars)' => [str_repeat('a', 256)],
            ]);

    test('user can update api key keeping the same name', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();
        $originalName = $apiKey->name;

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey->id]), [
                'name' => $originalName,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey->id,
            'name' => $originalName,
        ]);
    });

    test('cannot update to another existing api key name', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey1 = ApiKey::factory()->withApplication($application)->create();
        $apiKey2 = ApiKey::factory()->withApplication($application)->create();

        $response = $this
            ->actingAs($user)
            ->putJson(route('api-keys.update', ['api_key' => $apiKey1->id]), [
                'name' => $apiKey2->name, // Try to steal apiKey2's name
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);

        // Verify original name preserved
        $this->assertDatabaseHas('api_keys', [
            'id' => $apiKey1->id,
            'name' => $apiKey1->name,
        ]);
    });
});

describe('Validation Error Response Format', function () {
    test('validation errors return proper JSON structure', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => '', // Invalid
                'expires_at' => 'invalid-date', // Invalid
            ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'name',
                'expires_at',
            ],
        ]);
    });

    test('multiple validation errors are returned together', function () {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                // Missing application_id
                'name' => '', // Invalid
                'expires_at' => 'not-a-date', // Invalid
            ]);

        $response->assertUnprocessable();

        $errors = $response->json('errors');
        expect(array_keys($errors))->toContain('application_id');
        expect(array_keys($errors))->toContain('name');
        expect(array_keys($errors))->toContain('expires_at');
    });
});
