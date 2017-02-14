<?php

use App\Concert;
use App\Order;
use App\Ticket;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class viewOrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_使用者可以查看訂單確認頁()
    {
        // create a concert
        $concert = factory(Concert::class)->create([
            'title'         => 'The Red Chord',
            'subtitle'      => 'with Animosity and Lethargy',
            'date'          => Carbon::parse('March 12, 2017 8:00pm'),
            'ticket_price'  => 4250,
            'venue'         => 'The Mosh Pit',
            'venue_address' => '123 Example Lane',
            'city'          => 'Laraville',
            'state'         => 'ON',
            'zip'           => '17916',
        ]);

        // create a order
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'card_last_four'      => '1881',
            'amount'              => 8500,
            'email'               => 'john@example.com',
        ]);
        // create tickets
        $ticketA = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id'   => $order->id,
            'code'       => 'TICKETCODE123',
        ]);
        $ticketB = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id'   => $order->id,
            'code'       => 'TICKETCODE456',
        ]);

        // visit thr order confirmation page
        $response = $this->get("/orders/ORDERCONFIRMATION1234");

        // Assert we see the correct order details
        $response->assertStatus(200);

        // Assert the view has an variable; Assert closure is true.
        $response->assertViewHas('order', function($viewOrder) use($order) {
            return $order->id === $viewOrder->id;
        });

        $response->assertSee('ORDERCONFIRMATION1234');
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 1881');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');
        $response->assertSee('The Red Chord');
        $response->assertSee('with Animosity and Lethargy');
        $response->assertSee('The Mosh Pit');
        $response->assertSee('123 Example Lane');
        $response->assertSee('Laraville, ON');
        $response->assertSee('17916');
        $response->assertSee('john@example.com');

        $response->assertSee('Sunday, March 12, 2017');
        $response->assertSee('Doors at 8:00pm');
    }
}
