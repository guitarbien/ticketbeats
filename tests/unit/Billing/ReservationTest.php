<?php

use App\Reservation;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class ReservationTest extends TestCase
{
    public function test_計算總金額()
    {
        $tickets = collect([
            (object) ['price' => 1200],
            (object) ['price' => 1200],
            (object) ['price' => 1200],
        ]);

        $reservation = new Reservation($tickets);

        $this->assertEquals(3600, $reservation->totalCost());
    }

    public function test_取消保留後保留票券應也要被釋出()
    {
        $tickets = collect([
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
            Mockery::mock(Ticket::class)->shouldReceive('release')->once()->getMock(),
        ]);

        $reservation = new Reservation($tickets);

        $reservation->cancel();
    }
}
