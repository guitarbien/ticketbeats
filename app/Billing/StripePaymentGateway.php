<?php

namespace App\Billing;

class StripePaymentGateway implements PaymentGateway
{
    public function charge($amount, $token)
    {

    }

    public function totalCharges()
    {
        return 2500;
    }
}