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
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        // Action
        // Assert
        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }
}
