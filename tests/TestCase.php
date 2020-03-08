<?php

namespace Tests;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Assert;

/**
 * Class TestCase
 * @package Tests
 */
abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected function setUp(): void
    {
        parent::setUp();
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);

        TestResponse::macro('data', function($key) {
            return $this->original->getData()[$key];
        });

        EloquentCollection::macro('assertContains', function($value) {
            Assert::AssertTrue($this->contains($value), "Failed asserting that the collection contained the specified value.");
        });

        EloquentCollection::macro('assertNotContains', function($value) {
            Assert::assertFalse($this->contains($value), "Failed asserting that the collection did not contain the specified value.");
        });

        EloquentCollection::macro('assertEquals', function($items) {
            Assert::assertEquals(count($this), count($items));

            $this->zip($items)->each(function ($pair) {
                list($a, $b) = $pair;
                Assert::assertTrue($a->is($b));
            });
        });
    }
}
