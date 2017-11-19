<?php

namespace App\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ConcertAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $concert;

    /**
     * ConcertAdded constructor.
     * @param $concert
     */
    public function __construct($concert)
    {
        $this->concert = $concert;
    }
}
