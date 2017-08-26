<?php
use App\Concert;

class ConcertFactory
{
    public static function createPublished($overrides = [])
    {
        $concert = factory(Concert::class)->create($overrides);
        $concert->publish();
        return $concert;
    }

    public static function createUnpublished($overrides = [])
    {
        $concert = factory(Concert::class)->create($overrides);
        return $concert;
    }
}