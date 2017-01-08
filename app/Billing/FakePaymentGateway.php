<?php

namespace App\Billing;

class FakePaymentGateway
{
    public function getValidTestToken()
    {

    }

    public function totalCharges()
    {
        return 9750;
    }
}