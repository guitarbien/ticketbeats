<?php

use App\Concert;
use App\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_轉換成array()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 1200])->addTickets(5);
        $order = $concert->orderTickets('jane@example.com', 5);

        $result = $order->toArray();

        $this->assertEquals([
            'email'           => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount'          => 6000,
        ], $result);
    }

    public function test_訂單取消後應將票券釋出()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(10);

        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
        $this->assertNull(Order::find($order->id));
    }
}
