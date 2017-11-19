<?php

namespace App\Listeners;

use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;

class SchedulePosterImageProcessing
{
    public function __construct()
    {
        //
    }

    public function handle(ConcertAdded $event)
    {
        ProcessPosterImage::dispatch($event);
    }
}
