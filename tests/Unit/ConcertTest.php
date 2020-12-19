<?php

namespace Tests\Unit;

use App\Concert;
use App\Exceptions\NotEnoughTicketsException;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class ConcertTest
 * @package Tests\Unit
 */
class ConcertTest extends TestCase
{
    use DatabaseMigrations;

    public function test_取得格式化的日期()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 8:00pm'),
        ]);

        static::assertEquals('December 1, 2016', $concert->formatted_date);
    }

    public function test_取得格式化的開始時間()
    {
        $concert = Concert::factory()->make([
            'date' => Carbon::parse('2016-12-01 17:00:00'),
        ]);

        static::assertEquals('5:00pm', $concert->formatted_start_time);
    }

    public function test_以美元顯示票價()
    {
        $concert = Concert::factory()->make([
            'ticket_price' => 6750,
        ]);

        static::assertEquals('67.50', $concert->ticket_price_in_dollars);
    }

    public function test_published_at有值的資料就是已經發佈了()
    {
        $publishedConcertA   = Concert::factory()->create(['published_at' => Carbon::parse('-1 week')]);
        $publishedConcertB   = Concert::factory()->create(['published_at' => Carbon::parse('-2 week')]);
        $unpublishedConcertC = Concert::factory()->create(['published_at' => null]);

        $publishedConcerts = Concert::published()->get();

        static::assertTrue($publishedConcerts->contains($publishedConcertA));
        static::assertTrue($publishedConcerts->contains($publishedConcertB));
        static::assertFalse($publishedConcerts->contains($unpublishedConcertC));
    }

    public function test_concert可以被發佈()
    {
        $concert = Concert::factory()->create([
            'published_at' => null,
            'ticket_quantity' => 5,
        ]);
        static::assertFalse($concert->isPublished());
        static::assertEquals(0, $concert->ticketsRemaining());

        $concert->publish();

        static::assertTrue($concert->isPublished());
        static::assertEquals(5, $concert->ticketsRemaining());
    }

    public function test_可購買的票券不應該包含已有訂單的票券()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        static::assertEquals(2, $concert->ticketsRemaining());
    }

    public function test_已售出的票券應關聯到訂單()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        static::assertEquals(3, $concert->ticketsSold());
    }

    public function test_全部的票券應包含所有票券狀態()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(3)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => null]));

        static::assertEquals(5, $concert->totalTickets());
    }

    public function test_計算票券售出百分比()
    {
        $concert = Concert::factory()->create();
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(Ticket::factory()->count(5)->create(['order_id' => null]));

        static::assertEquals(28.57, $concert->percentSoldOut());
    }

    public function test_計算票券售出總金額()
    {
        $concert = Concert::factory()->create();
        $orderA = Order::factory()->create(['amount' => 3850]);
        $orderB = Order::factory()->create(['amount' => 9625]);
        $concert->tickets()->saveMany(Ticket::factory()->count(2)->create(['order_id' => $orderA->id]));
        $concert->tickets()->saveMany(Ticket::factory()->count(5)->create(['order_id' => $orderB->id]));

        // DB 欄位以 cent 為單位，畫面上是以 dollar 為單位
        static::assertEquals(134.75, $concert->revenueInDollars());
    }

    public function test_保留超過可購買的票券數量會拋出例外()
    {
        $concert = \Database\Factories\ConcertFactory::createPublished(['ticket_quantity' => 10]);

        try {
            $concert->reserveTickets(11, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            static::assertFalse($concert->hasOrderFor('jane@example.com'));
            static::assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order succeeded even though there were not enough tickets remaining.');
    }

    public function test_能保留可用票券()
    {
        $concert = \Database\Factories\ConcertFactory::createPublished(['ticket_quantity' => 3]);
        static::assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'john@example.com');

        static::assertCount(2, $reservation->tickets());
        static::assertEquals('john@example.com', $reservation->email());
        static::assertEquals(1, $concert->ticketsRemaining());
    }

    public function test_已經被購買的票券不能被保留()
    {
        $concert = \Database\Factories\ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $order = Order::factory()->create();
        $order->tickets()->saveMany($concert->tickets->take(2));

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            static::assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Reserving tickets succeeded even though the tickets were already sold.");
    }

    public function test_已經被保留的票券不能再被保留()
    {
        $concert = \Database\Factories\ConcertFactory::createPublished(['ticket_quantity' => 3]);
        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            static::assertEquals(1, $concert->ticketsRemaining());
            return;
        }

        $this->fail("Reserving tickets succeeded even though the tickets were already reserved.");
    }
}
