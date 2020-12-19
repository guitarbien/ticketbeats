<?php

namespace Tests\Unit\Billing;

use App\Facades\TicketCode;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

/**
 * Class TicketTest
 * @package Tests\Unit\Billing
 */
class TicketTest extends TestCase
{
    use DatabaseMigrations;

    public function test_票券能被保留()
    {
        $ticket = Ticket::factory()->create();
        static::assertNull($ticket->reserved_at);

        $ticket->reserve();

        static::assertNotNull($ticket->fresh()->reserved_at);
    }

    public function test_票券可以被釋出()
    {
        $ticket = Ticket::factory()->reserved()->create();
        static::assertNotNull($ticket->reserved_at);

        $ticket->release();

        static::assertNull($ticket->fresh()->reserved_at);
    }

    public function test_票券可以被宣告為屬於某張訂單()
    {
        $order  = Order::factory()->create();
        $ticket = Ticket::factory()->create(['code' => null]);

        TicketCode::shouldReceive('generateFor')->with($ticket)->andReturn('TICKETCODE1');

        $ticket->claimFor($order);

        // Assert that the ticket is saved to the order
        static::assertStringContainsString($ticket->id, $order->tickets->pluck('id'));

        // Assert that the ticket had expected ticket code generated
        static::assertEquals('TICKETCODE1', $ticket->code);
    }
}
