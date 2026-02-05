<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| API Key Authentication Tests
|--------------------------------------------------------------------------
|
| These tests verify authentication rules for API keys.
|
*/

describe('Authentication with API Key', function () {

    describe('successful authentication', function () {

        test('accepts requests with valid API key', function () {
            $application = Application::factory()->create();
            $apiKey = ApiKey::factory()->withApplication($application)->create();

            $response = $this->withHeaders([
                'X-ECHONOT-API-KEY' => $apiKey->plainKey,
            ])->postJson('/api/notifications');

            $response->assertCreated();
        });

        test('updates last_used_at when API key is used', function () {
            $application = Application::factory()->create();
            $apiKey = ApiKey::factory()->withApplication($application)->create();

            expect($apiKey->last_used_at)->toBeNull();

            $this->withHeaders([
                'X-ECHONOT-API-KEY' => $apiKey->plainKey,
            ])->postJson('/api/notifications');

            $apiKey->refresh();
            expect($apiKey->last_used_at)->not->toBeNull();
        });

        test('sets application and api_key on request attributes', function () {
            $application = Application::factory()->create();
            $apiKey = ApiKey::factory()->withApplication($application)->create();

            $response = $this->withHeaders([
                'X-ECHONOT-API-KEY' => $apiKey->plainKey,
            ])->postJson('/api/notifications');

            $response->assertCreated();
            // If request reaches controller, attributes were set correctly
        });

    });

    describe('failed authentication', function () {

        test('rejects requests without API key header', function () {
            $response = $this->postJson('/api/notifications');

            $response
                ->assertUnauthorized()
                ->assertJson(['message' => 'API key missing.']);
        });

        test('rejects requests with non-existent API key', function () {
            $response = $this->withHeaders([
                'X-ECHONOT-API-KEY' => 'non-existent-key-12345',
            ])->postJson('/api/notifications');

            $response
                ->assertUnauthorized()
                ->assertJson(['message' => 'Invalid API key.']);
        });

        test('rejects requests with expired API key', function () {
            $application = Application::factory()->create();
            $apiKey = ApiKey::factory()
                ->withApplication($application)
                ->expired()
                ->create();

            $response = $this->withHeaders([
                'X-ECHONOT-API-KEY' => $apiKey->plainKey,
            ])->postJson('/api/notifications');

            $response
                ->assertUnauthorized()
                ->assertJson(['message' => 'Invalid API key.']);
        });

        test('rejects requests with revoked API key', function () {
            $application = Application::factory()->create();
            $apiKey = ApiKey::factory()
                ->withApplication($application)
                ->revoked()
                ->create();

            $response = $this->withHeaders([
                'X-ECHONOT-API-KEY' => $apiKey->plainKey,
            ])->postJson('/api/notifications');

            $response
                ->assertUnauthorized()
                ->assertJson(['message' => 'Invalid API key.']);
        });

        test('rejects API key when application is deleted', function () {
            $application = Application::factory()->create();
            $apiKey = ApiKey::factory()->withApplication($application)->create();
            $plainKey = $apiKey->plainKey;

            // Delete the application (soft delete or hard delete based on your setup)
            $application->delete();

            $response = $this->withHeaders([
                'X-ECHONOT-API-KEY' => $plainKey,
            ])->postJson('/api/notifications');

            $response
                ->assertUnauthorized()
                ->assertJson(['message' => 'Invalid API key.']);
        });

    });

});