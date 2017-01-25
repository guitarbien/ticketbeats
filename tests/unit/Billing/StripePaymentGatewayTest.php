<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class StripePaymentGatewayTest extends TestCase
{
    public function test_以合法token付款成功()
    {
        // Create a new Stripe paymentGateway

        // Create a new charge with some amount using a valid token

        // Verify that the charge was completed successfully
    }
}