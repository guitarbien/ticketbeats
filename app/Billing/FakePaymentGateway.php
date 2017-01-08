<?php

namespace App\Billing;

class FakePaymentGateway
{
    public function __construct()
    {
        $this->charges = collect();
    }

    public function getValidTestToken()
    {
        return 'valid-token';
    }

    public function totalCharges()
    {
        return $this->charges->sum();
    }

    public function charge($amount, $token)
    {
        $this->charges[] = $amount;
    }
}