<?php

namespace Tests;

use App\Exceptions\DisableExceptionHandler;
use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    protected function setUp()
    {
        parent::setUp();
        \Mockery::getConfiguration()->allowMockingNonExistentMethods(false);
    }

    protected function disableExceptionHandling()
    {
        $this->app->instance(ExceptionHandler::class, new DisableExceptionHandler);
    }
}
