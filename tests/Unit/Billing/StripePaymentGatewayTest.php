<?php

namespace Tests\Unit\Billing;

use App\Billing\StripePaymentGateway;
use Tests\TestCase;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTests;

    /**
     * @return StripePaymentGateway
     */
    protected function getPaymentGateway()
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    public function test_90趴的付款會被轉給DestinationAccountId()
    {
        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

        $paymentGateway->charge(5000, $paymentGateway->getValidTestToken(), env('STRIPE_TEST_PROMOTER_ID'));

        $lastStripeCharge = array_first(\Stripe\Charge::all(
            ["limit" => 1],
            ['api_key' => config('services.stripe.secret')]
        )->data);

        static::assertEquals(5000, $lastStripeCharge['amount']);
        static::assertEquals(env('STRIPE_TEST_PROMOTER_ID'), $lastStripeCharge['destination']);

        $transfer = \Stripe\Transfer::retrieve($lastStripeCharge['transfer'], ['api_key' => config('services.stripe.secret')]);
        static::assertEquals(4500, $transfer['amount']);
    }
}