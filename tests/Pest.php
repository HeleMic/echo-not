<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

expect()->extend('toHavePaginatedStructure', function (int $expectedCount = null) {
    // Verify 'data'
    $this->toHaveKey('data');
    expect($this->value['data'])->toBeArray();

    if ($expectedCount !== null) {
        expect($this->value['data'])->toHaveLength($expectedCount);
    }

    // Verify 'links'
    expect($this->value)->toHaveKey('links');
    expect($this->value['links'])->toBeArray()->toHaveKeys([
        'first',
        'last',
        'prev',
        'next',
    ]);

    // Verify 'meta'
    expect($this->value)->toHaveKey('meta');
    expect($this->value['meta'])->toBeArray()->toHaveKeys([
        'current_page',
        'from',
        'last_page',
        'path',
        'per_page',
        'to',
        'total',
    ]);

    return $this;
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
