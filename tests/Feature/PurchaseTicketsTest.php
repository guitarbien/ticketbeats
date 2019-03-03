<?php

namespace Tests\Feature;

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\Facades\TicketCode;
use App\Mail\OrderConfirmationEmail;
use App\User;
use ConcertFactory;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /** @var FakePaymentGateway */
    private $paymentGateway;

    protected function setUp(): void
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
        static::assertResponseStatus(422);
        static::assertArrayHasKey($field, $this->decodeResponseJson()['errors']);
    }

    public function test_使用者可以購票()
    {
        $this->withoutExceptionHandling();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
        TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

        // Arrange
        $user = factory(User::class)->create(['stripe_account_id' => 'test_acct_1234']);

        // Create a concert
        $concert = ConcertFactory::createPublished([
            'ticket_price'    => 3250,
            'ticket_quantity' => 3,
            'user_id'         => $user,
        ]);

        // Action
        // Purchase concert tickets\
        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        // Assert
        static::assertResponseStatus(201);

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
        static::assertEquals(9750, $this->paymentGateway->totalChargesFor('test_acct_1234'));

        // Make sure that an order exists for this customer
        static::assertTrue($concert->hasOrderFor('john@example.com'));

        $order = $concert->ordersFor('john@example.com')->first();

        static::assertEquals(3, $order->ticketQuantity());

        // 傳入 mailable class
        mail::assertSent(OrderConfirmationEmail::class, function($mail) use($order) {
            return $mail->hasTo('john@example.com') && $mail->order->id == $order->id;
        });
    }

    public function test_不能購買尚未發佈的票()
    {
        $concert = factory(Concert::class)->states('unpublished')->create(['ticket_price' => 3250, 'ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertResponseStatus(404);
        static::assertFalse($concert->hasOrderFor('john@example.com'));
        static::assertEquals(0, $this->paymentGateway->totalCharges());
    }

    public function test_不能超量購買()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 50]);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertResponseStatus(422);
        static::assertFalse($concert->hasOrderFor('john@example.com'));
        static::assertEquals(0, $this->paymentGateway->totalCharges());
        static::assertEquals(50, $concert->ticketsRemaining());
    }

    public function test_票券若是在嘗試購買中則不能再被購買()
    {
        $this->withoutExceptionHandling();

        $concert = ConcertFactory::createPublished(['ticket_price' => 1200, 'ticket_quantity' => 3]);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use($concert) {
            $this->orderTickets($concert, [
                'email'           => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token'   => $this->paymentGateway->getValidTestToken(),
            ]);

            static::assertResponseStatus(422);
            static::assertFalse($concert->hasOrderFor('personB@example.com'));
            static::assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $this->orderTickets($concert, [
            'email'           => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertEquals(3600, $this->paymentGateway->totalCharges());
        static::assertTrue($concert->hasOrderFor('personA@example.com'));
        static::assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    public function test_若付款失敗則不會產生訂單()
    {
        $concert = ConcertFactory::createPublished(['ticket_price' => 3250, 'ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'email'           => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'   => 'invalid-payment-token',
        ]);

        static::assertResponseStatus(422);

        static::assertFalse($concert->hasOrderFor('john@example.com'));
        static::assertEquals(3, $concert->ticketsRemaining());
    }

    public function test_下單時email為必填()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertValidationError('email');
    }

    public function test_驗證email格式()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'email'           => 'error-email-format',
            'ticket_quantity' => 3,
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertValidationError('email');
    }

    public function test_票券數量為必填()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'email'         => 'error-email-format',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertValidationError('ticket_quantity');
    }

    public function test_票券數量至少要為1()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'ticket_quantity' => 0,
            'email'           => 'error-email-format',
            'payment_token'   => $this->paymentGateway->getValidTestToken(),
        ]);

        static::assertValidationError('ticket_quantity');
    }

    public function test_token為必填()
    {
        $concert = ConcertFactory::createPublished(['ticket_quantity' => 3]);

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'email'           => 'error-email-format',
        ]);

        static::assertValidationError('payment_token');
    }
}
