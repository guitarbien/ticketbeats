<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use App\OrderConfirmationNumberGenerator;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp()
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);

        // tell the IOC container to switch to MailFake
        Mail::fake();
    }

    private function orderTickets($concert, $params)
    {
        $savedRequest = $this->app['request'];

        $this->response = $this->json('POST', "/concerts/{$concert->id}/orders", $params);

        $this->app['request'] = $savedRequest;
    }

    private function assertResponseStatus($status)
    {
        return $this->response->assertStatus($status);
    }

    private function seeJsonSubset($data)
    {
        return $this->response->assertJson($data);
    }

    private function decodeResponseJson()
    {
        return $this->response->decodeResponseJson();
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    public function test_使用者可以購票()
    {
        $this->disableExceptionHandling();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

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

        $this->seeJsonSubset([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email'           => 'john@example.com',
            'amount'          => 9750,
            'tickets'         => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ]
        ]);

        // Make sure the customer was charged the correct amount
        // 要付多少錢會決定 token，再拿 token 來問付了多少錢
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        // Make sure that an order exists for this customer
        $this->assertTrue($concert->hasOrderFor('john@example.com'));

        $order = $concert->ordersFor('john@example.com')->first();

        $this->assertEquals(3, $order->ticketQuantity());

        // 傳入 mailable class
        mail::assertSent(OrderConfirmationEmail::class, function($mail) use($order) {
            return $mail->hasTo('john@example.com') && $mail->order->id == $order->id;
        });
    }

    public function test_不能購買尚未發佈的票()
    {
        $concert = factory(Concert::class)->states('unpublished')->create(['ticket_price' => 3250])->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    public function test_不能超量購買()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    public function test_票券若是在嘗試購買中則不能再被購買()
    {
        $this->disableExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 1200])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use($concert) {
            $this->orderTickets($concert, [
                'email'           => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]);

            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $this->orderTickets($concert, [
            'email'           => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    public function test_若付款失敗則不會產生訂單()
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250])->addTickets(3);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
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