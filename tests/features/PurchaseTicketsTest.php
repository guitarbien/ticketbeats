<?php

use App\Billing\FakePaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    public function test_使用者可以購票()
    {
        $paymentGateway = new FakePaymentGateway;

        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->create(['ticket_price' => 3250]);

        // Action
        // Purchase concert tickets
        $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $paymentGateway->getValidTestToken(),
        ]);

        // Assert
        $this->assertResponseStatus(201);

        // Make sure the customer was charged the correct amount
        // 要付多少錢會決定 token，再拿 token 來問付了多少錢
        $this->assertEquals(9750, $paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertEquals(3, $order->tickets->count());
    }

}
