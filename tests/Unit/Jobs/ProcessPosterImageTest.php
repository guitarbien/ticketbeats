<?php

namespace Tests\Unit\Jobs;

use App\Jobs\ProcessPosterImage;
use ConcertFactory;
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

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('public')->get('posters/example-poster.png');
        list($width, $height) = getimagesizefromstring($resizedImage);

        $this->assertEquals(600, $width);
        // 600/(8.5/11) = 776
        $this->assertEquals(776, $height);
    }
}
