<?php

use App\Concert;
use App\Order;
use App\Ticket;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class viewOrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_使用者可以查看訂單確認頁()
    {
        // create a concert
        $concert = factory(Concert::class)->create();
        // create a order
        $order = factory(Order::class)->create();
        // create a ticket
        $ticket = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id'   => $order->id,
        ]);

        // visit thr order confirmation page
        // Assert we see the correct order details
    }
}