<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\ApiKey;
use App\Models\Application;

/*
|--------------------------------------------------------------------------
| API Key Business Logic Tests
|--------------------------------------------------------------------------
|
| These tests verify the business logic for API key expiration and revocation.
| These are unit-style tests that verify the state of API keys, preparing
| for future authentication middleware that will use these fields.
|
*/

describe('API Key Expiration', function () {
    test('api key with future expiration date is not expired', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->addDays(30),
        ]);

        expect($apiKey->expires_at->isFuture())->toBeTrue();
    });

    test('api key with past expiration date is expired', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->subDay(),
        ]);

        expect($apiKey->expires_at->isPast())->toBeTrue();
    });

    test('api key expiring now is considered expired', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        // Create key that expires exactly now
        $expirationTime = now();
        $apiKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => $expirationTime,
        ]);

        // Travel 1 second forward
        $this->travel(1)->seconds();

        expect($apiKey->fresh()->expires_at->isPast())->toBeTrue();
    });

    test('newly created api key has valid expiration date', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'test-expiration-' . fake()->unique()->word(),
            ]);

        $response->assertCreated();

        $apiKeyId = $response->json('data.id');
        $apiKey = ApiKey::find($apiKeyId);

        // Default expiration should be in the future
        expect($apiKey->expires_at->isFuture())->toBeTrue();

        // Default expiration should match config duration
        $expectedDuration = (int) config('api-keys.duration');
        $actualDuration = now()->diffInSeconds($apiKey->expires_at);

        // Allow 5 seconds tolerance for test execution time
        expect($actualDuration)->toBeGreaterThan($expectedDuration - 5);
        expect($actualDuration)->toBeLessThanOrEqual($expectedDuration + 5);
    });

    test('api key with custom expiration date respects the value', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $customExpiration = now()->addDays(7);

        $response = $this
            ->actingAs($user)
            ->postJson(route('api-keys.store'), [
                'application_id' => $application->id,
                'name' => 'custom-expiration-' . fake()->unique()->word(),
                'expires_at' => $customExpiration->toDateTimeString(),
            ]);

        $response->assertCreated();

        $apiKeyId = $response->json('data.id');
        $apiKey = ApiKey::find($apiKeyId);

        // Should be close to our custom date (within 1 second)
        expect($apiKey->expires_at->diffInSeconds($customExpiration))->toBeLessThanOrEqual(1);
    });
});

describe('API Key Revocation', function () {
    test('newly created api key is not revoked', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        expect($apiKey->revoked_at)->toBeNull();
    });

    test('revoked api key has revoked_at timestamp', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $apiKey->refresh();

        expect($apiKey->revoked_at)->not->toBeNull();
        expect($apiKey->revoked_at)->toBeInstanceOf(\Carbon\CarbonInterface::class);
    });

    test('revoked_at timestamp is set to current time', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $apiKey = ApiKey::factory()->withApplication($application)->create();

        $beforeRevoke = now()->subSecond();

        $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $afterRevoke = now()->addSecond();

        $apiKey->refresh();

        // revoked_at should be between before and after (with 1 second tolerance)
        expect($apiKey->revoked_at->gte($beforeRevoke))->toBeTrue();
        expect($apiKey->revoked_at->lte($afterRevoke))->toBeTrue();
    });

    test('api key can be both expired and revoked', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        // Create expired key
        $apiKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->subDay(),
        ]);

        // Revoke it
        $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $apiKey->refresh();

        expect($apiKey->expires_at->isPast())->toBeTrue();
        expect($apiKey->revoked_at)->not->toBeNull();
    });

    test('revoking does not change expiration date', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();
        $originalExpiration = now()->addDays(30);

        $apiKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => $originalExpiration,
        ]);

        $this
            ->actingAs($user)
            ->postJson(route('api-keys.revoke', ['api_key' => $apiKey->id]));

        $apiKey->refresh();

        expect($apiKey->expires_at->diffInSeconds($originalExpiration))->toBeLessThanOrEqual(1);
    });
});

describe('API Key Validity Helpers (Future Implementation)', function () {
    /*
     * These tests document expected behavior for when you implement
     * API key authentication. You can add helper methods to the model:
     *
     * public function isExpired(): bool
     * {
     *     return $this->expires_at->isPast();
     * }
     *
     * public function isRevoked(): bool
     * {
     *     return $this->revoked_at !== null;
     * }
     *
     * public function isValid(): bool
     * {
     *     return !$this->isExpired() && !$this->isRevoked();
     * }
     */

    test('can determine if api key is expired using Carbon', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $expiredKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->subDay(),
        ]);

        $validKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->addDay(),
        ]);

        // Using Carbon methods directly
        expect($expiredKey->expires_at->isPast())->toBeTrue();
        expect($validKey->expires_at->isPast())->toBeFalse();
    });

    test('can determine if api key is revoked by checking revoked_at', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $revokedKey = ApiKey::factory()->withApplication($application)->create([
            'revoked_at' => now(),
        ]);

        $activeKey = ApiKey::factory()->withApplication($application)->create([
            'revoked_at' => null,
        ]);

        expect($revokedKey->revoked_at)->not->toBeNull();
        expect($activeKey->revoked_at)->toBeNull();
    });

    test('valid api key is neither expired nor revoked', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $validKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->addDays(30),
            'revoked_at' => null,
        ]);

        $isValid = $validKey->expires_at->isFuture() && $validKey->revoked_at === null;

        expect($isValid)->toBeTrue();
    });

    test('expired api key is not valid', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $expiredKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->subDay(),
            'revoked_at' => null,
        ]);

        $isValid = $expiredKey->expires_at->isFuture() && $expiredKey->revoked_at === null;

        expect($isValid)->toBeFalse();
    });

    test('revoked api key is not valid even if not expired', function () {
        $user = User::factory()->create();
        $application = Application::factory()->withUser($user)->create();

        $revokedKey = ApiKey::factory()->withApplication($application)->create([
            'expires_at' => now()->addDays(30),
            'revoked_at' => now(),
        ]);

        $isValid = $revokedKey->expires_at->isFuture() && $revokedKey->revoked_at === null;

        expect($isValid)->toBeFalse();
    });
});
