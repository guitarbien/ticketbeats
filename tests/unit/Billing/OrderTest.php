<?php

use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class OrderTest extends TestCase
{
    public function test_訂單取消後應將票券釋出()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(10);

        $order = $concert->orderTickets('jane@example.com', 5);
        $this->assertEquals(5, $concert->ticketsRemaining());

        $order->cancel();

        $this->assertEquals(10, $concert->ticketsRemaining());
    }
}
