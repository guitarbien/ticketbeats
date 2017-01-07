<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function test_取得格式化的日期()
    {
        // Arrange
        $concert = Concert::create([
            'date'                   => Carbon::parse('2016-12-01 8:00pm'),
            'title'                  => 'The Red Chord',
            'subtitle'               => 'with Animosity and Lethargy',
            'ticket_price'           => 3250,
            'venue'                  => 'The Mosh Pit',
            'venue_address'          => '123 Example Lane',
            'city'                   => 'Laraville',
            'state'                  => 'ON',
            'zip'                    => '17916',
            'additional_information' => 'For tickets, call (555) 555-5555.',
        ]);

        // Action
        $date = $concert->formatted_date;

        // Assert
        $this->assertEquals('December 1, 2016', $date);
    }
}
