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

        static::assertEquals(600, $width);
        // 600/(8.5/11) = 776
        static::assertEquals(776, $height);

        $optimizedImageContents = Storage::disk('public')->get('posters/example-poster.png');
        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
        static::assertEquals($controlImageContents, $optimizedImageContents);
    }

    public function test_優化圖片大小()
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/__fixtures__/small-unoptimized-poster.png'))
        );

        $concert = ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);

        ProcessPosterImage::dispatch($concert);

        $optimizedImageSize = Storage::disk('public')->size('posters/example-poster.png');
        $originalSize = filesize(base_path('tests/__fixtures__/small-unoptimized-poster.png'));
        static::assertLessThan($originalSize, $optimizedImageSize);

        $optimizedImageContents = Storage::disk('public')->get('posters/example-poster.png');
        $controlImageContents = file_get_contents(base_path('tests/__fixtures__/optimized-poster.png'));
        static::assertEquals($controlImageContents, $optimizedImageContents);
    }
}
