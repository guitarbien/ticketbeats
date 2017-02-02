<?php

use App\Billing\PaymentFailedException;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    private $lastCharge;

    private function lastCharge()
    {
        return array_first(\Stripe\Charge::all(
            ["limit" => 1],
            ['api_key' => config('services.stripe.secret')]
        )->data);
    }

    private function newCharges()
    {
        return \Stripe\Charge::all(
            [
                "limit"         => 1,
                "ending_before" => $this->lastCharge ? $this->lastCharge->id : null,
            ],
            ['api_key' => config('services.stripe.secret')]
        )->data;
    }

    private function validToken()
    {
        return \Stripe\Token::create([
            "card" => [
                // give fake card info
                "number"    => "4242424242424242",
                "exp_month" => 1,
                "exp_year"  => date('Y') + 1,
                "cvc"       => "123",
            ]
        ], ['api_key' => config('services.stripe.secret')])->id;
    }

    protected function setUp()
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    public function test_以不合法token付款失敗()
    {
        try {
            $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail("Charging with an invalid payment token did not throw a PaymentFailedException.");
    }
}