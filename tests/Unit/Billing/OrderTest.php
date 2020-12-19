<?php

namespace Tests\Unit\Billing;

use App\Billing\Charge;
use App\Order;
use App\Ticket;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Mockery;
use Tests\TestCase;

/**
 * Class OrderTest
 * @package Tests\Unit\Billing
 */
class OrderTest extends TestCase
{
    use DatabaseMigrations;

    public function test_用票券和email和付款物件建立訂單()
    {
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);
        $tickets = collect([
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
            Mockery::spy(Ticket::class),
        ]);

        $order = Order::forTickets($tickets, 'jane@example.com', $charge);

        static::assertEquals('jane@example.com', $order->email);
        static::assertEquals(3600, $order->amount);
        static::assertEquals('1234', $order->card_last_four);
        $tickets->each->shouldHaveReceived('claimFor', [$order]);
    }

    public function test_用確認碼取得訂單資訊()
    {
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        static::assertEquals($order->id, $foundOrder->id);
    }

    public function test_用確認碼查詢不存在的訂單資訊拋出例外()
    {
        $this->expectException(ModelNotFoundException::class);
        Order::findByConfirmationNumber('NONEXISTENTCONFIRMATIONNUMBER');
    }

    public function test_轉換成array()
    {
        $order = Order::factory()->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'jane@example.com',
            'amount'              => 6000,
        ]);

        $order->tickets()->saveMany([
            Ticket::factory()->create(['code' => 'TICKETCODE1']),
            Ticket::factory()->create(['code' => 'TICKETCODE2']),
            Ticket::factory()->create(['code' => 'TICKETCODE3']),
        ]);

        $result = $order->toArray();

        static::assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'               => 'jane@example.com',
            'amount'              => 6000,
            'tickets'             => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ],
        ], $result);
    }
}
