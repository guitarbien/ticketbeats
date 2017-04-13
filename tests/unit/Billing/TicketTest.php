<?php

use App\Concert;
use App\Facades\TicketCode;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class TicketTest extends TestCase
{
    use DatabaseMigrations;

    public function test_票券能被保留()
    {
        $ticket = factory(Ticket::class)->create();
        $this->assertNull($ticket->reserved_at);

        $ticket->reserve();

        $this->assertNotNull($ticket->fresh()->reserved_at);
    }

    public function test_票券可以被釋出()
    {
        $ticket = factory(Ticket::class)->states('reserved')->create();
        $this->assertNotNull($ticket->reserved_at);

        $ticket->release();

        $this->assertNull($ticket->fresh()->reserved_at);
    }

    public function test_票券可以被宣告為屬於某張訂單()
    {
        $order  = factory(Order::class)->create();
        $ticket = factory(Ticket::class)->create(['code' => null]);

        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        // Assert that the ticket is saved to the order
        $this->assertContains($ticket->id, $order->tickets->pluck('id'));

        // Assert that the ticket had expected ticket code generated
        $this->assertEquals('TICKETCODE1', $ticket->code);
    }
}
