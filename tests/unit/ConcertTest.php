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
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        $this->assertEquals('December 1, 2016', $concert->formatted_date);
    }

    public function test_取得格式化的開始時間()
    {
        $concert = factory(Concert::class)->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    public function test_以美元顯示票價()
    {
        $concert = factory(Concert::class)->make([
            'ticket_price' => 6750,
        ]);

        $this->assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    public function test_published_at有值的資料就是已經發佈了()
    {
        $publishedConcertA   = factory(Concert::class)->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB   = factory(Concert::class)->create(['published_at' => Carbon::parse('-2 week')]);
        $unpublishedConcertC = factory(Concert::class)->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcertC));
    }

    public function test_寫入訂單和票券()
    {
        $concert = factory(Concert::class)->create();

        $order = $concert->orderTickets('jane@example.com', 3);

        $this->assertEquals('jane@example.com', $order->email);
        $this->assertEquals(3, $order->tickets()->count());
    }

    public function test_可以加入票券()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    public function test_可購買的票券不應該包含已有訂單的票券()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);
        $concert->orderTickets('jane@example.com', 30);

        $this->assertEquals(20, $concert->ticketsRemaining());
    }
}
