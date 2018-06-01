<?php

namespace Tests\Unit\Billing;

use App\Billing\FakePaymentGateway;
use App\Concert;
use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use \Mockery;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use DatabaseMigrations;

    public function test_計算總金額()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        static::assertEquals(3600, $reservation->totalCost());
    }

    public function test_取得被保留的票券()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        static::assertEquals($tickets, $reservation->tickets());
    }

    public function test_取得客戶email()
    {
        $reservation = new Reservation(collect(), 'john@example.com');

        static::assertEquals('john@example.com', $reservation->email());
    }

    public function test_取消保留後保留票券應也要被釋出()
    {
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $reservation->cancel();

        foreach ($tickets as $ticket) {
            $ticket->shouldHaveReceived('release');
        }
    }

    public function test_完成保留操作()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 1200]);
        $tickets = factory(Ticket::class, 3)->create(['concert_id' => $concert->id]);

        $reservation = new Reservation($tickets, 'john@example.com');

        $paymentGateway = new FakePaymentGateway;

        $order = $reservation->complete($paymentGateway, $paymentGateway->getValidTestToken(), 'test_acct_1234');

        static::assertEquals('john@example.com', $order->email);
        static::assertEquals(3, $order->ticketQuantity());
        static::assertEquals(3600, $order->amount);
        static::assertEquals(3600, $paymentGateway->totalChargesFor('test_acct_1234'));
    }
}
