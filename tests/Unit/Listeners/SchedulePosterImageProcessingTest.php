<?php

namespace Tests\Unit\Listeners;

use App\Events\ConcertAdded;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchedulePosterImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    public function test_若poster_image存在則會產生一個queue_job來處理image()
    {
        Queue::fake();

        $concert = \ConcertFactory::createUnpublished([
            'poster_image_path' => 'posters/example-poster.png',
        ]);

        // It will push a job (ProcessPosterImage) to a queue when dispatch an event
        ConcertAdded::dispatch($concert);

        Queue::assertPushed(ProcessPosterImage::class);
    }
}
