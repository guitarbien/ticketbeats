<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }

    private function orderTickets($concert, $params)
    {
        $this->json('POST', "/concerts/{$concert->id}/orders", $params);
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    public function test_使用者可以購票()
    {
        // Arrange
        // Create a concert
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250])->addTickets(3);

        // Action
        // Purchase concert tickets\
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assert
        $this->assertResponseStatus(201);

        // Make sure the customer was charged the correct amount
        // 要付多少錢會決定 token，再拿 token 來問付了多少錢
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    public function test_不能購買尚未發佈的票()
    {
        $concert = factory(Concert::class)->states('unpublished')->create(['ticket_price' => 3250]);
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertEquals(0, $concert->orders()->count());
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    public function test_不能超量購買()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(50);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);

        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    public function test_若付款失敗則不會產生訂單()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250]);
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);

        $order = $concert->orders()->where('email', 'john@example.com')->first();
        $this->assertNull($order);
    }

    public function test_下單時email為必填()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    public function test_驗證email格式()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'error-email-format',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    public function test_票券數量為必填()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'email'         => 'error-email-format',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    public function test_票券數量至少要為1()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'ticket_quantity' => 0,
            'email'           => 'error-email-format',
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    public function test_token為必填()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'email'           => 'error-email-format',
        ]);

        $this->assertValidationError('payment_token');
    }
}
