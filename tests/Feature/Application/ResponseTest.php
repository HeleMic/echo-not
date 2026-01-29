<?php

use App\Models\User;
use App\Models\Application;
use Illuminate\Support\Str;

test('store returns correct structure', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => 'Test Application',
            'description' => 'This is a test application.',
        ]);

    $response->assertCreated();

    expect($response->json('data'))->toBeArray()->toHaveKeys([
        'id',
        'name',
        'description',
        'api_key',
        'created_at',
        'updated_at',
    ]);
});

test('store exposes api key', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->postJson(route('applications.store'), [
            'name' => 'Test Application',
            'description' => 'This is a test application.',
        ]);

    $response->assertCreated();

    expect($response->json('data.api_key'))->toHaveLength(Str::length(config('application.api_key.prefix')) + config('application.api_key.length'));
});

test('index returns paginated structure', function () {
    $user = User::factory()->create();
    Application::factory()->count(20)->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.index'));

    $response->assertOk();

    expect($response->json('data'))->toBeArray()->toHaveLength(config('constants.pagination.elements_for_page'));
    expect($response->json('links'))->toBeArray()->toHaveKeys([
        'first',
        'last',
        'prev',
        'next',
    ]);
    expect($response->json('meta'))->toBeArray()->toHaveKeys([
        'current_page',
        'from',
        'last_page',
        'path',
        'per_page',
        'to',
        'total',
    ]);
});

test('index does not expose api keys', function () {
    $user = User::factory()->create();
    Application::factory()->count(5)->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.index'));

    $response->assertOk();

    foreach ($response->json('data') as $application) {
        expect($application['api_key'])->toEqual(config('application.api_key.hidden_placeholder'));
    }
});

test('show returns correct structure', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.show', ['application' => $application->id]));

    $response->assertOk();

    expect($response->json('data'))->toBeArray()->toHaveKeys([
        'id',
        'name',
        'description',
        'api_key',
        'created_at',
        'updated_at',
    ]);
});

test('show does not expose api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->getJson(route('applications.show', ['application' => $application->id]));

    $response->assertOk();

    expect($response->json('data.api_key'))->toEqual(config('application.api_key.hidden_placeholder'));
});

test('update returns correct structure', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Test Application updated',
            'description' => 'This is a test application (updated).',
        ]);

    $response->assertOk();

    expect($response->json('data'))->toBeArray()->toHaveKeys([
        'id',
        'name',
        'description',
        'api_key',
        'created_at',
        'updated_at',
    ]);
});

test('update does not expose api key', function () {
    $user = User::factory()->create();
    $application = Application::factory()->withUser($user)->create();

    $response = $this
        ->actingAs($user, 'sanctum')
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'name' => 'Test Application updated',
            'description' => 'This is a test application (updated).',
        ]);

    $response->assertOk();

    expect($response->json('data.api_key'))->toEqual(config('application.api_key.hidden_placeholder'));
});
