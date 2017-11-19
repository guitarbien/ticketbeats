<?php

namespace App\Listeners;

use App\Events\ConcertAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SchedulePosterImageProcessing
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ConcertAdded  $event
     * @return void
     */
    public function handle(ConcertAdded $event)
    {
        //
    }
}
