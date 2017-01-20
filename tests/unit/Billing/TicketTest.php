<?php

use App\Concert;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    public function test_票券能被保留()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reservt_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserve_at);
    }

    public function test_票券可以被釋出()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(1);
        $order = $concert->orderTickets('jane@example.com', 1);
        $ticket = $order->tickets()->first();

        $this->assertEquals($order->id, $ticket->order_id);

        $ticket->release();

        $this->assertNull($ticket->fresh()->order_id);
    }
}
