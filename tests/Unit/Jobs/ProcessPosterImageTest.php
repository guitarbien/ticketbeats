<?php

namespace Tests\Unit\Jobs;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class ProcessPosterImageTest
 * @package Tests\Unit\Jobs
 */
class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_改變posterImage寬度至600px()
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/full-size-poster.png'))
        );
    }
}
