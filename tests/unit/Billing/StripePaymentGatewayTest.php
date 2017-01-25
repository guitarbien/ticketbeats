<?php

use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class StripePaymentGatewayTest extends TestCase
{
    public function test_以合法token付款成功()
    {
        // Create a new Stripe paymentGateway
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        // get real token
        $token = \Stripe\Token::create([
            "card" => [
                // give fake card info
                "number"    => "4242424242424242",
                "exp_month" => 1,
                "exp_year"  => date('Y') + 1,
                "cvc"       => "123",
            ]
        ], ['api_key' => config('services.stripe.secret')])->id;


        // Create a new charge with some amount using a valid token
        $paymentGateway->charge(2500, $token);

        // Verify that the charge was completed successfully
        $this->assertEquals(2500, $paymentGateway->totalCharges());

        $lastCharge = \Stripe\Charge::all(
            ["limit" => 1],
            ['api_key' => config('services.stripe.secret')]
        )->data[0];

        $this->assertEquals(2500, $lastCharge->amount);
    }
}