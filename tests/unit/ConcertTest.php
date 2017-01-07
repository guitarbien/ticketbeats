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
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    public function test_取得格式化的開始時間()
    {
        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    public function test_以美元顯示票價()
    {
        $concert = factory(Concert::class)->create([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }
}
