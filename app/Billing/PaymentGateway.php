<?php

namespace App\Billing;

interface PaymentGateway
{
    public function charge($amount, $token, string $destinationAccountId);

    public function getValidTestToken();

    public function newChargesDuring($callback);
}
